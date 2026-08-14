<?php
/**
 * Plugin Name: ShopBlocks WP
 * Description: Structured WordPress Blogs, landing Articles, shoppable Collections, integrated schema output, and optional WooCommerce integrations.
 * Version: 2.2.0
 * Author: SitesByYogi
 * Text Domain: shopblocks-wp
 * Requires at least: 6.3
 * Requires PHP: 7.4
 * WC requires at least: 7.0
 * WC tested up to: 10.1
 * GitHub Plugin URI: https://github.com/SitesByYogi/shopblocks-wp
 * GitHub Branch: main
 * Primary Branch: main
 * Update URI: https://github.com/SitesByYogi/shopblocks-wp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SHOPBLOCKS_PLUGIN_VERSION', '2.2.0' );
define( 'SHOPBLOCKS_PLUGIN_FILE', __FILE__ );
define( 'SHOPBLOCKS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SHOPBLOCKS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );


/**
 * Declare compatibility with WooCommerce High-Performance Order Storage.
 *
 * ShopBlocks reads product/catalog data only and does not access or modify
 * WooCommerce order storage directly.
 */
function shopblocks_declare_woocommerce_compatibility() {
	if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
			'custom_order_tables',
			SHOPBLOCKS_PLUGIN_FILE,
			true
		);
	}
}
add_action( 'before_woocommerce_init', 'shopblocks_declare_woocommerce_compatibility' );

/**
 * Returns whether WooCommerce is available.
 */
function shopblocks_has_woocommerce() {
	return class_exists( 'WooCommerce' ) && function_exists( 'wc_get_product' );
}


/**
 * Resolve the effective sidebar product IDs for a Blog.
 * Blog-specific IDs override the global default. Editors can explicitly
 * disable inherited products for an individual Blog.
 */
function shopblocks_get_blog_sidebar_product_ids( $post_id ) {
	$post_id = absint( $post_id );
	if ( ! $post_id || '1' === get_post_meta( $post_id, '_shopblocks_disable_sidebar_products', true ) ) {
		return '';
	}

	$override = shopblocks_sanitize_id_list( get_post_meta( $post_id, '_shopblocks_sidebar_product_ids', true ) );
	if ( $override ) {
		return $override;
	}

	return shopblocks_sanitize_id_list( get_option( 'shopblocks_default_blog_sidebar_products', '' ) );
}

/**
 * Register Collections independently so content remains accessible if WooCommerce is disabled.
 */
function shopblocks_register_collections_cpt() {
	$labels = array(
		'name'               => __( 'Collections', 'shopblocks-wp' ),
		'singular_name'      => __( 'Collection', 'shopblocks-wp' ),
		'menu_name'          => __( 'Collections', 'shopblocks-wp' ),
		'name_admin_bar'     => __( 'Collection', 'shopblocks-wp' ),
		'add_new'            => __( 'Add New', 'shopblocks-wp' ),
		'add_new_item'       => __( 'Add New Collection', 'shopblocks-wp' ),
		'new_item'           => __( 'New Collection', 'shopblocks-wp' ),
		'edit_item'          => __( 'Edit Collection', 'shopblocks-wp' ),
		'view_item'          => __( 'View Collection', 'shopblocks-wp' ),
		'all_items'          => __( 'All Collections', 'shopblocks-wp' ),
		'search_items'       => __( 'Search Collections', 'shopblocks-wp' ),
		'not_found'          => __( 'No collections found.', 'shopblocks-wp' ),
		'not_found_in_trash' => __( 'No collections found in Trash.', 'shopblocks-wp' ),
	);

	register_post_type(
		'collection',
		array(
			'labels'             => $labels,
			'public'             => true,
			'has_archive'        => true,
			'rewrite'            => array( 'slug' => 'collections', 'with_front' => false ),
			'show_in_rest'       => true,
			'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
			'show_in_menu'       => 'shopblocks-settings',
			'menu_position'      => 56,
			'menu_icon'          => 'dashicons-screenoptions',
			'show_in_nav_menus'  => true,
			'publicly_queryable' => true,
		),
	);
}
add_action( 'init', 'shopblocks_register_collections_cpt' );

