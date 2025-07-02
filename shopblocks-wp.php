<?php
/**
 * Plugin Name: ShopBlocks WP
 * Description: Turn your WooCommerce store into a Shopify-style experience with custom collections, shortcodes, and design enhancements.
 * Version: 1.3.0
 * Author: SitesByYogi
 */

if (!defined('ABSPATH')) exit;

define('SHOPBLOCKS_PLUGIN_VERSION', '1.3.0');
define('SHOPBLOCKS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SHOPBLOCKS_PLUGIN_URL', plugin_dir_url(__FILE__));

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('shopblocks-style', SHOPBLOCKS_PLUGIN_URL . 'style.css', [], SHOPBLOCKS_PLUGIN_VERSION);
});

add_action('init', function () {
    register_block_pattern_category('shopblocks', ['label' => 'ShopBlocks Layouts']);

    $patterns = [
        'featured-product-hero' => 'Featured Product Hero',
        'fullwidth-cta' => 'Full Width CTA Section',
        'faq-accordion' => 'FAQ Accordion Section',
        'citations-section' => 'Citations Section',
        'single-shoppable-template' => 'Single Shoppable Template',
        'full-template-layout' => 'Full Template Layout'
    ];

    foreach ($patterns as $slug => $title) {
        register_block_pattern("shopblocks/{$slug}", [
            'title'       => __($title, 'shopblocks-wp'),
            'description' => __('Reusable layout block.', 'shopblocks-wp'),
            'categories'  => ['shopblocks'],
            'viewportWidth' => 1200,
            'content'     => file_get_contents(SHOPBLOCKS_PLUGIN_DIR . "patterns/{$slug}.php"),
        ]);
    }
});

add_action('init', 'shopblocks_register_collections_cpt');
function shopblocks_register_collections_cpt() {
    $labels = [
        'name' => 'Collections',
        'singular_name' => 'Collection',
        'menu_name' => 'Collections',
        'name_admin_bar' => 'Collection',
        'add_new' => 'Add New',
        'add_new_item' => 'Add New Collection',
        'new_item' => 'New Collection',
        'edit_item' => 'Edit Collection',
        'view_item' => 'View Collection',
        'all_items' => 'All Collections',
        'search_items' => 'Search Collections',
        'not_found' => 'No collections found.',
        'not_found_in_trash' => 'No collections found in Trash.',
    ];

    $args = [
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'rewrite' => ['slug' => 'collections'],
        'show_in_rest' => true,
        'supports' => ['title', 'editor', 'thumbnail', 'excerpt'],
        'menu_position' => 5,
        'menu_icon' => 'dashicons-screenoptions',
    ];

    register_post_type('collection', $args);
}

function shopblocks_product_top_shortcode($atts) {
    $atts = shortcode_atts(['id' => '', 'slug' => ''], $atts);
    $product = null;

    if ($atts['id']) {
        $product = wc_get_product(intval($atts['id']));
    } elseif ($atts['slug']) {
        $post_obj = get_page_by_path(sanitize_title($atts['slug']), OBJECT, 'product');
        if ($post_obj) {
            $product = wc_get_product($post_obj->ID);
        }
    }

    if (!$product || !is_a($product, 'WC_Product')) {
        return '<p>Product not found.</p>';
    }

    $GLOBALS['product'] = $product;

    ob_start(); ?>
    <section class="woocommerce shoppable-product-top-wrapper">
        <div class="shoppable-product-top-container product">
            <div class="shoppable-product-top-image"><?php echo $product->get_image('large'); ?></div>
            <div class="summary entry-summary">
                <p class="price"><?php echo $product->get_price_html(); ?></p>
                <div class="woocommerce-product-details__short-description"><?php echo apply_filters('woocommerce_short_description', $product->get_short_description()); ?></div>
                <form class="cart" action="<?php echo esc_url(get_permalink($product->get_id())); ?>" method="post" enctype="multipart/form-data">
                    <?php woocommerce_quantity_input(['min_value' => 1, 'max_value' => $product->get_max_purchase_quantity(), 'input_value' => 1]); ?>
                    <button type="submit" name="add-to-cart" value="<?php echo esc_attr($product->get_id()); ?>" class="single_add_to_cart_button button alt">
                        <?php echo esc_html($product->single_add_to_cart_text()); ?>
                    </button>
                </form>
            </div>
        </div>
    </section>
    <?php return ob_get_clean();
}
add_shortcode('shoppable_product_top', 'shopblocks_product_top_shortcode');

function shopblocks_add_products_shortcode($atts) {
    $atts = shortcode_atts(['ids' => '', 'slugs' => '', 'category' => '', 'limit' => -1], $atts);
    $products = [];

    if ($atts['ids']) {
        $ids = array_map('intval', explode(',', $atts['ids']));
        foreach ($ids as $id) {
            $product = wc_get_product($id);
            if ($product && is_a($product, 'WC_Product')) $products[] = $product;
        }
    } elseif ($atts['slugs']) {
        $slugs = array_map('sanitize_title', explode(',', $atts['slugs']));
        foreach ($slugs as $slug) {
            $post = get_page_by_path($slug, OBJECT, 'product');
            if ($post) {
                $product = wc_get_product($post->ID);
                if ($product && is_a($product, 'WC_Product')) $products[] = $product;
            }
        }
    } elseif ($atts['category']) {
        $products = wc_get_products([
            'status' => 'publish',
            'limit' => intval($atts['limit']),
            'category' => [sanitize_title($atts['category'])]
        ]);
    }

    if (empty($products)) return '<p>No products found.</p>';

    ob_start(); ?>
    <section class="local-product-grid">
        <div class="local-product-grid__container">
            <?php foreach ($products as $product): ?>
            <div class="local-product-card">
                <a href="<?php echo esc_url(get_permalink($product->get_id())); ?>" class="local-product-card__image">
                    <?php echo $product->get_image(); ?>
                </a>
                <div class="local-product-card__content">
                    <h3 class="local-product-card__title">
                        <a href="<?php echo esc_url(get_permalink($product->get_id())); ?>"><?php echo esc_html($product->get_name()); ?></a>
                    </h3>
                    <div class="local-product-card__price"><?php echo $product->get_price_html(); ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php return ob_get_clean();
}
add_shortcode('add_products', 'shopblocks_add_products_shortcode');