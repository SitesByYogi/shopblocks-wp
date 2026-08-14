<?php
/**
 * ShopBlocks content types and editorial fields.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Register the public Blogs post type without altering native Posts. */
function shopblocks_register_blogs_cpt() {
	$labels = array(
		'name' => __( 'Blogs', 'shopblocks-wp' ), 'singular_name' => __( 'Blog', 'shopblocks-wp' ),
		'menu_name' => __( 'Blogs', 'shopblocks-wp' ), 'name_admin_bar' => __( 'Blog', 'shopblocks-wp' ),
		'add_new' => __( 'Add New', 'shopblocks-wp' ), 'add_new_item' => __( 'Add New Blog', 'shopblocks-wp' ),
		'new_item' => __( 'New Blog', 'shopblocks-wp' ), 'edit_item' => __( 'Edit Blog', 'shopblocks-wp' ),
		'view_item' => __( 'View Blog', 'shopblocks-wp' ), 'all_items' => __( 'All Blogs', 'shopblocks-wp' ),
		'search_items' => __( 'Search Blogs', 'shopblocks-wp' ), 'not_found' => __( 'No blogs found.', 'shopblocks-wp' ),
		'not_found_in_trash' => __( 'No blogs found in Trash.', 'shopblocks-wp' ),
	);
	register_post_type( 'shopblocks_blog', array(
		'labels' => $labels, 'public' => true, 'has_archive' => true,
		'rewrite' => array( 'slug' => 'blogs', 'with_front' => false ), 'show_in_rest' => true,
		'show_in_menu' => 'shopblocks-settings',
		'supports' => array( 'title', 'editor', 'thumbnail', 'excerpt', 'author', 'revisions' ),
		'taxonomies' => array( 'category', 'post_tag' ), 'menu_icon' => 'dashicons-welcome-write-blog',
		'show_in_nav_menus' => true, 'publicly_queryable' => true,
	) );
}
add_action( 'init', 'shopblocks_register_blogs_cpt' );

/** Meta boxes. */
function shopblocks_add_editorial_meta_boxes() {
	add_meta_box( 'shopblocks_content_layout', __( 'Content Template', 'shopblocks-wp' ), 'shopblocks_content_layout_meta_box', 'shopblocks_blog', 'side', 'high' );
	add_meta_box( 'shopblocks_conversion', __( 'Hero & Conversion', 'shopblocks-wp' ), 'shopblocks_conversion_meta_box', 'shopblocks_blog', 'normal', 'high' );
	add_meta_box( 'shopblocks_blog_sidebar', __( 'Blog Sidebar', 'shopblocks-wp' ), 'shopblocks_blog_sidebar_meta_box', 'shopblocks_blog', 'normal', 'default' );
	add_meta_box( 'shopblocks_blog_faqs', __( 'Blog FAQs', 'shopblocks-wp' ), 'shopblocks_blog_faqs_meta_box', 'shopblocks_blog', 'normal', 'default' );
	add_meta_box( 'shopblocks_collection_related_content', __( 'Related Content Panel', 'shopblocks-wp' ), 'shopblocks_collection_related_content_meta_box', 'collection', 'normal', 'high' );
	add_meta_box( 'shopblocks_collection_faqs', __( 'Collection FAQs', 'shopblocks-wp' ), 'shopblocks_collection_faqs_meta_box', 'collection', 'normal', 'default' );
}
add_action( 'add_meta_boxes', 'shopblocks_add_editorial_meta_boxes' );


/** Blog CPT presentation mode. Existing Blogs default to the 2.0 Blog template. */
function shopblocks_content_layout_meta_box( $post ) {
	$layout = get_post_meta( $post->ID, '_shopblocks_content_layout', true );
	$layout = in_array( $layout, array( 'blog', 'article' ), true ) ? $layout : 'blog';
	?>
	<p><label for="shopblocks_content_layout"><strong><?php esc_html_e( 'Template', 'shopblocks-wp' ); ?></strong></label></p>
	<select class="widefat" id="shopblocks_content_layout" name="shopblocks_content_layout">
		<option value="blog" <?php selected( 'blog', $layout ); ?>><?php esc_html_e( 'Blog Post — hero + modular sidebar', 'shopblocks-wp' ); ?></option>
		<option value="article" <?php selected( 'article', $layout ); ?>><?php esc_html_e( 'Article / Landing Article — conversion hero + full-width content', 'shopblocks-wp' ); ?></option>
	</select>
	<p class="description"><?php esc_html_e( 'Article mode is designed for service, location, treatment, and lead-generation content. It does not require WooCommerce.', 'shopblocks-wp' ); ?></p>
	<?php
}

