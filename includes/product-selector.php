<?php
/**
 * Reusable WooCommerce product selector for ShopBlocks admin screens.
 *
 * Preserves the existing comma-separated product ID storage format while
 * providing AJAX search, product details, removal, and drag-and-drop ordering.
 *
 * @package ShopBlocksWP
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function shopblocks_product_selector_ids( $value ) {
	$parts = is_array( $value ) ? $value : preg_split( '/\s*,\s*/', (string) $value );
	return array_values( array_unique( array_filter( array_map( 'absint', (array) $parts ) ) ) );
}

function shopblocks_product_selector_item( $product_id ) {
	if ( ! shopblocks_has_woocommerce() ) {
		return null;
	}

	$product = wc_get_product( absint( $product_id ) );
	if ( ! $product instanceof WC_Product ) {
		return null;
	}

	$image_id  = $product->get_image_id();
	$image_url = $image_id ? wp_get_attachment_image_url( $image_id, 'thumbnail' ) : '';

	if ( ! $image_url && function_exists( 'wc_placeholder_img_src' ) ) {
		$image_url = wc_placeholder_img_src( 'thumbnail' );
	}

	return array(
		'id'           => $product->get_id(),
		'name'         => $product->get_name(),
		'sku'          => $product->get_sku(),
		'price_html'   => $product->get_price_html(),
		'stock_status' => $product->get_stock_status(),
		'image'        => $image_url ? esc_url_raw( $image_url ) : '',
		'type'         => $product->get_type(),
	);
}

function shopblocks_render_product_selector( $field_name, $value = '', $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'id'          => sanitize_html_class( str_replace( array( '[', ']' ), '-', $field_name ) ),
			'label'       => __( 'Products', 'shopblocks-wp' ),
			'description' => __( 'Search WooCommerce products, add them to the list, then drag to control display order.', 'shopblocks-wp' ),
			'placeholder' => __( 'Search products by name or SKU…', 'shopblocks-wp' ),
		)
	);

	$ids = shopblocks_product_selector_ids( $value );

	if ( ! shopblocks_has_woocommerce() ) {
		?>
		<input type="hidden" name="<?php echo esc_attr( $field_name ); ?>" value="<?php echo esc_attr( implode( ',', $ids ) ); ?>">
		<p class="description"><?php esc_html_e( 'WooCommerce is not active. Existing product IDs are being preserved.', 'shopblocks-wp' ); ?></p>
		<?php
		return;
	}

	$items = array();
	foreach ( $ids as $id ) {
		$item = shopblocks_product_selector_item( $id );
		if ( $item ) {
			$items[] = $item;
		}
	}

	$selector_id = 'shopblocks-product-selector-' . sanitize_html_class( $args['id'] ) . '-' . wp_rand( 1000, 999999 );
	?>
	<div id="<?php echo esc_attr( $selector_id ); ?>" class="shopblocks-product-selector" data-field-name="<?php echo esc_attr( $field_name ); ?>">
		<input type="hidden" class="shopblocks-product-selector__value" name="<?php echo esc_attr( $field_name ); ?>" value="<?php echo esc_attr( implode( ',', $ids ) ); ?>">

		<div class="shopblocks-product-selector__search-wrap">
			<label class="screen-reader-text" for="<?php echo esc_attr( $selector_id ); ?>-search"><?php echo esc_html( $args['label'] ); ?></label>
			<input id="<?php echo esc_attr( $selector_id ); ?>-search" type="search" class="shopblocks-product-selector__search" placeholder="<?php echo esc_attr( $args['placeholder'] ); ?>" autocomplete="off">
			<span class="spinner shopblocks-product-selector__spinner" aria-hidden="true"></span>
		</div>

		<div class="shopblocks-product-selector__results" hidden></div>
		<p class="description shopblocks-product-selector__description"><?php echo esc_html( $args['description'] ); ?></p>

		<div class="shopblocks-product-selector__selected-wrap">
			<div class="shopblocks-product-selector__selected-heading">
				<strong><?php esc_html_e( 'Selected products', 'shopblocks-wp' ); ?></strong>
				<span class="shopblocks-product-selector__count"><?php echo esc_html( count( $items ) ); ?></span>
			</div>

			<ul class="shopblocks-product-selector__selected">
				<?php foreach ( $items as $item ) : ?>
					<?php shopblocks_product_selector_render_selected_item( $item ); ?>
				<?php endforeach; ?>
			</ul>

			<p class="shopblocks-product-selector__empty" <?php echo $items ? 'hidden' : ''; ?>><?php esc_html_e( 'No products selected yet.', 'shopblocks-wp' ); ?></p>
		</div>
	</div>
	<?php
}

