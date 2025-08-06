<?php
/**
 * Plugin Name: ShopBlocks WP
 * Description: Turn your WooCommerce store into a Shopify-style experience with custom collections, shortcodes, and design enhancements.
 * Version: 1.3.4
 * Author: SitesByYogi
 * GitHub Plugin URI: https://github.com/SitesByYogi/shopblocks-wp
 * GitHub Branch: main
 * Primary Branch: main
 */

if (!defined('ABSPATH')) exit;

define('SHOPBLOCKS_PLUGIN_VERSION', '1.3.1');
define('SHOPBLOCKS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SHOPBLOCKS_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * WooCommerce dependency guard.
 */
add_action('plugins_loaded', function () {
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', function () {
            echo '<div class="notice notice-error"><p><strong>ShopBlocks WP</strong> requires WooCommerce to be active.</p></div>';
        });
    }
});

/**
 * Enqueue front-end stylesheet.
 */
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('shopblocks-style', SHOPBLOCKS_PLUGIN_URL . 'style.css', [], SHOPBLOCKS_PLUGIN_VERSION);
}, 20);

/**
 * Register Block Pattern Category & Patterns (guarded + file existence checks).
 */
add_action('init', function () {
    if (!function_exists('register_block_pattern_category') || !function_exists('register_block_pattern')) {
        return; // Older WP / GB not available.
    }

    register_block_pattern_category('shopblocks', ['label' => 'ShopBlocks Layouts']);

    $patterns = [
        'featured-product-hero'     => 'Featured Product Hero',
        'fullwidth-cta'             => 'Full Width CTA Section',
        'faq-accordion'             => 'FAQ Accordion Section',
        'citations-section'         => 'Citations Section',
        'single-shoppable-template' => 'Single Shoppable Template',
        'full-template-layout'      => 'Full Template Layout',
    ];

    foreach ($patterns as $slug => $title) {
        $path = SHOPBLOCKS_PLUGIN_DIR . "patterns/{$slug}.php";
        if (is_readable($path)) {
            register_block_pattern("shopblocks/{$slug}", [
                'title'         => __($title, 'shopblocks-wp'),
                'description'   => __('Reusable layout block.', 'shopblocks-wp'),
                'categories'    => ['shopblocks'],
                'viewportWidth' => 1200,
                'content'       => file_get_contents($path),
            ]);
        }
    }
});

/**
 * Collections Custom Post Type.
 */
add_action('init', 'shopblocks_register_collections_cpt');
function shopblocks_register_collections_cpt() {
    $labels = [
        'name'               => 'Collections',
        'singular_name'      => 'Collection',
        'menu_name'          => 'Collections',
        'name_admin_bar'     => 'Collection',
        'add_new'            => 'Add New',
        'add_new_item'       => 'Add New Collection',
        'new_item'           => 'New Collection',
        'edit_item'          => 'Edit Collection',
        'view_item'          => 'View Collection',
        'all_items'          => 'All Collections',
        'search_items'       => 'Search Collections',
        'not_found'          => 'No collections found.',
        'not_found_in_trash' => 'No collections found in Trash.',
    ];

    $args = [
        'labels'        => $labels,
        'public'        => true,
        'has_archive'   => true,
        'rewrite'       => [
            'slug'       => 'collections',
            'with_front' => false, // prevents "/blog/" being prefixed
        ],
        'show_in_rest'  => true,
        'supports'      => ['title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'],
        'menu_position' => 5,
        'menu_icon'     => 'dashicons-screenoptions',
    ];

    register_post_type('collection', $args);
}

/**
 * Flush rewrite on activation/deactivation so Collections URLs work immediately.
 */
register_activation_hook(__FILE__, function () {
    shopblocks_register_collections_cpt();
    flush_rewrite_rules();
});
register_deactivation_hook(__FILE__, function () {
    flush_rewrite_rules();
});

/**
 * Shortcode: [shoppable_product_top]
 * Supports locking to a specific variation via variation_id OR attribute_* preselection.
 * Examples:
 *   [shoppable_product_top slug="thca-flower-jars" variation_id="1234"]
 *   [shoppable_product_top slug="thca-flower-jars" attribute_pa_strain="aaa-cereal-milk" attribute_pa_size="eighthounce"]
 */