/** Shared hero/conversion settings for Blog and Article layouts. */
function shopblocks_conversion_meta_box( $post ) {
	wp_nonce_field( 'shopblocks_save_blog_sidebar', 'shopblocks_blog_sidebar_nonce' );
	$fields = array(
		'eyebrow' => get_post_meta( $post->ID, '_shopblocks_hero_eyebrow', true ),
		'description' => get_post_meta( $post->ID, '_shopblocks_hero_description', true ),
		'primary_label' => get_post_meta( $post->ID, '_shopblocks_primary_cta_label', true ),
		'primary_url' => get_post_meta( $post->ID, '_shopblocks_primary_cta_url', true ),
		'secondary_label' => get_post_meta( $post->ID, '_shopblocks_secondary_cta_label', true ),
		'secondary_url' => get_post_meta( $post->ID, '_shopblocks_secondary_cta_url', true ),
		'hero_form' => get_post_meta( $post->ID, '_shopblocks_hero_form_shortcode', true ),
		'location_name' => get_post_meta( $post->ID, '_shopblocks_location_name', true ),
		'location_address' => get_post_meta( $post->ID, '_shopblocks_location_address', true ),
		'location_phone' => get_post_meta( $post->ID, '_shopblocks_location_phone', true ),
		'location_email' => get_post_meta( $post->ID, '_shopblocks_location_email', true ),
		'map_url' => get_post_meta( $post->ID, '_shopblocks_location_map_url', true ),
	);
	?>
	<p><label><strong><?php esc_html_e( 'Hero eyebrow', 'shopblocks-wp' ); ?></strong><br><input class="widefat" name="shopblocks_hero_eyebrow" value="<?php echo esc_attr( $fields['eyebrow'] ); ?>" placeholder="LIMITED AVAILABILITY"></label></p>
	<p><label><strong><?php esc_html_e( 'Hero description', 'shopblocks-wp' ); ?></strong><br><textarea class="widefat" rows="3" name="shopblocks_hero_description"><?php echo esc_textarea( $fields['description'] ); ?></textarea></label></p>
	<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
		<p><label><strong><?php esc_html_e( 'Primary CTA label', 'shopblocks-wp' ); ?></strong><br><input class="widefat" name="shopblocks_primary_cta_label" value="<?php echo esc_attr( $fields['primary_label'] ); ?>" placeholder="Book Free Consultation"></label></p>
		<p><label><strong><?php esc_html_e( 'Primary CTA URL', 'shopblocks-wp' ); ?></strong><br><input class="widefat" type="url" name="shopblocks_primary_cta_url" value="<?php echo esc_attr( $fields['primary_url'] ); ?>"></label></p>
		<p><label><strong><?php esc_html_e( 'Secondary CTA label', 'shopblocks-wp' ); ?></strong><br><input class="widefat" name="shopblocks_secondary_cta_label" value="<?php echo esc_attr( $fields['secondary_label'] ); ?>" placeholder="Find a Location"></label></p>
		<p><label><strong><?php esc_html_e( 'Secondary CTA URL', 'shopblocks-wp' ); ?></strong><br><input class="widefat" type="url" name="shopblocks_secondary_cta_url" value="<?php echo esc_attr( $fields['secondary_url'] ); ?>"></label></p>
	</div>
	<p><label><strong><?php esc_html_e( 'Hero form / booking shortcode', 'shopblocks-wp' ); ?></strong><br><input class="widefat code" name="shopblocks_hero_form_shortcode" value="<?php echo esc_attr( $fields['hero_form'] ); ?>" placeholder="[wpforms id=&quot;123&quot;]"></label></p>
	<p class="description"><?php esc_html_e( 'Use any provider shortcode. Leave blank to remove the conversion panel. A global lead form can also be configured in ShopBlocks settings.', 'shopblocks-wp' ); ?></p>
	<hr>
	<p><strong><?php esc_html_e( 'Optional Location Section (Article mode)', 'shopblocks-wp' ); ?></strong></p>
	<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
		<p><label><?php esc_html_e( 'Location name', 'shopblocks-wp' ); ?><br><input class="widefat" name="shopblocks_location_name" value="<?php echo esc_attr( $fields['location_name'] ); ?>"></label></p>
		<p><label><?php esc_html_e( 'Phone', 'shopblocks-wp' ); ?><br><input class="widefat" name="shopblocks_location_phone" value="<?php echo esc_attr( $fields['location_phone'] ); ?>"></label></p>
		<p><label><?php esc_html_e( 'Address', 'shopblocks-wp' ); ?><br><textarea class="widefat" rows="2" name="shopblocks_location_address"><?php echo esc_textarea( $fields['location_address'] ); ?></textarea></label></p>
		<p><label><?php esc_html_e( 'Email', 'shopblocks-wp' ); ?><br><input class="widefat" type="email" name="shopblocks_location_email" value="<?php echo esc_attr( $fields['location_email'] ); ?>"></label></p>
	</div>
	<p><label><strong><?php esc_html_e( 'Google Maps embed URL', 'shopblocks-wp' ); ?></strong><br><input class="widefat" type="url" name="shopblocks_location_map_url" value="<?php echo esc_attr( $fields['map_url'] ); ?>" placeholder="https://www.google.com/maps/embed?... "></label></p>
	<?php
}

