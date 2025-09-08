<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Storefront theme compatibility for Collections CPT.
 * Hide theme-generated H1/date/author on single + archive Collection views.
 */
add_action('wp', function () {
    // Single Collection
    if ( is_singular('collection') ) {
        // Single post header (prints H1, meta)
        remove_action('storefront_single_post', 'storefront_post_header', 10);
        remove_action('storefront_single_post', 'storefront_post_meta', 20);

        // Some setups use the generic page header; remove just in case
        remove_action('storefront_page', 'storefront_page_header', 10);

        // Optional: remove extras you may not want beneath content
        // remove_action('storefront_single_post', 'storefront_post_nav', 10);
        // remove_action('storefront_single_post', 'storefront_display_comments', 20);
        // remove_action('storefront_single_post', 'storefront_post_author', 30);
        // remove_action('storefront_single_post', 'storefront_related_posts', 30);
    }

    // Collections archive
    if ( is_post_type_archive('collection') ) {
        // Archive/page header
        remove_action('storefront_page', 'storefront_page_header', 10);

        // Loop item title/meta if your archive uses post loops
        remove_action('storefront_loop_post', 'storefront_post_header', 10);
        remove_action('storefront_loop_post', 'storefront_post_meta', 20);
    }
});
