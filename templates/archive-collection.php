<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header(); ?>
<main class="shopblocks-collections">
	<header class="shopblocks-collections__header">
		<h1><?php post_type_archive_title(); ?></h1>
		<p><?php esc_html_e( 'Browse our curated product collections.', 'shopblocks-wp' ); ?></p>
	</header>
	<div class="shopblocks-collections__grid">
		<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
			<article <?php post_class( 'shopblocks-collection-card' ); ?>>
				<a href="<?php the_permalink(); ?>" class="shopblocks-collection-card__image"><?php if ( has_post_thumbnail() ) { the_post_thumbnail( 'medium_large' ); } ?></a>
				<div class="shopblocks-collection-card__content"><h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2><?php the_excerpt(); ?></div>
			</article>
		<?php endwhile; the_posts_pagination(); else : ?><p><?php esc_html_e( 'No collections found.', 'shopblocks-wp' ); ?></p><?php endif; ?>
	</div>
</main>
<?php get_footer();