/**
 * Register block patterns containing real Gutenberg markup.
 */
function shopblocks_register_patterns() {
	if ( ! function_exists( 'register_block_pattern_category' ) || ! function_exists( 'register_block_pattern' ) ) {
		return;
	}

	register_block_pattern_category( 'shopblocks', array( 'label' => __( 'ShopBlocks Layouts', 'shopblocks-wp' ) ) );

	$patterns = array(
		'featured-product-hero'     => __( 'Featured Product Hero', 'shopblocks-wp' ),
		'fullwidth-cta'             => __( 'Full Width CTA Section', 'shopblocks-wp' ),
		'faq-accordion'             => __( 'FAQ Section', 'shopblocks-wp' ),
		'citations-section'         => __( 'Citations Section', 'shopblocks-wp' ),
		'single-shoppable-template' => __( 'Single Shoppable Template', 'shopblocks-wp' ),
		'full-template-layout'      => __( 'Full Shoppable Page Layout', 'shopblocks-wp' ),
		'blog-article'             => __( 'Standard Blog Article', 'shopblocks-wp' ),
		'blog-sidebar'             => __( 'Blog Sidebar', 'shopblocks-wp' ),
		'collection-article'       => __( 'Shoppable Collection Article', 'shopblocks-wp' ),
		'article-landing'          => __( 'Lead Generation Article', 'shopblocks-wp' ),
	);

	foreach ( $patterns as $slug => $title ) {
		$path    = SHOPBLOCKS_PLUGIN_DIR . 'patterns/' . $slug . '.html';
		$content = is_readable( $path ) ? file_get_contents( $path ) : '';
		if ( '' === trim( (string) $content ) ) {
			continue;
		}
		register_block_pattern(
			'shopblocks/' . $slug,
			array(
				'title'         => $title,
				'description'   => __( 'A reusable ShopBlocks layout.', 'shopblocks-wp' ),
				'categories'    => array( 'shopblocks' ),
				'viewportWidth' => 1200,
				'content'       => $content,
			),
		);
	}
}
add_action( 'init', 'shopblocks_register_patterns', 20 );

/**
 * Load plugin templates, while allowing theme overrides in /shopblocks/.
 */
function shopblocks_template_include( $template ) {
	if ( is_singular( 'collection' ) ) {
		$post_id = get_queried_object_id();
		if ( function_exists( 'shopblocks_is_legacy_collection_compat' ) && shopblocks_is_legacy_collection_compat( $post_id ) ) {
			return $template;
		}
		$theme_template = locate_template( 'shopblocks/single-collection.php' );
		return $theme_template ? $theme_template : SHOPBLOCKS_PLUGIN_DIR . 'templates/single-collection.php';
	}

	if ( is_singular( 'shopblocks_blog' ) ) {
		$post_id = get_queried_object_id();
		$layout  = get_post_meta( $post_id, '_shopblocks_content_layout', true );
		if ( 'article' === $layout ) {
			$theme_template = locate_template( 'shopblocks/single-article.php' );
			return $theme_template ? $theme_template : SHOPBLOCKS_PLUGIN_DIR . 'templates/single-article.php';
		}
		$theme_template = locate_template( 'shopblocks/single-blog.php' );
		return $theme_template ? $theme_template : SHOPBLOCKS_PLUGIN_DIR . 'templates/single-blog.php';
	}

	if ( is_post_type_archive( 'collection' ) ) {
		$theme_template = locate_template( 'shopblocks/archive-collection.php' );
		return $theme_template ? $theme_template : SHOPBLOCKS_PLUGIN_DIR . 'templates/archive-collection.php';
	}

	if ( is_post_type_archive( 'shopblocks_blog' ) ) {
		$theme_template = locate_template( 'shopblocks/archive-blog.php' );
		return $theme_template ? $theme_template : SHOPBLOCKS_PLUGIN_DIR . 'templates/archive-blog.php';
	}

	return $template;
}
add_filter( 'template_include', 'shopblocks_template_include', 99 );


