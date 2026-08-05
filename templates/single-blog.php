<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
shopblocks_render_theme_header();
while ( have_posts() ) : the_post();
	$post_id             = get_the_ID();
	$product_ids         = get_post_meta( $post_id, '_shopblocks_sidebar_product_ids', true );
	$helpful             = get_post_meta( $post_id, '_shopblocks_helpful_links', true );
	$helpful_heading     = get_post_meta( $post_id, '_shopblocks_helpful_links_heading', true );
	$newsletter          = get_post_meta( $post_id, '_shopblocks_show_newsletter', true );
	$newsletter          = '' === $newsletter ? '1' : $newsletter;
	$newsletter_title    = get_post_meta( $post_id, '_shopblocks_newsletter_title', true );
	$newsletter_text     = get_post_meta( $post_id, '_shopblocks_newsletter_description', true );
	$custom_content      = get_post_meta( $post_id, '_shopblocks_sidebar_custom_content', true );
	$faqs                = get_post_meta( $post_id, '_shopblocks_blog_faqs', true );
	$faq_heading         = get_post_meta( $post_id, '_shopblocks_blog_faq_heading', true );
	$hero_url            = has_post_thumbnail() ? get_the_post_thumbnail_url( $post_id, 'full' ) : '';
	?>
	<main class="shopblocks-page shopblocks-blog shopblocks-single-blog">
		<header class="shopblocks-blog__hero<?php echo $hero_url ? ' has-background' : ' has-no-background'; ?>"<?php if ( $hero_url ) : ?> style="--shopblocks-blog-hero-image:url('<?php echo esc_url( $hero_url ); ?>')"<?php endif; ?>>
			<?php if ( $hero_url ) : ?><div class="shopblocks-blog__hero-media" aria-hidden="true"></div><?php endif; ?>
			<div class="shopblocks-blog__hero-overlay" aria-hidden="true"></div>
			<div class="shopblocks-blog__hero-content">
				<h1 class="shopblocks-blog__title"><?php the_title(); ?></h1>
				<div class="shopblocks-blog__meta">
					<time class="shopblocks-blog__date" datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
					<span class="shopblocks-blog__meta-separator" aria-hidden="true">|</span>
					<span class="shopblocks-blog__author"><?php echo esc_html( get_the_author() ); ?></span>
				</div>
			</div>
		</header>

		<div class="shopblocks-blog__layout">
			<div class="shopblocks-blog__main">
				<article class="shopblocks-blog__article">
					<div class="shopblocks-gutenberg-content shopblocks-blog__content"><?php the_content(); ?></div>
				</article>
				<?php echo shopblocks_render_faqs( $faqs, $faq_heading ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>

			<aside class="shopblocks-blog__sidebar" aria-label="<?php esc_attr_e( 'Blog sidebar', 'shopblocks-wp' ); ?>">
				<?php if ( '1' === $newsletter ) : ?>
					<section class="shopblocks-sidebar-block shopblocks-sidebar-block--newsletter">
						<?php echo do_shortcode( '[shopblocks_newsletter title="' . esc_attr( $newsletter_title ?: __( 'Join Our Mailing List.', 'shopblocks-wp' ) ) . '" description="' . esc_attr( $newsletter_text ) . '"]' ); ?>
					</section>
				<?php endif; ?>

				<?php if ( $product_ids ) : ?>
					<section class="shopblocks-sidebar-block shopblocks-sidebar-block--products" aria-label="<?php esc_attr_e( 'Featured products', 'shopblocks-wp' ); ?>">
						<?php echo do_shortcode( '[shopblocks_products ids="' . esc_attr( $product_ids ) . '" limit="12" columns="1" layout="sidebar" class="shopblocks-sidebar-products"]' ); ?>
					</section>
				<?php endif; ?>

				<?php if ( trim( (string) $helpful ) ) : ?>
					<section class="shopblocks-sidebar-block shopblocks-sidebar-block--links">
						<?php echo shopblocks_render_helpful_links( $helpful, $helpful_heading ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</section>
				<?php endif; ?>

				<?php if ( trim( (string) $custom_content ) ) : ?>
					<section class="shopblocks-sidebar-block shopblocks-sidebar-block--custom">
						<?php echo do_shortcode( wp_kses_post( $custom_content ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</section>
				<?php endif; ?>
			</aside>
		</div>
	</main>
<?php endwhile;
shopblocks_render_theme_footer();
