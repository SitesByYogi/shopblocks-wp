(function ($) {
	'use strict';

	var config = window.ShopBlocksProductSelector || {};
	var timers = new WeakMap();
	var controllers = new WeakMap();

	function escapeHtml(value) {
		return $('<div>').text(value == null ? '' : String(value)).html();
	}

	function stockLabel(status) {
		if (status === 'instock') return config.inStock || 'In stock';
		if (status === 'onbackorder') return config.backorder || 'On backorder';
		return config.outOfStock || 'Out of stock';
	}

	function selectedIds($selector) {
		var ids = [];
		$selector.find('.shopblocks-product-selector__item').each(function () {
			var id = parseInt($(this).attr('data-product-id'), 10);
			if (id && ids.indexOf(id) === -1) ids.push(id);
		});
		return ids;
	}

	function sync($selector) {
		var ids = selectedIds($selector);
		$selector.find('.shopblocks-product-selector__value').val(ids.join(',')).trigger('change');
		$selector.find('.shopblocks-product-selector__count').text(ids.length);
		$selector.find('.shopblocks-product-selector__empty').prop('hidden', ids.length > 0);
	}

	function selectedRow(item) {
		var image = item.image ? '<img class="shopblocks-product-selector__thumb" src="' + escapeHtml(item.image) + '" alt="">' : '';
		var details = '<span>#' + escapeHtml(item.id) + '</span>';
		if (item.sku) details += '<span>SKU: ' + escapeHtml(item.sku) + '</span>';
		if (item.price_html) details += '<span class="shopblocks-product-selector__price">' + item.price_html + '</span>';
		details += '<span>' + escapeHtml(stockLabel(item.stock_status)) + '</span>';

		return '<li class="shopblocks-product-selector__item" data-product-id="' + escapeHtml(item.id) + '">' +
			'<button type="button" class="shopblocks-product-selector__handle" aria-label="Drag to reorder" title="Drag to reorder">☰</button>' +
			image +
			'<div class="shopblocks-product-selector__meta">' +
				'<strong class="shopblocks-product-selector__name">' + escapeHtml(item.name) + '</strong>' +
				'<div class="shopblocks-product-selector__details">' + details + '</div>' +
			'</div>' +
			'<button type="button" class="button-link-delete shopblocks-product-selector__remove">' + escapeHtml(config.remove || 'Remove') + '</button>' +
		'</li>';
	}

	function resultRow(item, alreadySelected) {
		var image = item.image ? '<img class="shopblocks-product-selector__result-thumb" src="' + escapeHtml(item.image) + '" alt="">' : '';
		var meta = '#' + escapeHtml(item.id);
		if (item.sku) meta += ' · SKU: ' + escapeHtml(item.sku);

		return '<button type="button" class="shopblocks-product-selector__result" data-product=\'' + escapeHtml(JSON.stringify(item)) + '\'' + (alreadySelected ? ' disabled' : '') + '>' +
			image +
			'<span class="shopblocks-product-selector__result-copy">' +
				'<strong>' + escapeHtml(item.name) + '</strong>' +
				'<small>' + meta + '</small>' +
			'</span>' +
			'<span class="shopblocks-product-selector__result-action">' + (alreadySelected ? '✓' : escapeHtml(config.add || 'Add')) + '</span>' +
		'</button>';
	}

	function renderResults($selector, items) {
		var $results = $selector.find('.shopblocks-product-selector__results');
		var current = selectedIds($selector);

		if (!items.length) {
			$results.html('<div class="shopblocks-product-selector__message">' + escapeHtml(config.noResults || 'No matching products found.') + '</div>').prop('hidden', false);
			return;
		}

		$results.html(items.map(function (item) {
			return resultRow(item, current.indexOf(parseInt(item.id, 10)) !== -1);
		}).join('')).prop('hidden', false);
	}

	function search($selector, term, input) {
		var previous = controllers.get(input);
		if (previous && previous.abort) previous.abort();

		var controller = window.AbortController ? new AbortController() : null;
		if (controller) controllers.set(input, controller);

		var $spinner = $selector.find('.shopblocks-product-selector__spinner');
		var $results = $selector.find('.shopblocks-product-selector__results');

		$spinner.addClass('is-active');
		$results.html('<div class="shopblocks-product-selector__message">' + escapeHtml(config.searching || 'Searching…') + '</div>').prop('hidden', false);

		var url = config.ajaxUrl +
			'?action=shopblocks_search_products' +
			'&nonce=' + encodeURIComponent(config.nonce || '') +
			'&term=' + encodeURIComponent(term);

		fetch(url, {
			credentials: 'same-origin',
			signal: controller ? controller.signal : undefined
		})
		.then(function (response) { return response.json(); })
		.then(function (json) {
			if (!json || !json.success || !Array.isArray(json.data)) throw new Error('Invalid response');
			renderResults($selector, json.data);
		})
		.catch(function (error) {
			if (error && error.name === 'AbortError') return;
			$results.html('<div class="shopblocks-product-selector__message shopblocks-product-selector__message--error">' + escapeHtml(config.error || 'Product search failed. Try again.') + '</div>').prop('hidden', false);
		})
		.finally(function () {
			$spinner.removeClass('is-active');
		});
	}

	function init($selector) {
		if ($selector.data('shopblocksProductSelectorReady')) return;
		$selector.data('shopblocksProductSelectorReady', true);

		if ($.fn.sortable) {
			$selector.find('.shopblocks-product-selector__selected').sortable({
				handle: '.shopblocks-product-selector__handle',
				axis: 'y',
				placeholder: 'shopblocks-product-selector__placeholder',
				update: function () { sync($selector); }
			});
		}
		sync($selector);
	}

	$(document).on('input', '.shopblocks-product-selector__search', function () {
		var input = this;
		var $input = $(input);
		var $selector = $input.closest('.shopblocks-product-selector');
		var term = $.trim($input.val());
		var oldTimer = timers.get(input);

		if (oldTimer) clearTimeout(oldTimer);

		if (term.length < 2) {
			$selector.find('.shopblocks-product-selector__results').prop('hidden', true).empty();
			return;
		}

		timers.set(input, setTimeout(function () {
			search($selector, term, input);
		}, 300));
	});

	$(document).on('click', '.shopblocks-product-selector__result', function () {
		if (this.disabled) return;

		var $button = $(this);
		var $selector = $button.closest('.shopblocks-product-selector');
		var item;

		try { item = JSON.parse($button.attr('data-product')); } catch (e) { return; }
		if (!item || !item.id || selectedIds($selector).indexOf(parseInt(item.id, 10)) !== -1) return;

		$selector.find('.shopblocks-product-selector__selected').append(selectedRow(item));
		$button.prop('disabled', true).find('.shopblocks-product-selector__result-action').text('✓');
		sync($selector);
	});

	$(document).on('click', '.shopblocks-product-selector__remove', function () {
		var $selector = $(this).closest('.shopblocks-product-selector');
		var id = parseInt($(this).closest('.shopblocks-product-selector__item').attr('data-product-id'), 10);

		$(this).closest('.shopblocks-product-selector__item').remove();

		$selector.find('.shopblocks-product-selector__result').each(function () {
			var item;
			try { item = JSON.parse($(this).attr('data-product')); } catch (e) { return; }
			if (parseInt(item.id, 10) === id) {
				$(this).prop('disabled', false).find('.shopblocks-product-selector__result-action').text(config.add || 'Add');
			}
		});

		sync($selector);
	});

	$(document).on('keydown', function (event) {
		if (event.key === 'Escape') $('.shopblocks-product-selector__results').prop('hidden', true);
	});

	$(function () {
		$('.shopblocks-product-selector').each(function () { init($(this)); });
	});
})(jQuery);