function shopblocks_blog_sidebar_meta_box( $post ) {
	$stored_product_ids = get_post_meta( $post->ID, '_shopblocks_sidebar_product_ids', true );
	$default_product_ids = get_option( 'shopblocks_default_blog_sidebar_products', '' );
	$disable_products   = '1' === get_post_meta( $post->ID, '_shopblocks_disable_sidebar_products', true );
	$product_ids        = $stored_product_ids ?: $default_product_ids;
	$helpful           = get_post_meta( $post->ID, '_shopblocks_helpful_links', true );
	$helpful_heading   = get_post_meta( $post->ID, '_shopblocks_helpful_links_heading', true );
	$newsletter        = get_post_meta( $post->ID, '_shopblocks_show_newsletter', true );
	$newsletter        = '' === $newsletter ? '1' : $newsletter;
	$newsletter_title  = get_post_meta( $post->ID, '_shopblocks_newsletter_title', true );
	$newsletter_text   = get_post_meta( $post->ID, '_shopblocks_newsletter_description', true );
	$custom_content    = get_post_meta( $post->ID, '_shopblocks_sidebar_custom_content', true );
	$cta_image         = get_post_meta( $post->ID, '_shopblocks_sidebar_cta_image', true );
	$cta_heading       = get_post_meta( $post->ID, '_shopblocks_sidebar_cta_heading', true );
	$cta_text          = get_post_meta( $post->ID, '_shopblocks_sidebar_cta_text', true );
	$cta_label         = get_post_meta( $post->ID, '_shopblocks_sidebar_cta_label', true );
	$cta_url           = get_post_meta( $post->ID, '_shopblocks_sidebar_cta_url', true );
	$form_heading      = get_post_meta( $post->ID, '_shopblocks_sidebar_form_heading', true );
	$form_shortcode    = get_post_meta( $post->ID, '_shopblocks_sidebar_form_shortcode', true );
	?>
	<p><strong><?php esc_html_e( 'Lead-generation modules', 'shopblocks-wp' ); ?></strong></p>
	<p><label><?php esc_html_e( 'CTA image URL', 'shopblocks-wp' ); ?><br><input type="url" class="widefat" name="shopblocks_sidebar_cta_image" value="<?php echo esc_attr( $cta_image ); ?>"></label></p>
	<p><label><?php esc_html_e( 'CTA heading', 'shopblocks-wp' ); ?><br><input class="widefat" name="shopblocks_sidebar_cta_heading" value="<?php echo esc_attr( $cta_heading ); ?>"></label></p>
	<p><label><?php esc_html_e( 'CTA description', 'shopblocks-wp' ); ?><br><textarea class="widefat" rows="2" name="shopblocks_sidebar_cta_text"><?php echo esc_textarea( $cta_text ); ?></textarea></label></p>
	<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
		<p><label><?php esc_html_e( 'CTA button label', 'shopblocks-wp' ); ?><br><input class="widefat" name="shopblocks_sidebar_cta_label" value="<?php echo esc_attr( $cta_label ); ?>"></label></p>
		<p><label><?php esc_html_e( 'CTA button URL', 'shopblocks-wp' ); ?><br><input type="url" class="widefat" name="shopblocks_sidebar_cta_url" value="<?php echo esc_attr( $cta_url ); ?>"></label></p>
	</div>
	<p><label><?php esc_html_e( 'Sidebar form heading', 'shopblocks-wp' ); ?><br><input class="widefat" name="shopblocks_sidebar_form_heading" value="<?php echo esc_attr( $form_heading ); ?>"></label></p>
	<p><label><?php esc_html_e( 'Sidebar form shortcode', 'shopblocks-wp' ); ?><br><input class="widefat code" name="shopblocks_sidebar_form_shortcode" value="<?php echo esc_attr( $form_shortcode ); ?>" placeholder="[gravityform id=&quot;4&quot;]"></label></p>
	<hr>
	<p><strong><?php esc_html_e( 'Editorial modules', 'shopblocks-wp' ); ?></strong></p>
	<p><label><input type="checkbox" name="shopblocks_show_newsletter" value="1" <?php checked( '1', $newsletter ); ?>> <?php esc_html_e( 'Display newsletter when a global newsletter shortcode is configured', 'shopblocks-wp' ); ?></label></p>
	<p><label><strong><?php esc_html_e( 'Newsletter heading', 'shopblocks-wp' ); ?></strong><br><input type="text" class="widefat" name="shopblocks_newsletter_title" value="<?php echo esc_attr( $newsletter_title ?: __( 'Join Our Mailing List.', 'shopblocks-wp' ) ); ?>"></label></p>
	<p><label><strong><?php esc_html_e( 'Newsletter supporting text', 'shopblocks-wp' ); ?></strong><br><textarea class="widefat" rows="2" name="shopblocks_newsletter_description"><?php echo esc_textarea( $newsletter_text ); ?></textarea></label></p>
	<?php if ( shopblocks_has_woocommerce() ) : ?>
		<hr><p><strong><?php esc_html_e( 'Commerce module — Sidebar Products', 'shopblocks-wp' ); ?></strong></p>
		<input type="text" class="widefat" name="shopblocks_sidebar_product_ids" value="<?php echo esc_attr( $product_ids ); ?>" placeholder="123, 456">
		<p class="description">
			<?php if ( ! $stored_product_ids && $default_product_ids ) : ?>
				<?php esc_html_e( 'Using the global ShopBlocks default. Change these IDs to create a Blog-specific override.', 'shopblocks-wp' ); ?>
			<?php else : ?>
				<?php esc_html_e( 'Optional WooCommerce product IDs in display order. Blog-specific values override the global default.', 'shopblocks-wp' ); ?>
			<?php endif; ?>
		</p>
		<p><label><input type="checkbox" name="shopblocks_disable_sidebar_products" value="1" <?php checked( $disable_products ); ?>> <?php esc_html_e( 'Disable sidebar products for this Blog', 'shopblocks-wp' ); ?></label></p>
	<?php else : ?>
		<input type="hidden" name="shopblocks_sidebar_product_ids" value="<?php echo esc_attr( $stored_product_ids ); ?>">
		<p class="description"><?php esc_html_e( 'WooCommerce is not active. Product modules are disabled; Blog, Article, CTA, form, FAQ, and link features remain available.', 'shopblocks-wp' ); ?></p>
	<?php endif; ?>
	<hr>
	<p><label><strong><?php esc_html_e( 'Helpful Links heading', 'shopblocks-wp' ); ?></strong><br><input type="text" class="widefat" name="shopblocks_helpful_links_heading" value="<?php echo esc_attr( $helpful_heading ?: __( 'Other Helpful Links', 'shopblocks-wp' ) ); ?>"></label></p>
	<textarea class="widefat" rows="5" name="shopblocks_helpful_links" placeholder="Consultation | /contact/&#10;Locations | /locations/"><?php echo esc_textarea( $helpful ); ?></textarea>
	<p class="description"><?php esc_html_e( 'Enter one link per line using Label | URL.', 'shopblocks-wp' ); ?></p>
	<hr>
	<p><label><strong><?php esc_html_e( 'Optional custom sidebar content', 'shopblocks-wp' ); ?></strong><br><textarea class="widefat code" rows="5" name="shopblocks_sidebar_custom_content" placeholder="[shortcode] or simple HTML"><?php echo esc_textarea( $custom_content ); ?></textarea></label></p>
	<?php
}

