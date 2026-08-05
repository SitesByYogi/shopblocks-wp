<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
shopblocks_render_theme_header();
while ( have_posts() ) : the_post();
	$post_id      = get_the_ID();
	$product_ids  = get_post_meta( $post_id, '_shopblocks_product_ids', true );
	$related_refs = get_post_meta( $post_id, '_shopblocks_related_content_refs', true );
	if ( ! $related_refs ) { $related_refs = shopblocks_legacy_related_refs( get_post_meta( $post_id, '_shopblocks_related_blog_ids', true ) ); }
	$show_related = get_post_meta( $post_id, '_shopblocks_show_related_content', true );
	$show_related = '' === $show_related ? '1' : $show_related;
	$faqs          = get_post_meta( $post_id, '_shopblocks_collection_faqs', true );
	?>
	<main class="shopblocks-page shopblocks-page--collection">
		<div class="shopblocks-page__inner">
			<article class="shopblocks-collection shopblocks-single-collection">
				<header class="shopblocks-collection__hero shopblocks-collection-hero">
					<h1 class="shopblocks-collection__title"><?php the_title(); ?></h1>
					<?php if ( has_excerpt() ) : ?><div class="shopblocks-collection__intro shopblocks-collection-hero__intro"><?php the_excerpt(); ?></div><?php endif; ?>
				</header>
				<?php if ( $product_ids ) : ?>
					<section class="shopblocks-collection__products shopblocks-collection-products" aria-label="<?php esc_attr_e( 'Featured products', 'shopblocks-wp' ); ?>">
						<?php echo do_shortcode( '[shopblocks_products ids="' . esc_attr( $product_ids ) . '" limit="48" columns="4" layout="grid"]' ); ?>
					</section>
				<?php endif; ?>
				<?php if ( '1' === $show_related && $related_refs ) :
					echo shopblocks_render_related_content( $related_refs, array(
						'heading' => get_post_meta( $post_id, '_shopblocks_related_heading', true ) ?: __( 'Check out our Blog', 'shopblocks-wp' ),
						'button'  => get_post_meta( $post_id, '_shopblocks_related_button_label', true ) ?: __( 'Learn More', 'shopblocks-wp' ),
						'class'   => 'shopblocks-related-content--collection',
					) );
				endif; ?>
				<div class="shopblocks-collection__article">
					<div class="shopblocks-gutenberg-content shopblocks-collection__content shopblocks-collection-content"><?php the_content(); ?></div>
					<?php echo shopblocks_render_faqs( $faqs, get_post_meta( $post_id, '_shopblocks_faq_heading', true ) ); ?>
				</div>
			</article>
		</div>
	</main>
<?php endwhile;
shopblocks_render_theme_footer();
