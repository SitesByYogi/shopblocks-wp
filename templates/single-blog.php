<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
shopblocks_render_theme_header();
while ( have_posts() ) : the_post();
	$post_id             = get_the_ID();
	$product_ids         = shopblocks_get_blog_sidebar_product_ids( $post_id );
	$helpful             = get_post_meta( $post_id, '_shopblocks_helpful_links', true );
	$helpful_heading     = get_post_meta( $post_id, '_shopblocks_helpful_links_heading', true );
	$newsletter          = get_post_meta( $post_id, '_shopblocks_show_newsletter', true );
	$newsletter          = '' === $newsletter ? '1' : $newsletter;
	$newsletter_title    = get_post_meta( $post_id, '_shopblocks_newsletter_title', true );
	$newsletter_text     = get_post_meta( $post_id, '_shopblocks_newsletter_description', true );
	$newsletter_embed    = shopblocks_get_blog_newsletter_embed( $post_id );
	$custom_content      = get_post_meta( $post_id, '_shopblocks_sidebar_custom_content', true );
	$faqs                = get_post_meta( $post_id, '_shopblocks_blog_faqs', true );
	$faq_heading         = get_post_meta( $post_id, '_shopblocks_blog_faq_heading', true );
	$hero_url            = has_post_thumbnail() ? get_the_post_thumbnail_url( $post_id, 'full' ) : '';
	$eyebrow             = get_post_meta( $post_id, '_shopblocks_hero_eyebrow', true );
	$hero_description    = get_post_meta( $post_id, '_shopblocks_hero_description', true );
	$primary_label       = get_post_meta( $post_id, '_shopblocks_primary_cta_label', true );
	$primary_url         = get_post_meta( $post_id, '_shopblocks_primary_cta_url', true );
	$secondary_label     = get_post_meta( $post_id, '_shopblocks_secondary_cta_label', true );
	$secondary_url       = get_post_meta( $post_id, '_shopblocks_secondary_cta_url', true );
	$hero_form_shortcode = shopblocks_get_lead_form_shortcode( $post_id, '_shopblocks_hero_form_shortcode' );
	$sidebar_form_heading = get_post_meta( $post_id, '_shopblocks_sidebar_form_heading', true );
	$sidebar_form_shortcode = shopblocks_get_lead_form_shortcode( $post_id, '_shopblocks_sidebar_form_shortcode' );
	$has_sidebar = (
		( '1' === $newsletter && trim( (string) $newsletter_embed ) ) ||
		( shopblocks_has_woocommerce() && trim( (string) $product_ids ) ) ||
		trim( (string) $helpful ) || trim( (string) $custom_content ) ||
		trim( (string) shopblocks_render_sidebar_cta( $post_id ) ) ||
		trim( (string) $sidebar_form_shortcode )
	);
	?>
	<main class="shopblocks-page shopblocks-blog shopblocks-single-blog<?php echo $hero_form_shortcode ? ' has-hero-conversion' : ''; ?><?php echo $has_sidebar ? ' has-sidebar' : ' has-no-sidebar'; ?>">
		<header class="shopblocks-blog__hero<?php echo $hero_url ? ' has-background' : ' has-no-background'; ?>"<?php if ( $hero_url ) : ?> style="--shopblocks-blog-hero-image:url('<?php echo esc_url( $hero_url ); ?>')"<?php endif; ?>>
			<?php if ( $hero_url ) : ?><div class="shopblocks-blog__hero-media" aria-hidden="true"></div><?php endif; ?>
			<div class="shopblocks-blog__hero-overlay" aria-hidden="true"></div>
			<div class="shopblocks-blog__hero-shell">
				<div class="shopblocks-blog__hero-content">
					<?php if ( $eyebrow ) : ?><div class="shopblocks-blog__eyebrow"><?php echo esc_html( $eyebrow ); ?></div><?php endif; ?>
					<h1 class="shopblocks-blog__title"><?php the_title(); ?></h1>
					<?php if ( $hero_description ) : ?><p class="shopblocks-blog__hero-description"><?php echo esc_html( $hero_description ); ?></p><?php endif; ?>
					<div class="shopblocks-blog__meta">
						<time class="shopblocks-blog__date" datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
						<span class="shopblocks-blog__meta-separator" aria-hidden="true">|</span>
						<span class="shopblocks-blog__author"><?php echo esc_html( get_the_author() ); ?></span>
					</div>
					<?php if ( ( $primary_label && $primary_url ) || ( $secondary_label && $secondary_url ) ) : ?>
						<div class="shopblocks-hero-actions">
							<?php if ( $primary_label && $primary_url ) : ?><a class="shopblocks-button shopblocks-button--primary" href="<?php echo esc_url( $primary_url ); ?>"><?php echo esc_html( $primary_label ); ?></a><?php endif; ?>
							<?php if ( $secondary_label && $secondary_url ) : ?><a class="shopblocks-button shopblocks-button--secondary" href="<?php echo esc_url( $secondary_url ); ?>"><?php echo esc_html( $secondary_label ); ?></a><?php endif; ?>
						</div>
					<?php endif; ?>
				</div>
				<?php if ( $hero_form_shortcode ) : ?>
					<div class="shopblocks-blog__hero-conversion"><?php echo shopblocks_render_lead_form( $post_id, '_shopblocks_hero_form_shortcode' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
				<?php endif; ?>
			</div>
		</header>

		<div class="shopblocks-blog__layout">
			<div class="shopblocks-blog__main">
				<article class="shopblocks-blog__article">
					<div class="shopblocks-gutenberg-content shopblocks-blog__content"><?php the_content(); ?></div>
				</article>
				<?php echo shopblocks_render_faqs( $faqs, $faq_heading ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>

			<?php if ( $has_sidebar ) : ?>
			<aside class="shopblocks-blog__sidebar" aria-label="<?php esc_attr_e( 'Blog sidebar', 'shopblocks-wp' ); ?>">
				<?php $cta = shopblocks_render_sidebar_cta( $post_id ); if ( $cta ) : ?><section class="shopblocks-sidebar-block shopblocks-sidebar-block--cta"><?php echo $cta; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></section><?php endif; ?>
				<?php if ( $sidebar_form_shortcode ) : ?><section class="shopblocks-sidebar-block shopblocks-sidebar-block--form"><?php echo shopblocks_render_lead_form( $post_id, '_shopblocks_sidebar_form_shortcode', $sidebar_form_heading ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></section><?php endif; ?>
				<?php if ( '1' === $newsletter && trim( (string) $newsletter_embed ) ) : ?>
					<section class="shopblocks-sidebar-block shopblocks-sidebar-block--newsletter">
						<?php if ( $newsletter_title ) : ?><h2 class="shopblocks-newsletter__title"><?php echo esc_html( $newsletter_title ); ?></h2><?php endif; ?>
						<?php if ( $newsletter_text ) : ?><p class="shopblocks-newsletter__description"><?php echo esc_html( $newsletter_text ); ?></p><?php endif; ?>
						<div class="shopblocks-newsletter__form"><?php echo shopblocks_render_embed_content( $newsletter_embed ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
					</section>
				<?php endif; ?>
				<?php if ( shopblocks_has_woocommerce() && $product_ids ) : ?><section class="shopblocks-sidebar-block shopblocks-sidebar-block--products" aria-label="<?php esc_attr_e( 'Featured products', 'shopblocks-wp' ); ?>"><?php echo do_shortcode( '[shopblocks_products ids="' . esc_attr( $product_ids ) . '" limit="12" columns="1" layout="sidebar" class="shopblocks-sidebar-products"]' ); ?></section><?php endif; ?>
				<?php if ( trim( (string) $helpful ) ) : ?><section class="shopblocks-sidebar-block shopblocks-sidebar-block--links"><?php echo shopblocks_render_helpful_links( $helpful, $helpful_heading ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></section><?php endif; ?>
				<?php if ( trim( (string) $custom_content ) ) : ?><section class="shopblocks-sidebar-block shopblocks-sidebar-block--custom"><?php echo do_shortcode( wp_kses_post( $custom_content ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></section><?php endif; ?>
			</aside>
			<?php endif; ?>
		</div>
	</main>
<?php endwhile;
shopblocks_render_theme_footer();
