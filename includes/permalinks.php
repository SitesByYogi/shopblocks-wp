<?php
/**
 * Configurable ShopBlocks permalink bases.
 *
 * @package ShopBlocksWP
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return the configured Blog URL base.
 */
function shopblocks_get_blog_slug() {
	$slug = sanitize_title( (string) get_option( 'shopblocks_blog_slug', 'blogs' ) );
	return '' !== $slug ? $slug : 'blogs';
}

/**
 * Return the configured Collection URL base.
 */
function shopblocks_get_collection_slug() {
	$slug = sanitize_title( (string) get_option( 'shopblocks_collection_slug', 'collections' ) );
	return '' !== $slug ? $slug : 'collections';
}

/**
 * Sanitize a ShopBlocks permalink base.
 *
 * @param mixed  $value    Submitted value.
 * @param string $fallback Default slug.
 * @return string
 */
function shopblocks_sanitize_permalink_base( $value, $fallback ) {
	$value = sanitize_title( wp_unslash( (string) $value ) );
	return '' !== $value ? $value : $fallback;
}

/**
 * Sanitize Blog slug and prevent it from matching the Collection slug.
 */
function shopblocks_sanitize_blog_slug( $value ) {
	$slug = shopblocks_sanitize_permalink_base( $value, 'blogs' );
	$collection_slug = isset( $_POST['shopblocks_collection_slug'] )
		? shopblocks_sanitize_permalink_base( wp_unslash( $_POST['shopblocks_collection_slug'] ), 'collections' )
		: shopblocks_get_collection_slug(); // phpcs:ignore WordPress.Security.NonceVerification.Missing

	if ( $slug === $collection_slug ) {
		add_settings_error(
			'shopblocks_blog_slug',
			'shopblocks_duplicate_permalink_base',
			__( 'The Blog and Collection URL bases cannot be the same. The Blog URL base was not changed.', 'shopblocks-wp' ),
			'error'
		);
		return shopblocks_get_blog_slug();
	}

	return $slug;
}

/**
 * Sanitize Collection slug and prevent it from matching the Blog slug.
 */
function shopblocks_sanitize_collection_slug( $value ) {
	$slug = shopblocks_sanitize_permalink_base( $value, 'collections' );
	$blog_slug = isset( $_POST['shopblocks_blog_slug'] )
		? shopblocks_sanitize_permalink_base( wp_unslash( $_POST['shopblocks_blog_slug'] ), 'blogs' )
		: shopblocks_get_blog_slug(); // phpcs:ignore WordPress.Security.NonceVerification.Missing

	if ( $slug === $blog_slug ) {
		add_settings_error(
			'shopblocks_collection_slug',
			'shopblocks_duplicate_permalink_base',
			__( 'The Blog and Collection URL bases cannot be the same. The Collection URL base was not changed.', 'shopblocks-wp' ),
			'error'
		);
		return shopblocks_get_collection_slug();
	}

	return $slug;
}

/**
 * Queue one rewrite flush after a permalink option actually changes.
 */
function shopblocks_schedule_permalink_flush( $old_value, $value ) {
	if ( (string) $old_value !== (string) $value ) {
		update_option( 'shopblocks_flush_rewrite_rules', 1, false );
	}
}
add_action( 'update_option_shopblocks_blog_slug', 'shopblocks_schedule_permalink_flush', 10, 2 );
add_action( 'update_option_shopblocks_collection_slug', 'shopblocks_schedule_permalink_flush', 10, 2 );

/** Queue the first rewrite flush when an upgraded site saves these options for the first time. */
function shopblocks_schedule_permalink_flush_on_add() {
	update_option( 'shopblocks_flush_rewrite_rules', 1, false );
}
add_action( 'add_option_shopblocks_blog_slug', 'shopblocks_schedule_permalink_flush_on_add' );
add_action( 'add_option_shopblocks_collection_slug', 'shopblocks_schedule_permalink_flush_on_add' );

/**
 * Flush once on the next admin request, after post types are registered.
 */
function shopblocks_maybe_flush_rewrite_rules() {
	if ( ! is_admin() || ! get_option( 'shopblocks_flush_rewrite_rules', 0 ) ) {
		return;
	}

	flush_rewrite_rules( false );
	delete_option( 'shopblocks_flush_rewrite_rules' );
}
add_action( 'admin_init', 'shopblocks_maybe_flush_rewrite_rules', 99 );

/**
 * Return human-readable collisions for a URL base.
 *
 * These are warnings only because an administrator may be intentionally
 * replacing an existing route while coordinating redirects.
 *
 * @param string $slug          URL base.
 * @param string $own_post_type ShopBlocks post type that owns the slug.
 * @return array
 */
function shopblocks_get_permalink_conflicts( $slug, $own_post_type ) {
	$conflicts = array();
	$slug      = sanitize_title( (string) $slug );

	if ( '' === $slug ) {
		return $conflicts;
	}

	$page = get_page_by_path( $slug, OBJECT, 'page' );
	if ( $page instanceof WP_Post ) {
		$conflicts[] = sprintf(
			/* translators: %s: URL base. */
			__( 'A WordPress Page currently uses /%s/.', 'shopblocks-wp' ),
			$slug
		);
	}

	global $wp_post_types;
	if ( is_array( $wp_post_types ) ) {
		foreach ( $wp_post_types as $post_type => $object ) {
			if ( $post_type === $own_post_type || ! is_object( $object ) || ! is_array( $object->rewrite ) || empty( $object->rewrite['slug'] ) ) {
				continue;
			}
			$other_slug = trim( (string) $object->rewrite['slug'], '/' );
			if ( $slug === $other_slug ) {
				$conflicts[] = sprintf(
					/* translators: 1: URL base, 2: post type label. */
					__( 'The /%1$s/ base is also registered by the %2$s post type.', 'shopblocks-wp' ),
					$slug,
					isset( $object->labels->singular_name ) ? $object->labels->singular_name : $post_type
				);
			}
		}
	}

	return array_values( array_unique( $conflicts ) );
}