function shopblocks_product_top_shortcode($atts) {
    $atts = shortcode_atts([
        'id'           => '',
        'slug'         => '',
        'variation_id' => '',
    ], $atts, 'shoppable_product_top');

    $product = null;
    if ($atts['id']) {
        $product = wc_get_product((int) $atts['id']);
    } elseif ($atts['slug']) {
        $post_obj = get_page_by_path(sanitize_title($atts['slug']), OBJECT, 'product');
        if ($post_obj) {
            $product = wc_get_product($post_obj->ID);
        }
    }
    if (!$product || !is_a($product, 'WC_Product')) {
        return '<p>Product not found.</p>';
    }

    $selected_attrs = [];
    foreach ($atts as $key => $value) {
        if (0 === strpos($key, 'attribute_') && $value !== '') {
            $selected_attrs[$key] = sanitize_title($value);
        }
    }

    $display_product = $product;
    $active_variation = null;
    $active_variation_id = 0;

    if ($product->is_type('variable')) {
        $lock_id = absint($atts['variation_id']);
        if ($lock_id) {
            $maybe = wc_get_product($lock_id);
            if ($maybe && $maybe->is_type('variation') && (int) $maybe->get_parent_id() === $product->get_id()) {
                $active_variation = $maybe;
                $active_variation_id = $lock_id;
                $display_product = $maybe;
            }
        }

        if (!$active_variation && !empty($selected_attrs)) {
            $attr_map = [];
            foreach ($product->get_attributes() as $name => $attr) {
                $field = 'attribute_' . $name;
                if (isset($selected_attrs[$field])) {
                    $attr_map[$field] = $selected_attrs[$field];
                }
            }
            if (!empty($attr_map)) {
                if (function_exists('wc_get_matching_product_variation')) {
                    $match_id = wc_get_matching_product_variation($product, $attr_map);
                } else {
                    $data_store = WC_Data_Store::load('product');
                    $match_id = $data_store->find_matching_product_variation($product, $attr_map);
                }
                if ($match_id) {
                    $maybe = wc_get_product($match_id);
                    if ($maybe && $maybe->is_type('variation')) {
                        $active_variation = $maybe;
                        $active_variation_id = $match_id;
                        $display_product = $maybe;
                    }
                }
            }
        }
    }

    $img_html = '<a href="' . esc_url(get_permalink($product->get_id())) . '">' . $display_product->get_image('large') . '</a>';

    $GLOBALS['product'] = $product;
    global $post;
    $post = get_post($product->get_id());
    setup_postdata($post);

    wp_enqueue_script('wc-single-product');
    wp_enqueue_script('wc-add-to-cart-variation');

    ob_start(); ?>
    <section class="woocommerce shoppable-product-top-wrapper">
        <div class="shoppable-product-top-container product">
            <div class="shoppable-product-top-image">
                <?php echo $img_html; ?>
            </div>

            <div class="summary entry-summary">
                <?php if ($display_product->get_price_html()): ?>
                    <p class="price"><?php echo $display_product->get_price_html(); ?></p>
                <?php endif; ?>

                <?php if ($product->get_short_description()): ?>
                    <div class="woocommerce-product-details__short-description">
                        <?php echo apply_filters('woocommerce_short_description', $product->get_short_description()); ?>
                    </div>
                <?php endif; ?>

                <?php if ($product->is_type('variable')): ?>
                    <?php if ($active_variation): ?>
                        <form class="cart" action="<?php echo esc_url(get_permalink($product->get_id())); ?>" method="post" enctype="multipart/form-data">
                            <input type="hidden" name="add-to-cart" value="<?php echo esc_attr($product->get_id()); ?>">
                            <input type="hidden" name="product_id" value="<?php echo esc_attr($product->get_id()); ?>">
                            <input type="hidden" name="variation_id" value="<?php echo esc_attr($active_variation_id); ?>">
                            <?php foreach ($active_variation->get_attributes() as $attr_name => $attr_value): ?>
                                <input type="hidden" name="<?php echo esc_attr($attr_name); ?>" value="<?php echo esc_attr($attr_value); ?>">
                            <?php endforeach; ?>
                            <?php woocommerce_quantity_input(['min_value' => 1, 'max_value' => $display_product->get_max_purchase_quantity(), 'input_value' => 1]); ?>
                            <button type="submit" class="single_add_to_cart_button button alt">
                                <?php echo esc_html($product->single_add_to_cart_text()); ?>
                            </button>
                        </form>
                    <?php else:
                        $prefill = $selected_attrs;
                        $prefill_filter = function($args) use ($prefill) {
                            if (isset($args['name']) && isset($prefill[$args['name']])) {
                                $args['selected'] = $prefill[$args['name']];
                            }
                            return $args;
                        };
                        add_filter('woocommerce_dropdown_variation_attribute_options_args', $prefill_filter, 10, 1);
                        woocommerce_variable_add_to_cart();
                        remove_filter('woocommerce_dropdown_variation_attribute_options_args', $prefill_filter, 10);
                    endif; ?>
                <?php else: ?>
                    <form class="cart" action="<?php echo esc_url(get_permalink($product->get_id())); ?>" method="post" enctype="multipart/form-data">
                        <?php woocommerce_quantity_input(['min_value' => 1, 'max_value' => $product->get_max_purchase_quantity(), 'input_value' => 1]); ?>
                        <button type="submit" name="add-to-cart" value="<?php echo esc_attr($product->get_id()); ?>" class="single_add_to_cart_button button alt">
                            <?php echo esc_html($product->single_add_to_cart_text()); ?>
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <?php
    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode('shoppable_product_top', 'shopblocks_product_top_shortcode');

/**
 * Shortcode: [add_products]
 * Params: ids, slugs, category, limit
 */
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
            'status'   => 'publish',
            'limit'    => intval($atts['limit']),
            'category' => [sanitize_title($atts['category'])],
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
                            <a href="<?php echo esc_url(get_permalink($product->get_id())); ?>">
                                <?php echo esc_html($product->get_name()); ?>
                            </a>
                        </h3>
                        <div class="local-product-card__price"><?php echo $product->get_price_html(); ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php
    return ob_get_clean();
}
add_shortcode('add_products', 'shopblocks_add_products_shortcode');

/**
 * Admin Settings page (instructions, toggles, etc.).
 */
require_once SHOPBLOCKS_PLUGIN_DIR . 'admin/settings-page.php';