function shopblocks_blog_faqs_meta_box( $post ) {
	$faqs    = get_post_meta( $post->ID, '_shopblocks_blog_faqs', true );
	$heading = get_post_meta( $post->ID, '_shopblocks_blog_faq_heading', true );
	?>
	<p><label><strong><?php esc_html_e( 'FAQ heading', 'shopblocks-wp' ); ?></strong><br><input class="widefat" name="shopblocks_blog_faq_heading" value="<?php echo esc_attr( $heading ?: __( 'Frequently Asked Questions', 'shopblocks-wp' ) ); ?>"></label></p>
	<p><?php esc_html_e( 'Enter one FAQ per line using Question | Answer. These render after the Block Editor article content and before the mobile sidebar.', 'shopblocks-wp' ); ?></p>
	<textarea class="widefat" rows="10" name="shopblocks_blog_faqs" placeholder="What is HPLC? | HPLC is an analytical testing method."><?php echo esc_textarea( $faqs ); ?></textarea>
	<?php
}

function shopblocks_collection_related_content_meta_box( $post ) {
	wp_nonce_field( 'shopblocks_save_collection_editorial', 'shopblocks_collection_editorial_nonce' );
	$refs = get_post_meta( $post->ID, '_shopblocks_related_content_refs', true );
	if ( ! $refs ) { $refs = shopblocks_legacy_related_refs( get_post_meta( $post->ID, '_shopblocks_related_blog_ids', true ) ); }
	$heading = get_post_meta( $post->ID, '_shopblocks_related_heading', true );
	$button  = get_post_meta( $post->ID, '_shopblocks_related_button_label', true );
	$enabled = get_post_meta( $post->ID, '_shopblocks_show_related_content', true );
	$enabled = '' === $enabled ? '1' : $enabled;
	?>
	<p><label><input type="checkbox" name="shopblocks_show_related_content" value="1" <?php checked( '1', $enabled ); ?>> <?php esc_html_e( 'Display the related-content panel', 'shopblocks-wp' ); ?></label></p>
	<p><label><strong><?php esc_html_e( 'Panel heading', 'shopblocks-wp' ); ?></strong><br><input class="widefat" type="text" name="shopblocks_related_heading" value="<?php echo esc_attr( $heading ?: __( 'Check out our Blog', 'shopblocks-wp' ) ); ?>"></label></p>
	<p><label><strong><?php esc_html_e( 'Button label', 'shopblocks-wp' ); ?></strong><br><input class="widefat" type="text" name="shopblocks_related_button_label" value="<?php echo esc_attr( $button ?: __( 'Learn More', 'shopblocks-wp' ) ); ?>"></label></p>
	<?php shopblocks_related_content_field( $refs, 'shopblocks_collection_related_content_refs' );
}

