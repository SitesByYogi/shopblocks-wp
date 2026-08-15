<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

function shopblocks_admin_menu() {
	add_menu_page(
		__( 'ShopBlocks', 'shopblocks-wp' ),
		__( 'ShopBlocks', 'shopblocks-wp' ),
		'manage_options',
		'shopblocks-settings',
		'shopblocks_settings_page',
		'dashicons-cart',
		57
	);

	// Keep a clearly labeled Settings destination visible beneath the
	// ShopBlocks menu even when custom post type submenus are registered.
	add_submenu_page(
		'shopblocks-settings',
		__( 'ShopBlocks Settings', 'shopblocks-wp' ),
		__( 'Settings', 'shopblocks-wp' ),
		'manage_options',
		'shopblocks-settings-options',
		'shopblocks_settings_page'
	);
}
add_action( 'admin_menu', 'shopblocks_admin_menu' );

function shopblocks_settings_page() {
	$tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'instructions';
	$page_slug = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : 'shopblocks-settings';
	if ( ! in_array( $page_slug, array( 'shopblocks-settings', 'shopblocks-settings-options' ), true ) ) { $page_slug = 'shopblocks-settings'; }
	if ( ! in_array( $tab, array( 'instructions', 'general', 'design' ), true ) ) { $tab = 'instructions'; }
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'ShopBlocks Settings', 'shopblocks-wp' ); ?></h1>
		<nav class="nav-tab-wrapper">
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $page_slug . '&tab=instructions' ) ); ?>" class="nav-tab <?php echo 'instructions' === $tab ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Shortcodes', 'shopblocks-wp' ); ?></a>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $page_slug . '&tab=general' ) ); ?>" class="nav-tab <?php echo 'general' === $tab ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Settings', 'shopblocks-wp' ); ?></a>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $page_slug . '&tab=design' ) ); ?>" class="nav-tab <?php echo 'design' === $tab ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Design', 'shopblocks-wp' ); ?></a>
		</nav>
		<?php if ( 'instructions' === $tab ) : ?>
			<h2><?php esc_html_e( 'Content Types', 'shopblocks-wp' ); ?></h2>
			<p><strong><?php esc_html_e( 'Blogs', 'shopblocks-wp' ); ?></strong>: <?php esc_html_e( 'Editorial articles with a modular sidebar. Lead forms, CTA cards, helpful links, newsletters, and optional WooCommerce products can be mixed per post.', 'shopblocks-wp' ); ?></p>
			<p><strong><?php esc_html_e( 'Articles', 'shopblocks-wp' ); ?></strong>: <?php esc_html_e( 'Use the Article template on any ShopBlocks Blog for service, location, treatment, and lead-generation pages with a conversion hero and no traditional sidebar.', 'shopblocks-wp' ); ?></p>
			<p><strong><?php esc_html_e( 'Collections', 'shopblocks-wp' ); ?></strong>: <?php esc_html_e( 'Full-width shoppable articles with selected products near the top and no sidebar.', 'shopblocks-wp' ); ?></p>
			<h2><?php esc_html_e( 'Available Shortcodes', 'shopblocks-wp' ); ?></h2>
			<p><code>[shoppable_product_top id="123"]</code></p>
			<p><code>[shoppable_product_top slug="product-slug" variation_id="456"]</code></p>
			<p><code>[shoppable_product_top slug="product-slug" attribute_pa_size="large"]</code></p>
			<p><code>[shopblocks_products category="featured" limit="4" columns="4"]</code></p>
			<p><code>[shopblocks_products ids="1,2,3"]</code></p>
			<p><code>[shopblocks_products ids="1,2" columns="1" layout="sidebar"]</code></p>
			<p><code>[shopblocks_newsletter]</code></p>
			<p class="description"><?php esc_html_e( 'The legacy [add_products] shortcode remains supported.', 'shopblocks-wp' ); ?></p>
			<h2><?php esc_html_e( 'Integrated Structured Data', 'shopblocks-wp' ); ?></h2>
			<p><?php esc_html_e( 'ShopBlocks includes integrated CollectionPage and Product structured data. Schema output can be disabled in General Settings when another SEO or schema system owns that markup.', 'shopblocks-wp' ); ?></p>
		<?php elseif ( 'general' === $tab ) : ?>
			<form method="post" action="options.php">
				<?php settings_fields( 'shopblocks_general_settings' ); do_settings_sections( 'shopblocks-settings' ); submit_button(); ?>
			</form>
		<?php else : ?>
			<p><?php esc_html_e( 'ShopBlocks ships with safe template defaults and still inherits the active theme typography where possible. Design values are saved independently from General Settings so changing a CTA color can never clear layout or style tokens.', 'shopblocks-wp' ); ?></p>
			<form method="post" action="options.php">
				<?php settings_fields( 'shopblocks_design_settings' ); do_settings_sections( 'shopblocks-design' ); submit_button(); ?>
			</form>
		<?php endif; ?>
	</div>
	<?php
}