/**
 * Open the active theme shell for a ShopBlocks singular template.
 *
 * Block themes do not reliably expose their Site Editor header through
 * get_header(). Render the saved Header template part inside WordPress' normal
 * document canvas instead. Classic themes continue to use get_header().
 */
function shopblocks_render_theme_header() {
	if ( function_exists( 'wp_is_block_theme' ) && wp_is_block_theme() ) {
		?><!doctype html>
		<html <?php language_attributes(); ?>>
		<head>
			<meta charset="<?php bloginfo( 'charset' ); ?>">
			<meta name="viewport" content="width=device-width, initial-scale=1">
			<?php wp_head(); ?>
		</head>
		<body <?php body_class(); ?>>
		<?php
		wp_body_open();
		if ( function_exists( 'block_template_part' ) ) {
			block_template_part( 'header' );
		}
		return;
	}

	get_header();
}

/**
 * Close the active theme shell for a ShopBlocks singular template.
 */
function shopblocks_render_theme_footer() {
	if ( function_exists( 'wp_is_block_theme' ) && wp_is_block_theme() ) {
		if ( function_exists( 'block_template_part' ) ) {
			block_template_part( 'footer' );
		}
		wp_footer();
		?></body>
		</html><?php
		return;
	}

	get_footer();
}

/**
 * Add predictable body classes for theme and builder targeting.
 */
function shopblocks_body_classes( $classes ) {
	if ( is_singular( 'collection' ) ) {
		$classes[] = 'shopblocks-theme-shell';
		$classes[] = 'shopblocks-theme-shell--collection';
	}
	if ( is_singular( 'shopblocks_blog' ) ) {
		$classes[] = 'shopblocks-theme-shell';
		$classes[] = 'shopblocks-theme-shell--blog';
		$layout = get_post_meta( get_queried_object_id(), '_shopblocks_content_layout', true );
		$classes[] = 'article' === $layout ? 'shopblocks-theme-shell--article' : 'shopblocks-theme-shell--editorial-blog';
	}
	return $classes;
}
add_filter( 'body_class', 'shopblocks_body_classes' );

/**
 * Collection product IDs meta box.
 */
