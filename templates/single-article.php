<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
shopblocks_render_theme_header();
while ( have_posts() ) : the_post();
	$post_id          = get_the_ID();
	$hero_url         = has_post_thumbnail() ? get_the_post_thumbnail_url( $post_id, 'full' ) : '';
	$eyebrow          = get_post_meta( $post_id, '_shopblocks_hero_eyebrow', true );
	$description      = get_post_meta( $post_id, '_shopblocks_hero_description', true );
	$primary_label    = get_post_meta( $post_id, '_shopblocks_primary_cta_label', true );
	$primary_url      = get_post_meta( $post_id, '_shopblocks_primary_cta_url', true );
	$secondary_label  = get_post_meta( $post_id, '_shopblocks_secondary_cta_label', true );
	$secondary_url    = get_post_meta( $post_id, '_shopblocks_secondary_cta_url', true );
	$form_shortcode   = shopblocks_get_lead_form_shortcode( $post_id, '_shopblocks_hero_form_shortcode' );
	$location_name    = get_post_meta( $post_id, '_shopblocks_location_name', true );
	$location_address = get_post_meta( $post_id, '_shopblocks_location_address', true );
	$location_phone   = get_post_meta( $post_id, '_shopblocks_location_phone', true );
	$location_email   = get_post_meta( $post_id, '_shopblocks_location_email', true );
	$map_url          = get_post_meta( $post_id, '_shopblocks_location_map_url', true );
	$faqs             = get_post_meta( $post_id, '_shopblocks_blog_faqs', true );
	$faq_heading      = get_post_meta( $post_id, '_shopblocks_blog_faq_heading', true );
	$has_location     = $location_name || $location_address || $location_phone || $location_email || $map_url;
	?>
	<main class="shopblocks-page shopblocks-article shopblocks-single-article<?php echo $form_shortcode ? ' has-hero-conversion' : ''; ?>">
		<header class="shopblocks-article__hero<?php echo $hero_url ? ' has-background' : ' has-no-background'; ?>"<?php if ( $hero_url ) : ?> style="--shopblocks-article-hero-image:url('<?php echo esc_url( $hero_url ); ?>')"<?php endif; ?>>
			<?php if ( $hero_url ) : ?><div class="shopblocks-article__hero-media" aria-hidden="true"></div><?php endif; ?>
			<div class="shopblocks-article__hero-overlay" aria-hidden="true"></div>
			<div class="shopblocks-article__hero-shell">
				<div class="shopblocks-article__hero-copy">
					<?php if ( $eyebrow ) : ?><div class="shopblocks-article__eyebrow"><?php echo esc_html( $eyebrow ); ?></div><?php endif; ?>
					<h1 class="shopblocks-article__title"><?php the_title(); ?></h1>
					<?php if ( $description ) : ?><p class="shopblocks-article__description"><?php echo esc_html( $description ); ?></p><?php endif; ?>
					<?php if ( ( $primary_label && $primary_url ) || ( $secondary_label && $secondary_url ) ) : ?>
					<div class="shopblocks-hero-actions">
						<?php if ( $primary_label && $primary_url ) : ?><a class="shopblocks-button shopblocks-button--primary" href="<?php echo esc_url( $primary_url ); ?>"><?php echo esc_html( $primary_label ); ?></a><?php endif; ?>
						<?php if ( $secondary_label && $secondary_url ) : ?><a class="shopblocks-button shopblocks-button--secondary" href="<?php echo esc_url( $secondary_url ); ?>"><?php echo esc_html( $secondary_label ); ?></a><?php endif; ?>
					</div>
					<?php endif; ?>
				</div>
				<?php if ( $form_shortcode ) : ?><div class="shopblocks-article__hero-conversion"><?php echo shopblocks_render_lead_form( $post_id, '_shopblocks_hero_form_shortcode' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div><?php endif; ?>
			</div>
		</header>

		<div class="shopblocks-article__body">
			<?php if ( $has_location ) : ?>
			<section class="shopblocks-location" aria-label="<?php esc_attr_e( 'Location information', 'shopblocks-wp' ); ?>">
				<?php if ( $map_url ) : ?><div class="shopblocks-location__map"><iframe src="<?php echo esc_url( $map_url ); ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="<?php echo esc_attr( $location_name ?: __( 'Location map', 'shopblocks-wp' ) ); ?>"></iframe></div><?php endif; ?>
				<div class="shopblocks-location__details">
					<?php if ( $location_name ) : ?><h2 class="shopblocks-location__title"><?php echo esc_html( $location_name ); ?></h2><?php endif; ?>
					<?php if ( $location_address ) : ?><p class="shopblocks-location__address"><?php echo nl2br( esc_html( $location_address ) ); ?></p><?php endif; ?>
					<?php if ( $location_phone ) : ?><p class="shopblocks-location__phone"><a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $location_phone ) ); ?>"><?php echo esc_html( $location_phone ); ?></a></p><?php endif; ?>
					<?php if ( $location_email ) : ?><p class="shopblocks-location__email"><a href="mailto:<?php echo esc_attr( antispambot( $location_email ) ); ?>"><?php echo esc_html( antispambot( $location_email ) ); ?></a></p><?php endif; ?>
				</div>
			</section>
			<?php endif; ?>

			<article class="shopblocks-article__content">
				<div class="shopblocks-gutenberg-content"><?php the_content(); ?></div>
			</article>
			<?php echo shopblocks_render_faqs( $faqs, $faq_heading ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
	</main>
<?php endwhile;
shopblocks_render_theme_footer();