/** Mixed selector: regular Posts and ShopBlocks Blogs. */
function shopblocks_related_content_field( $value, $name ) {
	$items = get_posts( array(
		'post_type' => array( 'shopblocks_blog', 'post' ), 'post_status' => 'publish',
		'posts_per_page' => 150, 'orderby' => 'title', 'order' => 'ASC',
	) );
	$selected = shopblocks_parse_content_refs( $value );
	$selected_keys = array();
	foreach ( $selected as $ref ) { $selected_keys[] = $ref['post_type'] . ':' . $ref['post_id']; }
	?>
	<p><strong><?php esc_html_e( 'Related Blogs and Posts', 'shopblocks-wp' ); ?></strong></p>
	<select class="widefat" name="<?php echo esc_attr( $name ); ?>[]" multiple size="10" style="min-height:220px">
		<?php foreach ( $items as $item ) :
			$key = $item->post_type . ':' . $item->ID;
			$type_label = 'shopblocks_blog' === $item->post_type ? __( 'Blog', 'shopblocks-wp' ) : __( 'Post', 'shopblocks-wp' ); ?>
			<option value="<?php echo esc_attr( $key ); ?>" <?php selected( in_array( $key, $selected_keys, true ) ); ?>><?php echo esc_html( get_the_title( $item ) . ' — ' . $type_label . ' (#' . $item->ID . ')' ); ?></option>
		<?php endforeach; ?>
	</select>
	<p class="description"><?php esc_html_e( 'Choose any combination of ShopBlocks Blogs and regular WordPress Posts. Use Ctrl/Cmd-click to select multiple items.', 'shopblocks-wp' ); ?></p>
	<?php
}

function shopblocks_collection_faqs_meta_box( $post ) {
	$faqs = get_post_meta( $post->ID, '_shopblocks_collection_faqs', true );
	$heading = get_post_meta( $post->ID, '_shopblocks_faq_heading', true );
	?>
	<p><label><strong><?php esc_html_e( 'FAQ heading', 'shopblocks-wp' ); ?></strong><br><input class="widefat" name="shopblocks_faq_heading" value="<?php echo esc_attr( $heading ?: __( 'Frequently Asked Questions', 'shopblocks-wp' ) ); ?>"></label></p>
	<p><?php esc_html_e( 'Enter one FAQ per line using Question | Answer. These render automatically after the Block Editor article content.', 'shopblocks-wp' ); ?></p>
	<textarea class="widefat" rows="10" name="shopblocks_collection_faqs" placeholder="What is this collection? | This collection groups related products and educational content."><?php echo esc_textarea( $faqs ); ?></textarea>
	<?php
}

function shopblocks_sanitize_id_list( $raw ) {
	$ids = array_values( array_unique( array_filter( array_map( 'absint', preg_split( '/\s*,\s*/', (string) $raw ) ) ) ) );
	return implode( ',', $ids );
}
function shopblocks_legacy_related_refs( $raw ) {
	$ids = array_filter( array_map( 'absint', preg_split( '/\s*,\s*/', (string) $raw ) ) );
	return implode( ',', array_map( static function( $id ) { return 'shopblocks_blog:' . $id; }, $ids ) );
}
function shopblocks_sanitize_content_refs( $raw ) {
	$values = is_array( $raw ) ? $raw : preg_split( '/\s*,\s*/', (string) $raw );
	$out = array();
	foreach ( $values as $value ) {
		$value = sanitize_text_field( wp_unslash( $value ) );
		if ( preg_match( '/^(shopblocks_blog|post):(\d+)$/', $value, $m ) ) {
			$key = $m[1] . ':' . absint( $m[2] );
			$out[ $key ] = $key;
		} elseif ( ctype_digit( $value ) ) {
			$key = 'shopblocks_blog:' . absint( $value );
			$out[ $key ] = $key;
		}
	}
	return implode( ',', array_values( $out ) );
}
function shopblocks_parse_content_refs( $raw ) {
	$refs = array();
	foreach ( preg_split( '/\s*,\s*/', (string) $raw ) as $value ) {
		if ( preg_match( '/^(shopblocks_blog|post):(\d+)$/', trim( $value ), $m ) ) {
			$refs[] = array( 'post_type' => $m[1], 'post_id' => absint( $m[2] ) );
		} elseif ( ctype_digit( trim( $value ) ) ) {
			$refs[] = array( 'post_type' => 'shopblocks_blog', 'post_id' => absint( $value ) );
		}
	}
	return $refs;
}