function shopblocks_add_collection_meta_box() {
	add_meta_box(
		'shopblocks_collection_products',
		__( 'Collection Products', 'shopblocks-wp' ),
		'shopblocks_collection_products_meta_box',
		'collection',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'shopblocks_add_collection_meta_box' );

function shopblocks_collection_products_meta_box( $post ) {
	wp_nonce_field( 'shopblocks_save_collection_products', 'shopblocks_collection_products_nonce' );
	$value = get_post_meta( $post->ID, '_shopblocks_product_ids', true );
	?>
	<p><?php esc_html_e( 'Enter WooCommerce product IDs in the order they should appear, separated by commas.', 'shopblocks-wp' ); ?></p>
	<input type="text" class="widefat" name="shopblocks_product_ids" value="<?php echo esc_attr( $value ); ?>" placeholder="123, 456, 789">
	<p class="description"><?php esc_html_e( 'The collection page will render these products using the ShopBlocks product grid.', 'shopblocks-wp' ); ?></p>
	<?php
}

function shopblocks_save_collection_products( $post_id ) {
	if ( ! isset( $_POST['shopblocks_collection_products_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['shopblocks_collection_products_nonce'] ) ), 'shopblocks_save_collection_products' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$raw = isset( $_POST['shopblocks_product_ids'] ) ? sanitize_text_field( wp_unslash( $_POST['shopblocks_product_ids'] ) ) : '';
	$ids = array_values( array_filter( array_map( 'absint', preg_split( '/\s*,\s*/', $raw ) ) ) );
	update_post_meta( $post_id, '_shopblocks_product_ids', implode( ',', $ids ) );
}
add_action( 'save_post_collection', 'shopblocks_save_collection_products' );

/**
 * Front-end styles and custom CSS.
 */
function shopblocks_get_design_tokens_css() {
	$defaults = array(
		'--shopblocks-font-heading'       => 'inherit',
		'--shopblocks-font-body'          => 'inherit',
		'--shopblocks-color-primary'      => '#1ea5e8',
		'--shopblocks-color-button-text'  => '#ffffff',
		'--shopblocks-color-background'   => '#ffffff',
		'--shopblocks-color-text'         => '#1f2933',
		'--shopblocks-color-muted'        => '#6b7280',
		'--shopblocks-color-surface'      => '#ffffff',
		'--shopblocks-color-border'       => '#d9dde3',
		'--shopblocks-page-width'         => '1280px',
		'--shopblocks-article-width'      => '780px',
		'--shopblocks-sidebar-width'      => '320px',
		'--shopblocks-layout-gap'         => '48px',
		'--shopblocks-radius-small'       => '6px',
		'--shopblocks-radius-medium'      => '12px',
		'--shopblocks-radius-large'       => '24px',
		'--shopblocks-button-radius'      => '6px',
		'--shopblocks-card-shadow'        => 'none',
	);

	$options = array(
		'--shopblocks-font-heading'       => 'shopblocks_font_heading',
		'--shopblocks-font-body'          => 'shopblocks_font_body',
		'--shopblocks-color-primary'      => 'shopblocks_color_primary',
		'--shopblocks-color-button-text'  => 'shopblocks_color_button_text',
		'--shopblocks-color-background'   => 'shopblocks_color_background',
		'--shopblocks-color-text'         => 'shopblocks_color_text',
		'--shopblocks-color-muted'        => 'shopblocks_color_muted',
		'--shopblocks-color-surface'      => 'shopblocks_color_surface',
		'--shopblocks-color-border'       => 'shopblocks_color_border',
		'--shopblocks-page-width'         => 'shopblocks_page_width',
		'--shopblocks-article-width'      => 'shopblocks_article_width',
		'--shopblocks-sidebar-width'      => 'shopblocks_sidebar_width',
		'--shopblocks-layout-gap'         => 'shopblocks_layout_gap',
		'--shopblocks-radius-small'       => 'shopblocks_radius_small',
		'--shopblocks-radius-medium'      => 'shopblocks_radius_medium',
		'--shopblocks-radius-large'       => 'shopblocks_radius_large',
		'--shopblocks-button-radius'      => 'shopblocks_button_radius',
		'--shopblocks-card-shadow'        => 'shopblocks_card_shadow',
	);

	$declarations = array();
	foreach ( $options as $css_name => $option_name ) {
		$value = trim( (string) get_option( $option_name, $defaults[ $css_name ] ) );
		if ( '' === $value ) {
			$value = $defaults[ $css_name ];
		}
		$declarations[] = $css_name . ':' . $value;
	}

	/*
	 * Declare the resolved values both globally (for standalone shortcodes and
	 * legacy templates) and directly on ShopBlocks shells. This prevents empty
	 * or stale theme variables from winning the cascade on template pages.
	 */
	$selector = ':root,.shopblocks-blog,.shopblocks-article,.shopblocks-collection,.shopblocks-product-hero,.shopblocks-product-grid';
	return $selector . '{' . implode( ';', $declarations ) . '}';
}

function shopblocks_enqueue_assets() {
	if ( ! get_option( 'shopblocks_enable_styles', 1 ) ) {
		return;
	}
	wp_enqueue_style( 'shopblocks-style', SHOPBLOCKS_PLUGIN_URL . 'style.css', array(), SHOPBLOCKS_PLUGIN_VERSION );
	wp_add_inline_style( 'shopblocks-style', shopblocks_get_design_tokens_css() );

	/*
	 * Template CSS is deliberately scoped so common selectors such as h2,
	 * p, .button, or .product cannot affect the rest of the active theme.
	 * The plugin's base stylesheet remains structural and inherits theme
	 * typography, colors, links, buttons, and form controls by default.
	 */
	$shared_css      = get_option( 'shopblocks_custom_css', '' );
	$blog_css        = get_option( 'shopblocks_blog_css', '' );
	$article_css     = get_option( 'shopblocks_article_css', '' );
	$collection_css  = get_option( 'shopblocks_collection_css', '' );

	if ( $shared_css ) {
		wp_add_inline_style( 'shopblocks-style', '@scope (.shopblocks-blog) {' . $shared_css . '}@scope (.shopblocks-article) {' . $shared_css . '}@scope (.shopblocks-collection) {' . $shared_css . '}' );
	}
	if ( $blog_css ) {
		wp_add_inline_style( 'shopblocks-style', '@scope (.shopblocks-blog) {' . $blog_css . '}' );
	}
	if ( $article_css ) {
		wp_add_inline_style( 'shopblocks-style', '@scope (.shopblocks-article) {' . $article_css . '}' );
	}
	if ( $collection_css ) {
		wp_add_inline_style( 'shopblocks-style', '@scope (.shopblocks-collection) {' . $collection_css . '}' );
	}
}
add_action( 'wp_enqueue_scripts', 'shopblocks_enqueue_assets', 20 );

/**
 * Product hero shortcode.
 */
function shopblocks_product_top_shortcode( $raw_atts ) {
	if ( ! shopblocks_has_woocommerce() ) {
		return '<p class="shopblocks-notice">' . esc_html__( 'WooCommerce is required to display this product.', 'shopblocks-wp' ) . '</p>';
	}

	$raw_atts = is_array( $raw_atts ) ? $raw_atts : array();
	$base     = shortcode_atts(
		array( 'id' => '', 'slug' => '', 'variation_id' => '', 'show_title' => 'yes' ),
		$raw_atts,
		'shoppable_product_top'
	);
	$atts = array_merge( $raw_atts, $base );

	$product = false;
	if ( ! empty( $atts['id'] ) ) {
		$product = wc_get_product( absint( $atts['id'] ) );
	} elseif ( ! empty( $atts['slug'] ) ) {
		$post_obj = get_page_by_path( sanitize_title( $atts['slug'] ), OBJECT, 'product' );
		$product  = $post_obj ? wc_get_product( $post_obj->ID ) : false;
	}

	if ( ! $product instanceof WC_Product ) {
		return '<p class="shopblocks-notice">' . esc_html__( 'Product not found.', 'shopblocks-wp' ) . '</p>';
	}

	$selected_attrs = array();
	foreach ( $raw_atts as $key => $value ) {
		$key = sanitize_key( $key );
		if ( 0 === strpos( $key, 'attribute_' ) && '' !== $value ) {
			$selected_attrs[ $key ] = sanitize_title( $value );
		}
	}

	$display_product     = $product;
	$active_variation    = false;
	$active_variation_id = 0;

	if ( $product->is_type( 'variable' ) ) {
		$lock_id = absint( $atts['variation_id'] );
		if ( $lock_id ) {
			$maybe = wc_get_product( $lock_id );
			if ( $maybe instanceof WC_Product_Variation && (int) $maybe->get_parent_id() === $product->get_id() ) {
				$active_variation    = $maybe;
				$active_variation_id = $lock_id;
				$display_product     = $maybe;
			}
		}

		if ( ! $active_variation && $selected_attrs ) {
			$data_store = WC_Data_Store::load( 'product' );
			$match_id   = $data_store->find_matching_product_variation( $product, $selected_attrs );
			$maybe      = $match_id ? wc_get_product( $match_id ) : false;
			if ( $maybe instanceof WC_Product_Variation ) {
				$active_variation    = $maybe;
				$active_variation_id = $match_id;
				$display_product     = $maybe;
			}
		}
	}

	$previous_product = isset( $GLOBALS['product'] ) ? $GLOBALS['product'] : null;
	$previous_post    = isset( $GLOBALS['post'] ) ? $GLOBALS['post'] : null;
	$GLOBALS['product'] = $product;
	$GLOBALS['post']    = get_post( $product->get_id() );
	setup_postdata( $GLOBALS['post'] );

	if ( $product->is_type( 'variable' ) ) {
		wp_enqueue_script( 'wc-add-to-cart-variation' );
	}

	ob_start();
	?>
	<section class="woocommerce shopblocks-product-hero">
		<div class="shopblocks-product-hero__inner product">
			<div class="shopblocks-product-hero__media">
				<a href="<?php echo esc_url( get_permalink( $product->get_id() ) ); ?>"><?php echo wp_kses_post( $display_product->get_image( 'large' ) ); ?></a>
			</div>
			<div class="shopblocks-product-hero__summary summary entry-summary">
				<?php if ( 'no' !== strtolower( (string) $atts['show_title'] ) ) : ?>
					<h2 class="shopblocks-product-hero__title"><a href="<?php echo esc_url( get_permalink( $product->get_id() ) ); ?>"><?php echo esc_html( $product->get_name() ); ?></a></h2>
				<?php endif; ?>
				<?php if ( $display_product->get_price_html() ) : ?><p class="price"><?php echo wp_kses_post( $display_product->get_price_html() ); ?></p><?php endif; ?>
				<?php if ( $product->get_short_description() ) : ?><div class="woocommerce-product-details__short-description"><?php echo wp_kses_post( apply_filters( 'woocommerce_short_description', $product->get_short_description() ) ); ?></div><?php endif; ?>

				<?php if ( ! $product->is_purchasable() || ! $product->is_in_stock() ) : ?>
					<p class="stock out-of-stock"><?php echo esc_html( wc_get_stock_html( $product ) ? wp_strip_all_tags( wc_get_stock_html( $product ) ) : __( 'Out of stock', 'shopblocks-wp' ) ); ?></p>
				<?php elseif ( $product->is_type( 'variable' ) && $active_variation ) : ?>
					<form class="cart" action="<?php echo esc_url( get_permalink( $product->get_id() ) ); ?>" method="post" enctype="multipart/form-data">
						<input type="hidden" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>">
						<input type="hidden" name="product_id" value="<?php echo esc_attr( $product->get_id() ); ?>">
						<input type="hidden" name="variation_id" value="<?php echo esc_attr( $active_variation_id ); ?>">
						<?php foreach ( $active_variation->get_attributes() as $attr_name => $attr_value ) :
							$field_name = 0 === strpos( $attr_name, 'attribute_' ) ? $attr_name : 'attribute_' . $attr_name;
							?><input type="hidden" name="<?php echo esc_attr( $field_name ); ?>" value="<?php echo esc_attr( $attr_value ); ?>"><?php
						endforeach; ?>
						<?php woocommerce_quantity_input( array( 'min_value' => $display_product->get_min_purchase_quantity(), 'max_value' => $display_product->get_max_purchase_quantity(), 'input_value' => $display_product->get_min_purchase_quantity() ) ); ?>
						<button type="submit" class="single_add_to_cart_button button alt"><?php echo esc_html( $active_variation->single_add_to_cart_text() ); ?></button>
					</form>
				<?php elseif ( $product->is_type( 'variable' ) ) :
					$prefill_filter = function ( $args ) use ( $selected_attrs ) {
						if ( isset( $args['name'], $selected_attrs[ $args['name'] ] ) ) {
							$args['selected'] = $selected_attrs[ $args['name'] ];
						}
						return $args;
					};
					add_filter( 'woocommerce_dropdown_variation_attribute_options_args', $prefill_filter );
					woocommerce_variable_add_to_cart();
					remove_filter( 'woocommerce_dropdown_variation_attribute_options_args', $prefill_filter );
				else : ?>
					<form class="cart" action="<?php echo esc_url( get_permalink( $product->get_id() ) ); ?>" method="post" enctype="multipart/form-data">
						<?php woocommerce_quantity_input( array( 'min_value' => $product->get_min_purchase_quantity(), 'max_value' => $product->get_max_purchase_quantity(), 'input_value' => $product->get_min_purchase_quantity() ) ); ?>
						<button type="submit" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>" class="single_add_to_cart_button button alt"><?php echo esc_html( $product->single_add_to_cart_text() ); ?></button>
					</form>
				<?php endif; ?>
			</div>
		</div>
	</section>
	<?php
	$html = ob_get_clean();
	wp_reset_postdata();
	$GLOBALS['product'] = $previous_product;
	$GLOBALS['post']    = $previous_post;
	return $html;
}

/**
 * Product grid shortcode. [shopblocks_products] is preferred; [add_products] remains compatible.
 */
function shopblocks_products_shortcode( $atts ) {
	if ( ! shopblocks_has_woocommerce() ) {
		return '<p class="shopblocks-notice">' . esc_html__( 'WooCommerce is required to display products.', 'shopblocks-wp' ) . '</p>';
	}

	$default_limit = max( 1, min( 48, absint( get_option( 'shopblocks_default_limit', 4 ) ) ) );
	$atts = shortcode_atts(
		array( 'ids' => '', 'slugs' => '', 'category' => '', 'limit' => $default_limit, 'columns' => 4, 'orderby' => 'menu_order', 'order' => 'ASC', 'show_button' => 'yes', 'layout' => 'grid', 'class' => '' ),
		$atts,
		'shopblocks_products'
	);

	$limit    = max( 1, min( 48, absint( $atts['limit'] ) ) );
	$columns  = max( 1, min( 6, absint( $atts['columns'] ) ) );
	$products = array();
	$layout   = in_array( sanitize_key( $atts['layout'] ), array( 'grid', 'sidebar', 'compact' ), true ) ? sanitize_key( $atts['layout'] ) : 'grid';
	$extra_class = sanitize_html_class( (string) $atts['class'] );

	if ( $atts['ids'] ) {
		$ids = array_slice( array_values( array_filter( array_map( 'absint', preg_split( '/\s*,\s*/', $atts['ids'] ) ) ) ), 0, $limit );
		foreach ( $ids as $id ) {
			$product = wc_get_product( $id );
			if ( $product instanceof WC_Product && 'publish' === get_post_status( $id ) ) { $products[] = $product; }
		}
	} elseif ( $atts['slugs'] ) {
		$slugs = array_slice( array_map( 'sanitize_title', preg_split( '/\s*,\s*/', $atts['slugs'] ) ), 0, $limit );
		foreach ( $slugs as $slug ) {
			$post_obj = get_page_by_path( $slug, OBJECT, 'product' );
			$product  = $post_obj ? wc_get_product( $post_obj->ID ) : false;
			if ( $product instanceof WC_Product ) { $products[] = $product; }
		}
	} elseif ( $atts['category'] ) {
		$products = wc_get_products( array( 'status' => 'publish', 'limit' => $limit, 'category' => array( sanitize_title( $atts['category'] ) ), 'orderby' => sanitize_key( $atts['orderby'] ), 'order' => 'DESC' === strtoupper( $atts['order'] ) ? 'DESC' : 'ASC' ) );
	}

	if ( ! $products ) {
		return '<p class="shopblocks-notice">' . esc_html__( 'No products found.', 'shopblocks-wp' ) . '</p>';
	}

	ob_start(); ?>
	<section class="shopblocks-product-grid shopblocks-product-grid--<?php echo esc_attr( $layout ); ?><?php echo $extra_class ? ' ' . esc_attr( $extra_class ) : ''; ?>" style="--shopblocks-columns:<?php echo esc_attr( $columns ); ?>">
		<div class="shopblocks-product-grid__inner">
			<?php foreach ( $products as $product ) :
				$link = get_permalink( $product->get_id() ); ?>
				<article class="shopblocks-product-card">
					<a href="<?php echo esc_url( $link ); ?>" class="shopblocks-product-card__image" aria-label="<?php echo esc_attr( sprintf( __( 'View %s', 'shopblocks-wp' ), $product->get_name() ) ); ?>"><?php echo wp_kses_post( $product->get_image( 'woocommerce_thumbnail' ) ); ?></a>
					<div class="shopblocks-product-card__content">
						<h3 class="shopblocks-product-card__title"><a href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $product->get_name() ); ?></a></h3>
						<?php if ( $product->get_price_html() ) : ?><div class="shopblocks-product-card__price"><?php echo wp_kses_post( $product->get_price_html() ); ?></div><?php endif; ?>
						<?php if ( 'no' !== strtolower( (string) $atts['show_button'] ) ) : ?>
							<a class="button shopblocks-product-card__button" href="<?php echo esc_url( $product->add_to_cart_url() ); ?>" data-quantity="1" data-product_id="<?php echo esc_attr( $product->get_id() ); ?>" rel="nofollow"><?php echo esc_html( $product->add_to_cart_text() ); ?></a>
						<?php endif; ?>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
	</section>
	<?php return ob_get_clean();
}

/**
 * Newsletter placement shortcode.
 *
 * Set a provider shortcode in ShopBlocks settings (Klaviyo, Mailchimp, Fluent Forms, etc.).
 * The wrapper remains consistent with the shoppable article patterns.
 */
function shopblocks_newsletter_shortcode( $atts ) {
	$atts = shortcode_atts(
		array(
			'title'       => __( 'Join Our Mailing List.', 'shopblocks-wp' ),
			'description' => '',
		),
		$atts,
		'shopblocks_newsletter'
	);

	$provider_shortcode = trim( (string) get_option( 'shopblocks_newsletter_shortcode', '' ) );
	if ( '' === $provider_shortcode ) {
		return '';
	}

	ob_start();
	?>
	<aside class="shopblocks-newsletter" aria-label="<?php echo esc_attr( $atts['title'] ); ?>">
		<h2 class="shopblocks-newsletter__title"><?php echo esc_html( $atts['title'] ); ?></h2>
		<?php if ( $atts['description'] ) : ?><p class="shopblocks-newsletter__description"><?php echo esc_html( $atts['description'] ); ?></p><?php endif; ?>
		<div class="shopblocks-newsletter__form">
			<?php echo do_shortcode( $provider_shortcode ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
	</aside>
	<?php
	return ob_get_clean();
}
add_shortcode( 'shopblocks_newsletter', 'shopblocks_newsletter_shortcode' );

/**
 * Bootstrap WooCommerce-only hooks after plugins load.
 */
function shopblocks_bootstrap() {
	/* Editorial and lead-generation features load without WooCommerce. */
	if ( ! shopblocks_has_woocommerce() ) {
		return;
	}
	add_shortcode( 'shoppable_product_top', 'shopblocks_product_top_shortcode' );
	add_shortcode( 'shopblocks_products', 'shopblocks_products_shortcode' );
	add_shortcode( 'add_products', 'shopblocks_products_shortcode' );
}
add_action( 'plugins_loaded', 'shopblocks_bootstrap', 20 );

require_once SHOPBLOCKS_PLUGIN_DIR . 'includes/content-types.php';
require_once SHOPBLOCKS_PLUGIN_DIR . 'includes/legacy/legacy-compat.php';
require_once SHOPBLOCKS_PLUGIN_DIR . 'includes/schema/class-shopblocks-schema.php';
require_once SHOPBLOCKS_PLUGIN_DIR . 'admin/settings-page.php';

register_activation_hook( __FILE__, function () {
	shopblocks_register_collections_cpt();
	shopblocks_register_blogs_cpt();
	add_option( 'shopblocks_default_limit', 4 );
	add_option( 'shopblocks_enable_styles', 1 );
	add_option( 'shopblocks_default_blog_sidebar_products', '' );
	add_option( 'shopblocks_enable_schema', 1 );
	flush_rewrite_rules();
} );
register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
