<?php
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

if ( have_posts() ) :
  while ( have_posts() ) : the_post(); ?>
    <main id="primary" class="site-main">
      <article <?php post_class(); ?>>
        <div class="entry-content">
          <?php the_content(); ?>
        </div>
      </article>
    </main>
  <?php endwhile;
endif;

get_footer();
