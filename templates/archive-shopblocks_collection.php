<?php get_header(); ?>
<div class="shopblocks-collection-archive-wrapper">
    <header class="shopblocks-collection-header">
        <h1 class="shopblocks-collection-archive-title">Our Collections</h1>
        <p>Browse curated product collections designed to match your vibe, your needs, and your lifestyle.</p>
    </header>
    <div class="shopblocks-collection-grid">
        <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
            <div class="shopblocks-collection-card">
                <a href="<?php the_permalink(); ?>">
                    <?php if (has_post_thumbnail()) the_post_thumbnail('medium'); ?>
                    <h2 class="shopblocks-collection-name"><?php the_title(); ?></h2>
                </a>
                <div class="shopblocks-collection-excerpt"><?php the_excerpt(); ?></div>
            </div>
        <?php endwhile; else : ?>
            <p>No collections found.</p>
        <?php endif; ?>
    </div>
</div>
<?php get_footer(); ?>
