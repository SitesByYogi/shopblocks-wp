<?php
/**
 * Title: Default Shoppable Template
 * Slug: shopblocks/default-shoppable-template
 * Categories: shopblocks
 * Block Types: core/post-content
 * Inserter: yes
 * Description: Default shoppable layout using the ShopBlocks top product section, benefits, trust, FAQ, and CTA.
 */
?>

<!-- wp:group {"style":{"spacing":{"padding":{"top":"32px","bottom":"32px"}}},"layout":{"type":"constrained","contentSize":"1200px"}} -->
<div class="wp-block-group" style="padding-top:32px;padding-bottom:32px">

	<!-- wp:heading {"textAlign":"center","level":1,"style":{"typography":{"lineHeight":"1.2"}}} -->
	<h1 class="wp-block-heading has-text-align-center" style="line-height:1.2">Featured Product</h1>
	<!-- /wp:heading -->

	<!-- wp:paragraph {"align":"center"} -->
	<p class="has-text-align-center">Hand-picked for performance, value, and quality.</p>
	<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->

<!-- wp:shortcode -->
[shoppable_product_top id="740"]
<!-- /wp:shortcode -->

<!-- wp:spacer {"height":"24px"} -->
<div style="height:24px" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:group {"style":{"spacing":{"padding":{"top":"20px","bottom":"20px"}}},"layout":{"type":"constrained","contentSize":"1200px"}} -->
<div class="wp-block-group" style="padding-top:20px;padding-bottom:20px">
	<!-- wp:columns {"style":{"spacing":{"blockGap":{"top":"24px","left":"24px"}}}} -->
	<div class="wp-block-columns">
		<!-- wp:column -->
		<div class="wp-block-column">
			<!-- wp:heading {"level":3} -->
			<h3 class="wp-block-heading">Why You’ll Love It</h3>
			<!-- /wp:heading -->
			<!-- wp:list -->
			<ul>
				<li>Fast, frictionless checkout</li>
				<li>Optimized layout for conversions</li>
				<li>Mobile-first, desktop-polished</li>
				<li>Backed by our performance standards</li>
			</ul>
			<!-- /wp:list -->
		</div>
		<!-- /wp:column -->

		<!-- wp:column -->
		<div class="wp-block-column">
			<!-- wp:heading {"level":3} -->
			<h3 class="wp-block-heading">What’s Included</h3>
			<!-- /wp:heading -->
			<!-- wp:list -->
			<ul>
				<li>Top product section with variations</li>
				<li>Clear pricing and quantity controls</li>
				<li>SEO-ready structure</li>
				<li>Theme-friendly styles (Storefront)</li>
			</ul>
			<!-- /wp:list -->
		</div>
		<!-- /wp:column -->

		<!-- wp:column -->
		<div class="wp-block-column">
			<!-- wp:heading {"level":3} -->
			<h3 class="wp-block-heading">Trust & Support</h3>
			<!-- /wp:heading -->
			<!-- wp:list -->
			<ul>
				<li>Secure payments via WooCommerce</li>
				<li>Flexible returns per your policy</li>
				<li>Fast support from our team</li>
				<li>Performance-minded updates</li>
			</ul>
			<!-- /wp:list -->
		</div>
		<!-- /wp:column -->
	</div>
	<!-- /wp:columns -->
</div>
<!-- /wp:group -->

<!-- wp:group {"style":{"spacing":{"padding":{"top":"30px","bottom":"30px"}}},"layout":{"type":"constrained","contentSize":"1000px"}} -->
<div class="wp-block-group" style="padding-top:30px;padding-bottom:30px">
	<!-- wp:heading {"textAlign":"center","level":3} -->
	<h3 class="wp-block-heading has-text-align-center">FAQs</h3>
	<!-- /wp:heading -->

	<!-- wp:details -->
	<details>
		<summary>How fast will my order ship?</summary>
		<p>Most orders ship within 1–2 business days. You’ll receive tracking by email.</p>
	</details>
	<!-- /wp:details -->

	<!-- wp:details -->
	<details>
		<summary>Can I pick different options like size or variant?</summary>
		<p>Yes—use the selectors in the top product section. The price updates automatically.</p>
	</details>
	<!-- /wp:details -->

	<!-- wp:details -->
	<details>
		<summary>Is this layout compatible with my theme?</summary>
		<p>Yes—this template is built and tested on Storefront and follows Gutenberg best practices.</p>
	</details>
	<!-- /wp:details -->
</div>
<!-- /wp:group -->

<!-- wp:group {"style":{"spacing":{"padding":{"top":"24px","bottom":"24px"}},"border":{"radius":"10px"}},"backgroundColor":"primary","textColor":"background","layout":{"type":"constrained","contentSize":"1000px"}} -->
<div class="wp-block-group has-background-color has-primary-background-color has-text-color" style="border-radius:10px;padding-top:24px;padding-bottom:24px">
	<!-- wp:heading {"textAlign":"center","level":3} -->
	<h3 class="wp-block-heading has-text-align-center">Ready to checkout?</h3>
	<!-- /wp:heading -->
	<!-- wp:paragraph {"align":"center"} -->
	<p class="has-text-align-center">Add to cart above—questions? Contact us and we’ll help you choose the right options.</p>
	<!-- /wp:paragraph -->
	<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
	<div class="wp-block-buttons">
		<!-- wp:button {"className":"is-style-fill"} -->
		<div class="wp-block-button is-style-fill"><a class="wp-block-button__link wp-element-button">Need Help?</a></div>
		<!-- /wp:button -->
	</div>
	<!-- /wp:buttons -->
</div>
<!-- /wp:group -->
