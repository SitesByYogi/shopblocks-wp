<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Theme-agnostic "page-like" behavior for the `collection` CPT.
 * - Block themes: strip title/meta/comments/nav/featured-image blocks.
 * - Classic themes: render single Collections with the theme's page template.
 * - Disable comments on Collections.
 * - Keep Storefront unhooks to remove its entry header/meta.
 */

/** Helper */
function shopblocks_is_collection_context() {
    return is_singular('collection') || is_post_type_archive('collection');
}

/** BLOCK THEMES: remove post-like blocks on Collections */
function shopblocks_strip_blocks_on_collections( $content, $block ) {
    if ( ! shopblocks_is_collection_context() ) {
        return $content;
    }
    // Remove common post UI pieces to feel "page-like"
    $to_strip = array(
        'core/post-title',
        'core/post-author',
        'core/post-author-name',
        'core/post-date',
        'core/post-terms',
        'core/post-navigation-link',
        'core/post-featured-image',
        'core/post-comments-form',
        'core/comments',
        'core/comments-title',
        'core/comments-query-loop',
    );
    if ( isset( $block['blockName'] ) && in_array( $block['blockName'], $to_strip, true ) ) {
        return '';
    }
    return $content;
}
add_filter( 'render_block', 'shopblocks_strip_blocks_on_collections', 10, 2 );

/** CLASSIC THEMES: use the theme's PAGE template for single Collections (keeps header/menu) */
add_filter( 'template_include', function( $template ) {
    if ( is_singular('collection') && ! wp_is_block_theme() ) {
        $page_tpl = get_query_template( 'page' );
        if ( $page_tpl ) {
            return $page_tpl; // render like a Page, not a Post
        }
    }
    return $template;
}, 50 );

/** Disable comments/pings on Collections (front + admin) */
add_filter( 'comments_open', function( $open, $post_id ) {
    return ( get_post_type( $post_id ) === 'collection' ) ? false : $open;
}, 10, 2 );
add_filter( 'pings_open', function( $open, $post_id ) {
    return ( get_post_type( $post_id ) === 'collection' ) ? false : $open;
}, 10, 2 );

/** STOREFRONT: unhook entry header/meta (safe no-ops on other themes) */
add_action( 'template_redirect', function() {
    if ( is_singular('collection') ) {
        remove_action( 'storefront_single_post', 'storefront_post_header', 10 );
        remove_action( 'storefront_single_post', 'storefront_post_meta', 20 );
        remove_action( 'storefront_page', 'storefront_page_header', 10 );
    }
    if ( is_post_type_archive('collection') ) {
        remove_action( 'storefront_page', 'storefront_page_header', 10 );
        remove_action( 'storefront_loop_post', 'storefront_post_header', 10 );
        remove_action( 'storefront_loop_post', 'storefront_post_meta', 20 );
    }
}, 0 );