function shopblocks_sanitize_limit( $value ) { return max( 1, min( 48, absint( $value ) ) ); }
function shopblocks_sanitize_checkbox( $value ) { return empty( $value ) ? 0 : 1; }
function shopblocks_sanitize_css( $value ) {
	$value = is_string( $value ) ? wp_unslash( $value ) : '';
	$value = preg_replace( '#</?style[^>]*>#i', '', $value );
	return trim( $value );
}

function shopblocks_sanitize_color( $value ) {
	$color = sanitize_hex_color( $value );
	return $color ? $color : '';
}
function shopblocks_sanitize_css_token( $value ) {
	$value = trim( sanitize_text_field( wp_unslash( (string) $value ) ) );
	return preg_match( '/^[a-zA-Z0-9#.,()\s%\-\/\"\']+$/', $value ) ? $value : '';
}

function shopblocks_register_settings() {
	register_setting( 'shopblocks_general_settings', 'shopblocks_default_limit', array( 'type' => 'integer', 'sanitize_callback' => 'shopblocks_sanitize_limit', 'default' => 4 ) );
	register_setting( 'shopblocks_general_settings', 'shopblocks_enable_styles', array( 'type' => 'boolean', 'sanitize_callback' => 'shopblocks_sanitize_checkbox', 'default' => 1 ) );
	register_setting( 'shopblocks_general_settings', 'shopblocks_enable_schema', array( 'type' => 'boolean', 'sanitize_callback' => 'shopblocks_sanitize_checkbox', 'default' => 1 ) );
	register_setting( 'shopblocks_general_settings', 'shopblocks_newsletter_shortcode', array( 'sanitize_callback' => 'sanitize_text_field', 'default' => '' ) ); // Legacy <= 2.2.0 fallback.
	register_setting( 'shopblocks_general_settings', 'shopblocks_default_blog_newsletter_embed', array( 'type' => 'string', 'sanitize_callback' => 'shopblocks_sanitize_embed_content', 'default' => '' ) );
	register_setting( 'shopblocks_general_settings', 'shopblocks_lead_form_shortcode', array( 'sanitize_callback' => 'sanitize_text_field', 'default' => '' ) );
	register_setting( 'shopblocks_general_settings', 'shopblocks_default_blog_sidebar_products', array( 'sanitize_callback' => 'shopblocks_sanitize_id_list', 'default' => '' ) );
	register_setting( 'shopblocks_general_settings', 'shopblocks_blog_slug', array( 'type' => 'string', 'sanitize_callback' => 'shopblocks_sanitize_blog_slug', 'default' => 'blogs' ) );
	register_setting( 'shopblocks_general_settings', 'shopblocks_collection_slug', array( 'type' => 'string', 'sanitize_callback' => 'shopblocks_sanitize_collection_slug', 'default' => 'collections' ) );

	register_setting( 'shopblocks_design_settings', 'shopblocks_custom_css', array( 'type' => 'string', 'sanitize_callback' => 'shopblocks_sanitize_css', 'default' => '' ) );
	register_setting( 'shopblocks_design_settings', 'shopblocks_blog_css', array( 'type' => 'string', 'sanitize_callback' => 'shopblocks_sanitize_css', 'default' => '' ) );
	register_setting( 'shopblocks_design_settings', 'shopblocks_article_css', array( 'type' => 'string', 'sanitize_callback' => 'shopblocks_sanitize_css', 'default' => '' ) );
	register_setting( 'shopblocks_design_settings', 'shopblocks_collection_css', array( 'type' => 'string', 'sanitize_callback' => 'shopblocks_sanitize_css', 'default' => '' ) );
	add_settings_section( 'shopblocks_main_section', __( 'General Options', 'shopblocks-wp' ), '__return_false', 'shopblocks-settings' );
	add_settings_field( 'shopblocks_default_limit', __( 'Default Product Limit', 'shopblocks-wp' ), 'shopblocks_default_limit_callback', 'shopblocks-settings', 'shopblocks_main_section' );
	add_settings_field( 'shopblocks_enable_styles', __( 'Enable Plugin Styling', 'shopblocks-wp' ), 'shopblocks_enable_styles_callback', 'shopblocks-settings', 'shopblocks_main_section' );
	add_settings_field( 'shopblocks_enable_schema', __( 'Enable Structured Data', 'shopblocks-wp' ), 'shopblocks_enable_schema_callback', 'shopblocks-settings', 'shopblocks_main_section' );
	add_settings_field( 'shopblocks_default_blog_newsletter_embed', __( 'Default Blog Newsletter / Signup Embed', 'shopblocks-wp' ), 'shopblocks_default_blog_newsletter_embed_callback', 'shopblocks-settings', 'shopblocks_main_section' );
	add_settings_field( 'shopblocks_lead_form_shortcode', __( 'Default Lead Form / Booking Shortcode', 'shopblocks-wp' ), 'shopblocks_lead_form_shortcode_callback', 'shopblocks-settings', 'shopblocks_main_section' );
	add_settings_field( 'shopblocks_default_blog_sidebar_products', __( 'Default Blog Sidebar Products', 'shopblocks-wp' ), 'shopblocks_default_blog_sidebar_products_callback', 'shopblocks-settings', 'shopblocks_main_section' );

	add_settings_section( 'shopblocks_permalink_section', __( 'Permalinks / URL Bases', 'shopblocks-wp' ), 'shopblocks_permalink_section_callback', 'shopblocks-settings' );
	add_settings_field( 'shopblocks_blog_slug', __( 'Blog URL Base', 'shopblocks-wp' ), 'shopblocks_blog_slug_callback', 'shopblocks-settings', 'shopblocks_permalink_section' );
	add_settings_field( 'shopblocks_collection_slug', __( 'Collection URL Base', 'shopblocks-wp' ), 'shopblocks_collection_slug_callback', 'shopblocks-settings', 'shopblocks_permalink_section' );


	$design_options = array(
		'shopblocks_font_heading'  => array( __( 'Heading Font Stack', 'shopblocks-wp' ), 'inherit' ),
		'shopblocks_font_body'     => array( __( 'Body Font Stack', 'shopblocks-wp' ), 'inherit' ),
		'shopblocks_color_primary' => array( __( 'Primary Color', 'shopblocks-wp' ), '#1ea5e8', 'color' ),
		'shopblocks_color_button_text' => array( __( 'Button Text Color', 'shopblocks-wp' ), '#ffffff', 'color' ),
		'shopblocks_color_background' => array( __( 'Template Background', 'shopblocks-wp' ), '#ffffff', 'color' ),
		'shopblocks_color_text'    => array( __( 'Text Color', 'shopblocks-wp' ), '#1f2933', 'color' ),
		'shopblocks_color_muted'   => array( __( 'Muted Text Color', 'shopblocks-wp' ), '#6b7280', 'color' ),
		'shopblocks_color_surface' => array( __( 'Surface Color', 'shopblocks-wp' ), '#ffffff', 'color' ),
		'shopblocks_color_border'  => array( __( 'Border Color', 'shopblocks-wp' ), '#d9dde3', 'color' ),
		'shopblocks_page_width'    => array( __( 'Page Width', 'shopblocks-wp' ), '1280px' ),
		'shopblocks_article_width' => array( __( 'Article Width', 'shopblocks-wp' ), '780px' ),
		'shopblocks_sidebar_width' => array( __( 'Sidebar Width', 'shopblocks-wp' ), '320px' ),
		'shopblocks_layout_gap'    => array( __( 'Layout Gap', 'shopblocks-wp' ), '48px' ),
		'shopblocks_radius_small'  => array( __( 'Small Radius', 'shopblocks-wp' ), '6px' ),
		'shopblocks_radius_medium' => array( __( 'Medium Radius', 'shopblocks-wp' ), '12px' ),
		'shopblocks_radius_large'  => array( __( 'Large Radius', 'shopblocks-wp' ), '24px' ),
		'shopblocks_button_radius' => array( __( 'Button Radius', 'shopblocks-wp' ), '6px' ),
		'shopblocks_card_shadow'   => array( __( 'Card Shadow', 'shopblocks-wp' ), 'none' ),
	);
	add_settings_section( 'shopblocks_design_section', __( 'Design Tokens', 'shopblocks-wp' ), '__return_false', 'shopblocks-design' );
	foreach ( $design_options as $option => $config ) {
		$is_color = isset( $config[2] ) && 'color' === $config[2];
		register_setting( 'shopblocks_design_settings', $option, array( 'sanitize_callback' => $is_color ? 'shopblocks_sanitize_color' : 'shopblocks_sanitize_css_token', 'default' => $config[1] ) );
		add_settings_field( $option, $config[0], 'shopblocks_design_token_callback', 'shopblocks-design', 'shopblocks_design_section', array( 'option' => $option, 'default' => $config[1], 'type' => $is_color ? 'color' : 'text' ) );
	}
	add_settings_field( 'shopblocks_custom_css_design', __( 'Shared Template CSS', 'shopblocks-wp' ), 'shopblocks_custom_css_callback', 'shopblocks-design', 'shopblocks_design_section' );
	add_settings_field( 'shopblocks_blog_css_design', __( 'Blog Template CSS', 'shopblocks-wp' ), 'shopblocks_blog_css_callback', 'shopblocks-design', 'shopblocks_design_section' );
	add_settings_field( 'shopblocks_article_css_design', __( 'Article Template CSS', 'shopblocks-wp' ), 'shopblocks_article_css_callback', 'shopblocks-design', 'shopblocks_design_section' );
	add_settings_field( 'shopblocks_collection_css_design', __( 'Collection Template CSS', 'shopblocks-wp' ), 'shopblocks_collection_css_callback', 'shopblocks-design', 'shopblocks_design_section' );
}
add_action( 'admin_init', 'shopblocks_register_settings' );

