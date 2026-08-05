<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header(); ?>
<main class="shopblocks-blog-archive">
	<header class="shopblocks-blog-archive__header"><h1><?php post_type_archive_title(); ?></h1></header>
	<div class="shopblocks-blog-archive__grid">
		<?php while ( have_posts() ) : the_post(); ?>
			<article class="shopblocks-blog-archive-card">
				<a class="shopblocks-blog-archive-card__image" href="<?php the_permalink(); ?>"><?php if ( has_post_thumbnail() ) { the_post_thumbnail( 'large' ); } ?></a>
				<div class="shopblocks-blog-archive-card__content"><h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2><?php the_excerpt(); ?></div>
			</article>
		<?php endwhile; ?>
	</div>
	<?php the_posts_pagination(); ?>
</main>
<?php get_footer();
