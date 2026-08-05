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
	add_meta_box( 'shopblocks_blog_sidebar', __( 'Blog Sidebar', 'shopblocks-wp' ), 'shopblocks_blog_sidebar_meta_box', 'shopblocks_blog', 'normal', 'high' );
	add_meta_box( 'shopblocks_blog_faqs', __( 'Blog FAQs', 'shopblocks-wp' ), 'shopblocks_blog_faqs_meta_box', 'shopblocks_blog', 'normal', 'default' );
	add_meta_box( 'shopblocks_collection_related_content', __( 'Related Content Panel', 'shopblocks-wp' ), 'shopblocks_collection_related_content_meta_box', 'collection', 'normal', 'high' );
	add_meta_box( 'shopblocks_collection_faqs', __( 'Collection FAQs', 'shopblocks-wp' ), 'shopblocks_collection_faqs_meta_box', 'collection', 'normal', 'default' );
}
add_action( 'add_meta_boxes', 'shopblocks_add_editorial_meta_boxes' );

function shopblocks_blog_sidebar_meta_box( $post ) {
	wp_nonce_field( 'shopblocks_save_blog_sidebar', 'shopblocks_blog_sidebar_nonce' );
	$product_ids       = get_post_meta( $post->ID, '_shopblocks_sidebar_product_ids', true );
	$helpful           = get_post_meta( $post->ID, '_shopblocks_helpful_links', true );
	$helpful_heading   = get_post_meta( $post->ID, '_shopblocks_helpful_links_heading', true );
	$newsletter        = get_post_meta( $post->ID, '_shopblocks_show_newsletter', true );
	$newsletter        = '' === $newsletter ? '1' : $newsletter;
	$newsletter_title  = get_post_meta( $post->ID, '_shopblocks_newsletter_title', true );
	$newsletter_text   = get_post_meta( $post->ID, '_shopblocks_newsletter_description', true );
	$custom_content    = get_post_meta( $post->ID, '_shopblocks_sidebar_custom_content', true );
	?>
	<p><label><input type="checkbox" name="shopblocks_show_newsletter" value="1" <?php checked( '1', $newsletter ); ?>> <?php esc_html_e( 'Display the newsletter panel', 'shopblocks-wp' ); ?></label></p>
	<p><label><strong><?php esc_html_e( 'Newsletter heading', 'shopblocks-wp' ); ?></strong><br><input type="text" class="widefat" name="shopblocks_newsletter_title" value="<?php echo esc_attr( $newsletter_title ?: __( 'Join Our Mailing List.', 'shopblocks-wp' ) ); ?>"></label></p>
	<p><label><strong><?php esc_html_e( 'Newsletter supporting text', 'shopblocks-wp' ); ?></strong><br><textarea class="widefat" rows="2" name="shopblocks_newsletter_description"><?php echo esc_textarea( $newsletter_text ); ?></textarea></label></p>
	<hr>
	<p><strong><?php esc_html_e( 'Sidebar Products', 'shopblocks-wp' ); ?></strong></p>
	<input type="text" class="widefat" name="shopblocks_sidebar_product_ids" value="<?php echo esc_attr( $product_ids ); ?>" placeholder="123, 456">
	<p class="description"><?php esc_html_e( 'Enter WooCommerce product IDs in display order. These render as vertical sidebar cards only; Blogs never receive the Collection product grid.', 'shopblocks-wp' ); ?></p>
	<hr>
	<p><label><strong><?php esc_html_e( 'Helpful Links heading', 'shopblocks-wp' ); ?></strong><br><input type="text" class="widefat" name="shopblocks_helpful_links_heading" value="<?php echo esc_attr( $helpful_heading ?: __( 'Other Helpful Links', 'shopblocks-wp' ) ); ?>"></label></p>
	<textarea class="widefat" rows="5" name="shopblocks_helpful_links" placeholder="Lab Tests | /lab-tests/&#10;FAQ | /faq/"><?php echo esc_textarea( $helpful ); ?></textarea>
	<p class="description"><?php esc_html_e( 'Enter one link per line using Label | URL.', 'shopblocks-wp' ); ?></p>
	<hr>
	<p><label><strong><?php esc_html_e( 'Optional custom sidebar content', 'shopblocks-wp' ); ?></strong><br><textarea class="widefat code" rows="5" name="shopblocks_sidebar_custom_content" placeholder="[shortcode] or simple HTML"><?php echo esc_textarea( $custom_content ); ?></textarea></label></p>
	<p class="description"><?php esc_html_e( 'Rendered after Helpful Links. Shortcodes are supported. Leave blank to omit this block.', 'shopblocks-wp' ); ?></p>
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
	update_post_meta( $post_id, '_shopblocks_sidebar_product_ids', shopblocks_sanitize_id_list( $_POST['shopblocks_sidebar_product_ids'] ?? '' ) );
	update_post_meta( $post_id, '_shopblocks_helpful_links_heading', sanitize_text_field( wp_unslash( $_POST['shopblocks_helpful_links_heading'] ?? '' ) ) );
	update_post_meta( $post_id, '_shopblocks_helpful_links', sanitize_textarea_field( wp_unslash( $_POST['shopblocks_helpful_links'] ?? '' ) ) );
	update_post_meta( $post_id, '_shopblocks_sidebar_custom_content', wp_kses_post( wp_unslash( $_POST['shopblocks_sidebar_custom_content'] ?? '' ) ) );
	update_post_meta( $post_id, '_shopblocks_blog_faqs', sanitize_textarea_field( wp_unslash( $_POST['shopblocks_blog_faqs'] ?? '' ) ) );
	update_post_meta( $post_id, '_shopblocks_blog_faq_heading', sanitize_text_field( wp_unslash( $_POST['shopblocks_blog_faq_heading'] ?? '' ) ) );
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