function shopblocks_default_limit_callback() { printf( '<input type="number" name="shopblocks_default_limit" value="%d" min="1" max="48">', absint( get_option( 'shopblocks_default_limit', 4 ) ) ); }
function shopblocks_enable_styles_callback() { printf( '<label><input type="checkbox" name="shopblocks_enable_styles" value="1" %s> %s</label>', checked( 1, get_option( 'shopblocks_enable_styles', 1 ), false ), esc_html__( 'Load the default ShopBlocks stylesheet.', 'shopblocks-wp' ) ); }
function shopblocks_enable_schema_callback() { printf( '<label><input type="checkbox" name="shopblocks_enable_schema" value="1" %s> %s</label><p class="description">%s</p>', checked( 1, get_option( 'shopblocks_enable_schema', 1 ), false ), esc_html__( 'Output ShopBlocks CollectionPage/Product JSON-LD.', 'shopblocks-wp' ), esc_html__( 'Automatically pauses its own schema output while the legacy Schema Rich Snippets plugin is active to avoid duplicate JSON-LD.', 'shopblocks-wp' ) ); }
function shopblocks_default_blog_newsletter_embed_callback() {
	$value = get_option( 'shopblocks_default_blog_newsletter_embed', '' );
	if ( '' === trim( (string) $value ) ) {
		$value = get_option( 'shopblocks_newsletter_shortcode', '' );
	}
	printf(
		'<textarea name="shopblocks_default_blog_newsletter_embed" rows="5" class="large-text code" placeholder="[newsletter_shortcode] or &lt;div class=&quot;klaviyo-form-ABC123&quot;&gt;&lt;/div&gt;">%1$s</textarea><p class="description">%2$s</p>',
		esc_textarea( $value ),
		esc_html__( 'Global Blog newsletter/signup embed. Supports shortcodes and safe HTML. Blogs inherit this value unless they provide their own override or disable the newsletter module.', 'shopblocks-wp' )
	);
}
function shopblocks_lead_form_shortcode_callback() { printf( '<input type="text" name="shopblocks_lead_form_shortcode" value="%s" class="regular-text code" placeholder="[wpforms id=&quot;123&quot;]"><p class="description">%s</p>', esc_attr( get_option( 'shopblocks_lead_form_shortcode', '' ) ), esc_html__( 'Provider-agnostic fallback for Blog/Article hero and sidebar forms. Per-post shortcodes override this value.', 'shopblocks-wp' ) ); }
function shopblocks_default_blog_sidebar_products_callback() {
	shopblocks_render_product_selector(
		'shopblocks_default_blog_sidebar_products',
		get_option( 'shopblocks_default_blog_sidebar_products', '' ),
		array(
			'id'          => 'default-blog-sidebar-products',
			'label'       => __( 'Default Blog Sidebar Products', 'shopblocks-wp' ),
			'description' => __( 'Search WooCommerce products and drag them into the default sidebar order. Blogs inherit this list unless they provide their own override or disable sidebar products.', 'shopblocks-wp' ),
		)
	);
}
function shopblocks_permalink_section_callback() {
	printf(
		'<p>%1$s</p><p><strong>%2$s</strong></p>',
		esc_html__( 'Change these only when the default ShopBlocks routes conflict with an existing site structure. Existing installations keep /blogs/ and /collections/ unless you explicitly change them.', 'shopblocks-wp' ),
		esc_html__( 'Changing an established URL base changes public URLs. Add redirects when previous URLs are indexed or linked externally.', 'shopblocks-wp' )
	);
}