function shopblocks_product_selector_render_selected_item( $item ) {
	$stock_label = 'instock' === $item['stock_status']
		? __( 'In stock', 'shopblocks-wp' )
		: ( 'onbackorder' === $item['stock_status'] ? __( 'On backorder', 'shopblocks-wp' ) : __( 'Out of stock', 'shopblocks-wp' ) );
	?>
	<li class="shopblocks-product-selector__item" data-product-id="<?php echo esc_attr( $item['id'] ); ?>">
		<button type="button" class="shopblocks-product-selector__handle" aria-label="<?php esc_attr_e( 'Drag to reorder', 'shopblocks-wp' ); ?>" title="<?php esc_attr_e( 'Drag to reorder', 'shopblocks-wp' ); ?>">☰</button>
		<?php if ( $item['image'] ) : ?><img class="shopblocks-product-selector__thumb" src="<?php echo esc_url( $item['image'] ); ?>" alt=""><?php endif; ?>
		<div class="shopblocks-product-selector__meta">
			<strong class="shopblocks-product-selector__name"><?php echo esc_html( $item['name'] ); ?></strong>
			<div class="shopblocks-product-selector__details">
				<span>#<?php echo esc_html( $item['id'] ); ?></span>
				<?php if ( $item['sku'] ) : ?><span><?php echo esc_html( 'SKU: ' . $item['sku'] ); ?></span><?php endif; ?>
				<?php if ( $item['price_html'] ) : ?><span class="shopblocks-product-selector__price"><?php echo wp_kses_post( $item['price_html'] ); ?></span><?php endif; ?>
				<span><?php echo esc_html( $stock_label ); ?></span>
			</div>
		</div>
		<button type="button" class="button-link-delete shopblocks-product-selector__remove" aria-label="<?php esc_attr_e( 'Remove product', 'shopblocks-wp' ); ?>"><?php esc_html_e( 'Remove', 'shopblocks-wp' ); ?></button>
	</li>
	<?php
}

function shopblocks_product_selector_search_query( $query, $query_vars ) {
	if ( ! empty( $query_vars['shopblocks_search'] ) ) {
		$query['s'] = sanitize_text_field( $query_vars['shopblocks_search'] );
	}
	return $query;
}

function shopblocks_ajax_search_products() {
	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_send_json_error( array( 'message' => __( 'You do not have permission to search products.', 'shopblocks-wp' ) ), 403 );
	}

	check_ajax_referer( 'shopblocks_product_selector', 'nonce' );

	if ( ! shopblocks_has_woocommerce() ) {
		wp_send_json_success( array() );
	}

	$term = isset( $_GET['term'] ) ? sanitize_text_field( wp_unslash( $_GET['term'] ) ) : '';
	if ( strlen( $term ) < 2 ) {
		wp_send_json_success( array() );
	}

	add_filter( 'woocommerce_product_data_store_cpt_get_products_query', 'shopblocks_product_selector_search_query', 10, 2 );

	$products = wc_get_products(
		array(
			'status'            => 'publish',
			'limit'             => 20,
			'return'            => 'objects',
			'orderby'           => 'name',
			'order'             => 'ASC',
			'shopblocks_search' => $term,
		)
	);

	remove_filter( 'woocommerce_product_data_store_cpt_get_products_query', 'shopblocks_product_selector_search_query', 10 );

	$sku_products = wc_get_products(
		array(
			'status'  => 'publish',
			'limit'   => 10,
			'return'  => 'objects',
			'sku'     => $term,
			'orderby' => 'name',
			'order'   => 'ASC',
		)
	);

	$merged = array();
	foreach ( array_merge( $products, $sku_products ) as $product ) {
		if ( ! $product instanceof WC_Product ) {
			continue;
		}
		$merged[ $product->get_id() ] = $product;
		if ( count( $merged ) >= 20 ) {
			break;
		}
	}

	$results = array();
	foreach ( $merged as $product ) {
		$item = shopblocks_product_selector_item( $product->get_id() );
		if ( $item ) {
			$results[] = $item;
		}
	}

	wp_send_json_success( $results );
}
add_action( 'wp_ajax_shopblocks_search_products', 'shopblocks_ajax_search_products' );

function shopblocks_product_selector_admin_assets( $hook_suffix ) {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	$load   = false;

	if ( $screen && in_array( $screen->post_type, array( 'collection', 'shopblocks_blog' ), true ) ) {
		$load = true;
	}

	if ( false !== strpos( (string) $hook_suffix, 'shopblocks' ) ) {
		$load = true;
	}

	if ( ! $load ) {
		return;
	}

	wp_enqueue_script( 'jquery-ui-sortable' );

	wp_enqueue_style(
		'shopblocks-product-selector-admin',
		SHOPBLOCKS_PLUGIN_URL . 'assets/admin-product-selector.css',
		array(),
		SHOPBLOCKS_PLUGIN_VERSION
	);

	wp_enqueue_script(
		'shopblocks-product-selector-admin',
		SHOPBLOCKS_PLUGIN_URL . 'assets/admin-product-selector.js',
		array( 'jquery', 'jquery-ui-sortable' ),
		SHOPBLOCKS_PLUGIN_VERSION,
		true
	);

	wp_localize_script(
		'shopblocks-product-selector-admin',
		'ShopBlocksProductSelector',
		array(
			'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
			'nonce'      => wp_create_nonce( 'shopblocks_product_selector' ),
			'searching'  => __( 'Searching…', 'shopblocks-wp' ),
			'noResults'  => __( 'No matching products found.', 'shopblocks-wp' ),
			'error'      => __( 'Product search failed. Try again.', 'shopblocks-wp' ),
			'add'        => __( 'Add', 'shopblocks-wp' ),
			'remove'     => __( 'Remove', 'shopblocks-wp' ),
			'inStock'    => __( 'In stock', 'shopblocks-wp' ),
			'backorder'  => __( 'On backorder', 'shopblocks-wp' ),
			'outOfStock' => __( 'Out of stock', 'shopblocks-wp' ),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'shopblocks_product_selector_admin_assets' );