function shopblocks_save_blog_sidebar( $post_id ) {
	if ( ! isset( $_POST['shopblocks_blog_sidebar_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['shopblocks_blog_sidebar_nonce'] ) ), 'shopblocks_save_blog_sidebar' ) ) { return; }
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) { return; }
	update_post_meta( $post_id, '_shopblocks_show_newsletter', isset( $_POST['shopblocks_show_newsletter'] ) ? '1' : '0' );
	update_post_meta( $post_id, '_shopblocks_newsletter_title', sanitize_text_field( wp_unslash( $_POST['shopblocks_newsletter_title'] ?? '' ) ) );
	update_post_meta( $post_id, '_shopblocks_newsletter_description', sanitize_textarea_field( wp_unslash( $_POST['shopblocks_newsletter_description'] ?? '' ) ) );
	$submitted_products = shopblocks_sanitize_id_list( $_POST['shopblocks_sidebar_product_ids'] ?? '' );
	$default_products   = shopblocks_sanitize_id_list( get_option( 'shopblocks_default_blog_sidebar_products', '' ) );
	$disable_products   = isset( $_POST['shopblocks_disable_sidebar_products'] ) ? '1' : '0';
	update_post_meta( $post_id, '_shopblocks_disable_sidebar_products', $disable_products );
	if ( '1' === $disable_products || '' === $submitted_products || $submitted_products === $default_products ) {
		delete_post_meta( $post_id, '_shopblocks_sidebar_product_ids' );
	} else {
		update_post_meta( $post_id, '_shopblocks_sidebar_product_ids', $submitted_products );
	}
	update_post_meta( $post_id, '_shopblocks_helpful_links_heading', sanitize_text_field( wp_unslash( $_POST['shopblocks_helpful_links_heading'] ?? '' ) ) );
	update_post_meta( $post_id, '_shopblocks_helpful_links', sanitize_textarea_field( wp_unslash( $_POST['shopblocks_helpful_links'] ?? '' ) ) );
	update_post_meta( $post_id, '_shopblocks_sidebar_custom_content', wp_kses_post( wp_unslash( $_POST['shopblocks_sidebar_custom_content'] ?? '' ) ) );
	update_post_meta( $post_id, '_shopblocks_blog_faqs', sanitize_textarea_field( wp_unslash( $_POST['shopblocks_blog_faqs'] ?? '' ) ) );
	update_post_meta( $post_id, '_shopblocks_blog_faq_heading', sanitize_text_field( wp_unslash( $_POST['shopblocks_blog_faq_heading'] ?? '' ) ) );
	$layout = sanitize_key( wp_unslash( $_POST['shopblocks_content_layout'] ?? 'blog' ) );
	update_post_meta( $post_id, '_shopblocks_content_layout', in_array( $layout, array( 'blog', 'article' ), true ) ? $layout : 'blog' );
	update_post_meta( $post_id, '_shopblocks_hero_eyebrow', sanitize_text_field( wp_unslash( $_POST['shopblocks_hero_eyebrow'] ?? '' ) ) );
	update_post_meta( $post_id, '_shopblocks_hero_description', sanitize_textarea_field( wp_unslash( $_POST['shopblocks_hero_description'] ?? '' ) ) );
	update_post_meta( $post_id, '_shopblocks_primary_cta_label', sanitize_text_field( wp_unslash( $_POST['shopblocks_primary_cta_label'] ?? '' ) ) );
	update_post_meta( $post_id, '_shopblocks_primary_cta_url', esc_url_raw( wp_unslash( $_POST['shopblocks_primary_cta_url'] ?? '' ) ) );
	update_post_meta( $post_id, '_shopblocks_secondary_cta_label', sanitize_text_field( wp_unslash( $_POST['shopblocks_secondary_cta_label'] ?? '' ) ) );
	update_post_meta( $post_id, '_shopblocks_secondary_cta_url', esc_url_raw( wp_unslash( $_POST['shopblocks_secondary_cta_url'] ?? '' ) ) );
	update_post_meta( $post_id, '_shopblocks_hero_form_shortcode', sanitize_text_field( wp_unslash( $_POST['shopblocks_hero_form_shortcode'] ?? '' ) ) );
	update_post_meta( $post_id, '_shopblocks_location_name', sanitize_text_field( wp_unslash( $_POST['shopblocks_location_name'] ?? '' ) ) );
	update_post_meta( $post_id, '_shopblocks_location_address', sanitize_textarea_field( wp_unslash( $_POST['shopblocks_location_address'] ?? '' ) ) );
	update_post_meta( $post_id, '_shopblocks_location_phone', sanitize_text_field( wp_unslash( $_POST['shopblocks_location_phone'] ?? '' ) ) );
	update_post_meta( $post_id, '_shopblocks_location_email', sanitize_email( wp_unslash( $_POST['shopblocks_location_email'] ?? '' ) ) );
	update_post_meta( $post_id, '_shopblocks_location_map_url', esc_url_raw( wp_unslash( $_POST['shopblocks_location_map_url'] ?? '' ) ) );
	update_post_meta( $post_id, '_shopblocks_sidebar_cta_image', esc_url_raw( wp_unslash( $_POST['shopblocks_sidebar_cta_image'] ?? '' ) ) );
	update_post_meta( $post_id, '_shopblocks_sidebar_cta_heading', sanitize_text_field( wp_unslash( $_POST['shopblocks_sidebar_cta_heading'] ?? '' ) ) );
	update_post_meta( $post_id, '_shopblocks_sidebar_cta_text', sanitize_textarea_field( wp_unslash( $_POST['shopblocks_sidebar_cta_text'] ?? '' ) ) );
	update_post_meta( $post_id, '_shopblocks_sidebar_cta_label', sanitize_text_field( wp_unslash( $_POST['shopblocks_sidebar_cta_label'] ?? '' ) ) );
	update_post_meta( $post_id, '_shopblocks_sidebar_cta_url', esc_url_raw( wp_unslash( $_POST['shopblocks_sidebar_cta_url'] ?? '' ) ) );
	update_post_meta( $post_id, '_shopblocks_sidebar_form_heading', sanitize_text_field( wp_unslash( $_POST['shopblocks_sidebar_form_heading'] ?? '' ) ) );
	update_post_meta( $post_id, '_shopblocks_sidebar_form_shortcode', sanitize_text_field( wp_unslash( $_POST['shopblocks_sidebar_form_shortcode'] ?? '' ) ) );
}
add_action( 'save_post_shopblocks_blog', 'shopblocks_save_blog_sidebar' );

function shopblocks_save_collection_editorial( $post_id ) {
	if ( ! isset( $_POST['shopblocks_collection_editorial_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['shopblocks_collection_editorial_nonce'] ) ), 'shopblocks_save_collection_editorial' ) ) { return; }
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) { return; }
	update_post_meta( $post_id, '_shopblocks_show_related_content', isset( $_POST['shopblocks_show_related_content'] ) ? '1' : '0' );
	update_post_meta( $post_id, '_shopblocks_related_content_refs', shopblocks_sanitize_content_refs( $_POST['shopblocks_collection_related_content_refs'] ?? array() ) );
	update_post_meta( $post_id, '_shopblocks_related_heading', sanitize_text_field( wp_unslash( $_POST['shopblocks_related_heading'] ?? '' ) ) );
	update_post_meta( $post_id, '_shopblocks_related_button_label', sanitize_text_field( wp_unslash( $_POST['shopblocks_related_button_label'] ?? '' ) ) );
	update_post_meta( $post_id, '_shopblocks_collection_faqs', sanitize_textarea_field( wp_unslash( $_POST['shopblocks_collection_faqs'] ?? '' ) ) );
	update_post_meta( $post_id, '_shopblocks_faq_heading', sanitize_text_field( wp_unslash( $_POST['shopblocks_faq_heading'] ?? '' ) ) );
}
add_action( 'save_post_collection', 'shopblocks_save_collection_editorial' );

/** Render mixed related-content cards. */
function shopblocks_render_related_content( $refs, $args = array() ) {
	$refs = shopblocks_parse_content_refs( $refs );
	if ( ! $refs ) { return ''; }
	$args = wp_parse_args( $args, array( 'heading' => __( 'Check out our Blog', 'shopblocks-wp' ), 'button' => __( 'Learn More', 'shopblocks-wp' ), 'class' => '' ) );
	$posts = array();
	foreach ( array_slice( $refs, 0, 12 ) as $ref ) {
		$item = get_post( $ref['post_id'] );
		if ( $item && 'publish' === $item->post_status && $ref['post_type'] === $item->post_type ) { $posts[] = $item; }
	}
	if ( ! $posts ) { return ''; }
		$heading_id = wp_unique_id( 'shopblocks-related-' );
	ob_start(); ?>
	<section class="shopblocks-related-content <?php echo esc_attr( sanitize_html_class( $args['class'] ) ); ?>" aria-labelledby="<?php echo esc_attr( $heading_id ); ?>">
		<h2 id="<?php echo esc_attr( $heading_id ); ?>" class="shopblocks-related-content__title"><?php echo esc_html( $args['heading'] ); ?></h2>
		<div class="shopblocks-related-content__grid">
			<?php foreach ( $posts as $item ) :
				$excerpt = has_excerpt( $item ) ? get_the_excerpt( $item ) : wp_trim_words( wp_strip_all_tags( strip_shortcodes( $item->post_content ) ), 22 ); ?>
				<article class="shopblocks-related-card shopblocks-related-card--<?php echo esc_attr( $item->post_type ); ?>">
					<a class="shopblocks-related-card__image" href="<?php echo esc_url( get_permalink( $item ) ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Read %s', 'shopblocks-wp' ), get_the_title( $item ) ) ); ?>">
						<?php if ( has_post_thumbnail( $item ) ) { echo get_the_post_thumbnail( $item, 'medium_large' ); } else { echo '<span class="shopblocks-related-card__placeholder" aria-hidden="true"></span>'; } ?>
					</a>
					<div class="shopblocks-related-card__body">
						<h3 class="shopblocks-related-card__title"><a href="<?php echo esc_url( get_permalink( $item ) ); ?>"><?php echo esc_html( get_the_title( $item ) ); ?></a></h3>
						<?php if ( $excerpt ) : ?><p class="shopblocks-related-card__excerpt"><?php echo esc_html( $excerpt ); ?></p><?php endif; ?>
						<a class="shopblocks-related-card__button" href="<?php echo esc_url( get_permalink( $item ) ); ?>"><?php echo esc_html( $args['button'] ); ?></a>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
	</section>
	<?php return ob_get_clean();
}

/** Backward-compatible renderer. */
function shopblocks_render_blog_cards( $ids, $class = '' ) {
	return shopblocks_render_related_content( shopblocks_legacy_related_refs( $ids ), array( 'class' => $class ) );
}

function shopblocks_parse_faqs( $raw ) {
	$faqs = array();
	foreach ( preg_split( '/\r\n|\r|\n/', (string) $raw ) as $line ) {
		$parts = array_map( 'trim', explode( '|', $line, 2 ) );
		if ( 2 === count( $parts ) && $parts[0] && $parts[1] ) { $faqs[] = array( 'question' => $parts[0], 'answer' => $parts[1] ); }
	}
	return $faqs;
}
function shopblocks_render_faqs( $raw, $heading = '' ) {
	$faqs = shopblocks_parse_faqs( $raw );
	if ( ! $faqs ) { return ''; }
	$heading = $heading ?: __( 'Frequently Asked Questions', 'shopblocks-wp' );
	ob_start(); ?>
	<section class="shopblocks-faq shopblocks-faq--automatic">
		<h2 class="shopblocks-faq__title"><?php echo esc_html( $heading ); ?></h2>
		<?php foreach ( $faqs as $index => $faq ) : ?>
			<details class="shopblocks-faq__item" <?php echo 0 === $index ? 'open' : ''; ?>>
				<summary class="shopblocks-faq__question"><?php echo esc_html( $faq['question'] ); ?></summary>
				<div class="shopblocks-faq__answer"><p><?php echo esc_html( $faq['answer'] ); ?></p></div>
			</details>
		<?php endforeach; ?>
	</section>
	<?php return ob_get_clean();
}

function shopblocks_render_helpful_links( $raw, $heading = '' ) {
	$items = array();
	foreach ( preg_split( '/\r\n|\r|\n/', (string) $raw ) as $line ) {
		$parts = array_map( 'trim', explode( '|', $line, 2 ) );
		if ( 2 === count( $parts ) && $parts[0] && $parts[1] ) { $items[] = array( 'label' => $parts[0], 'url' => $parts[1] ); }
	}
	if ( ! $items ) { return ''; }
	$heading = $heading ?: __( 'Other Helpful Links', 'shopblocks-wp' );
	ob_start(); ?><div class="shopblocks-helpful-links"><h2 class="shopblocks-helpful-links__title"><?php echo esc_html( $heading ); ?></h2><ul class="shopblocks-helpful-links__list"><?php foreach ( $items as $item ) : ?><li><a href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['label'] ); ?></a></li><?php endforeach; ?></ul></div><?php
	return ob_get_clean();
}


/** Resolve a per-post form shortcode with an optional global fallback. */
function shopblocks_get_lead_form_shortcode( $post_id, $meta_key ) {
	$shortcode = trim( (string) get_post_meta( $post_id, $meta_key, true ) );
	if ( '' === $shortcode ) {
		$shortcode = trim( (string) get_option( 'shopblocks_lead_form_shortcode', '' ) );
	}
	return $shortcode;
}

/** Render the generic lead-generation CTA card used in Blog sidebars. */
function shopblocks_render_sidebar_cta( $post_id ) {
	$image   = get_post_meta( $post_id, '_shopblocks_sidebar_cta_image', true );
	$heading = get_post_meta( $post_id, '_shopblocks_sidebar_cta_heading', true );
	$text    = get_post_meta( $post_id, '_shopblocks_sidebar_cta_text', true );
	$label   = get_post_meta( $post_id, '_shopblocks_sidebar_cta_label', true );
	$url     = get_post_meta( $post_id, '_shopblocks_sidebar_cta_url', true );
	if ( ! $image && ! $heading && ! $text && ! ( $label && $url ) ) { return ''; }
	ob_start(); ?>
	<div class="shopblocks-sidebar-cta">
		<?php if ( $image ) : ?><img class="shopblocks-sidebar-cta__image" src="<?php echo esc_url( $image ); ?>" alt=""><?php endif; ?>
		<div class="shopblocks-sidebar-cta__body">
			<?php if ( $heading ) : ?><h2 class="shopblocks-sidebar-cta__title"><?php echo esc_html( $heading ); ?></h2><?php endif; ?>
			<?php if ( $text ) : ?><p class="shopblocks-sidebar-cta__text"><?php echo esc_html( $text ); ?></p><?php endif; ?>
			<?php if ( $label && $url ) : ?><a class="shopblocks-sidebar-cta__button" href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $label ); ?></a><?php endif; ?>
		</div>
	</div>
	<?php return ob_get_clean();
}

/** Render a generic shortcode-powered lead form. */
function shopblocks_render_lead_form( $post_id, $meta_key, $heading = '' ) {
	$shortcode = shopblocks_get_lead_form_shortcode( $post_id, $meta_key );
	if ( '' === $shortcode ) { return ''; }
	ob_start(); ?>
	<div class="shopblocks-lead-form">
		<?php if ( $heading ) : ?><h2 class="shopblocks-lead-form__title"><?php echo esc_html( $heading ); ?></h2><?php endif; ?>
		<div class="shopblocks-lead-form__embed"><?php echo do_shortcode( $shortcode ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
	</div>
	<?php return ob_get_clean();
}