function shopblocks_render_slug_conflicts( $slug, $own_post_type ) {
	$conflicts = shopblocks_get_permalink_conflicts( $slug, $own_post_type );
	if ( empty( $conflicts ) ) {
		return;
	}
	foreach ( $conflicts as $conflict ) {
		printf( '<p class="description" style="color:#b32d2e"><strong>%1$s</strong> %2$s</p>', esc_html__( 'Potential conflict:', 'shopblocks-wp' ), esc_html( $conflict ) );
	}
}

function shopblocks_blog_slug_callback() {
	$slug = shopblocks_get_blog_slug();
	printf(
		'<input type="text" name="shopblocks_blog_slug" value="%1$s" class="regular-text code" placeholder="blogs"><p class="description">%2$s <code>%3$s</code></p>',
		esc_attr( $slug ),
		esc_html__( 'Example URL:', 'shopblocks-wp' ),
		esc_html( home_url( '/' . $slug . '/sample-post/' ) )
	);
	shopblocks_render_slug_conflicts( $slug, 'shopblocks_blog' );
}

function shopblocks_collection_slug_callback() {
	$slug = shopblocks_get_collection_slug();
	printf(
		'<input type="text" name="shopblocks_collection_slug" value="%1$s" class="regular-text code" placeholder="collections"><p class="description">%2$s <code>%3$s</code></p>',
		esc_attr( $slug ),
		esc_html__( 'Example URL:', 'shopblocks-wp' ),
		esc_html( home_url( '/' . $slug . '/sample-collection/' ) )
	);
	shopblocks_render_slug_conflicts( $slug, 'collection' );
}

