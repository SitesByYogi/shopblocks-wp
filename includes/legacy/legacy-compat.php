<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * ShopBlocks runtime compatibility helpers.
 *
 * One-time migration/conversion tools were retired in 2.2.5. This file now
 * contains only compatibility behavior that may still be required by content
 * migrated on earlier ShopBlocks installations.
 */

/* =========================================================
 * ARCHIVE / FEED COMPATIBILITY
 * ========================================================= */

/**
 * Keep ShopBlocks Blogs visible in author archives and the main site feed.
 * Category/tag archives already know about ShopBlocks Blogs because the CPT is
 * registered against those native taxonomies.
 */
function shopblocks_include_blogs_in_core_queries( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) { return; }

	if ( $query->is_author() || $query->is_feed() ) {
		$current = $query->get( 'post_type' );
		if ( empty( $current ) || 'post' === $current ) {
			$query->set( 'post_type', array( 'post', 'shopblocks_blog' ) );
		} elseif ( is_array( $current ) && ! in_array( 'shopblocks_blog', $current, true ) ) {
			$current[] = 'shopblocks_blog';
			$query->set( 'post_type', array_values( array_unique( $current ) ) );
		}
	}
}
add_action( 'pre_get_posts', 'shopblocks_include_blogs_in_core_queries', 20 );



/* =========================================================
 * LEGACY COLLECTION TEMPLATE COMPATIBILITY
 * ========================================================= */

/** Keep migrated legacy Collections on the site's original theme/Elementor single template by default. */
function shopblocks_is_legacy_collection_compat( $post_id ) {
	return 'collections' === get_post_meta( $post_id, '_shopblocks_legacy_post_type', true )
		&& '0' !== get_post_meta( $post_id, '_shopblocks_legacy_template_compat', true );
}

function shopblocks_legacy_collection_template_meta_box() {
	global $post;
	if ( ! $post || 'collection' !== $post->post_type || 'collections' !== get_post_meta( $post->ID, '_shopblocks_legacy_post_type', true ) ) { return; }
	add_meta_box( 'shopblocks_legacy_template_compat', __( 'Legacy Template Compatibility', 'shopblocks-wp' ), 'shopblocks_legacy_template_compat_box', 'collection', 'side', 'high' );
}
add_action( 'add_meta_boxes_collection', 'shopblocks_legacy_collection_template_meta_box' );

function shopblocks_legacy_template_compat_box( $post ) {
	wp_nonce_field( 'shopblocks_save_legacy_template_compat', 'shopblocks_legacy_template_compat_nonce' );
	$enabled = shopblocks_is_legacy_collection_compat( $post->ID );
	?>
	<label><input type="checkbox" name="shopblocks_legacy_template_compat" value="1" <?php checked( $enabled ); ?>> <?php esc_html_e( 'Use the original theme/Elementor single template for this migrated Collection.', 'shopblocks-wp' ); ?></label>
	<p class="description"><?php esc_html_e( 'Leave enabled until this page has been rebuilt for the modern ShopBlocks Collection template.', 'shopblocks-wp' ); ?></p>
	<?php
}

function shopblocks_save_legacy_template_compat( $post_id ) {
	if ( ! isset( $_POST['shopblocks_legacy_template_compat_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['shopblocks_legacy_template_compat_nonce'] ) ), 'shopblocks_save_legacy_template_compat' ) ) { return; }
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) { return; }
	update_post_meta( $post_id, '_shopblocks_legacy_template_compat', isset( $_POST['shopblocks_legacy_template_compat'] ) ? '1' : '0' );
}
add_action( 'save_post_collection', 'shopblocks_save_legacy_template_compat', 50 );