function shopblocks_design_token_callback( $args ) {
	$option  = sanitize_key( $args['option'] );
	$default = isset( $args['default'] ) ? $args['default'] : '';
	$type    = isset( $args['type'] ) && 'color' === $args['type'] ? 'color' : 'text';
		$css_var = '--' . str_replace( '_', '-', $option );
	$value = trim( (string) get_option( $option, $default ) );
	if ( '' === $value ) { $value = $default; }
	printf( '<input type="%1$s" name="%2$s" value="%3$s" class="regular-text code"><p class="description"><code>%4$s</code></p>', esc_attr( $type ), esc_attr( $option ), esc_attr( $value ), esc_html( $css_var ) );
}

function shopblocks_custom_css_callback() {
	printf( '<textarea name="shopblocks_custom_css" rows="10" class="large-text code">%s</textarea><p class="description">%s</p>', esc_textarea( get_option( 'shopblocks_custom_css', '' ) ), esc_html__( 'Applied only inside ShopBlocks Blog, Article, and Collection templates. Theme styles remain inherited elsewhere.', 'shopblocks-wp' ) );
}
function shopblocks_blog_css_callback() {
	printf( '<textarea name="shopblocks_blog_css" rows="12" class="large-text code">%s</textarea><p class="description">%s</p>', esc_textarea( get_option( 'shopblocks_blog_css', '' ) ), esc_html__( 'Automatically scoped to .shopblocks-blog. You may safely use selectors such as h2, p, .button, or .shopblocks-blog__sidebar.', 'shopblocks-wp' ) );
}
function shopblocks_article_css_callback() {
	printf( '<textarea name="shopblocks_article_css" rows="12" class="large-text code">%s</textarea><p class="description">%s</p>', esc_textarea( get_option( 'shopblocks_article_css', '' ) ), esc_html__( 'Automatically scoped to .shopblocks-article for lead-generation, service, treatment, and location Article layouts.', 'shopblocks-wp' ) );
}
function shopblocks_collection_css_callback() {
	printf( '<textarea name="shopblocks_collection_css" rows="12" class="large-text code">%s</textarea><p class="description">%s</p>', esc_textarea( get_option( 'shopblocks_collection_css', '' ) ), esc_html__( 'Automatically scoped to .shopblocks-collection. You may safely use selectors such as h2, p, .button, or .shopblocks-product-card.', 'shopblocks-wp' ) );
}
