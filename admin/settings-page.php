<?php
// 1. Admin Menu
function shopblocks_admin_menu() {
    add_menu_page(
        'ShopBlocks Settings',
        'ShopBlocks',
        'manage_options',
        'shopblocks-settings',
        'shopblocks_settings_page',
        'dashicons-cart',
        56
    );
}
add_action('admin_menu', 'shopblocks_admin_menu');

// 2. Settings Page Content
function shopblocks_settings_page() {
    ?>
    <div class="wrap">
        <h1>ShopBlocks Settings</h1>
        <h2 class="nav-tab-wrapper">
            <a href="?page=shopblocks-settings&tab=instructions" class="nav-tab <?php echo (!isset($_GET['tab']) || $_GET['tab'] === 'instructions') ? 'nav-tab-active' : ''; ?>">Shortcodes</a>
            <a href="?page=shopblocks-settings&tab=general" class="nav-tab <?php echo (isset($_GET['tab']) && $_GET['tab'] === 'general') ? 'nav-tab-active' : ''; ?>">Settings</a>
        </h2>

        <div class="shopblocks-tab-content">
            <?php
            $tab = $_GET['tab'] ?? 'instructions';
            if ($tab === 'instructions') {
                ?>
                <h2>Available Shortcodes</h2>
                <ul>
                    <li><code>[shoppable_product_top id="123"]</code> – Displays a hero layout for a single product.</li>
                    <li><code>[shoppable_product_top slug="product-slug"]</code> – Same as above but by slug.</li>
                    <li><code>[add_products category="thca-flower" limit="4"]</code> – Displays multiple products from a category.</li>
                    <li><code>[add_products ids="1,2,3"]</code> – Displays products by specific IDs.</li>
                </ul>
                <?php
            } elseif ($tab === 'general') {
                ?>
                <form method="post" action="options.php">
                    <?php
                    settings_fields('shopblocks_settings');
                    do_settings_sections('shopblocks-settings');
                    submit_button();
                    ?>
                </form>
                <?php
            }
            ?>
        </div>
    </div>
    <?php
}

// 3. Register Settings
function shopblocks_register_settings() {
    // Register options
    register_setting('shopblocks_settings', 'shopblocks_default_limit');
    register_setting('shopblocks_settings', 'shopblocks_enable_styles');
    register_setting('shopblocks_settings', 'shopblocks_custom_css');

    // Section
    add_settings_section(
        'shopblocks_main_section',
        'General Options',
        null,
        'shopblocks-settings'
    );

    // Fields
    add_settings_field(
        'shopblocks_default_limit',
        'Default Product Limit',
        'shopblocks_default_limit_callback',
        'shopblocks-settings',
        'shopblocks_main_section'
    );

    add_settings_field(
        'shopblocks_enable_styles',
        'Enable Plugin Styling',
        'shopblocks_enable_styles_callback',
        'shopblocks-settings',
        'shopblocks_main_section'
    );

    add_settings_field(
        'shopblocks_custom_css',
        'Custom CSS (Global)',
        'shopblocks_custom_css_callback',
        'shopblocks-settings',
        'shopblocks_main_section'
    );
}
add_action('admin_init', 'shopblocks_register_settings');

// 4. Callbacks
function shopblocks_default_limit_callback() {
    $value = esc_attr(get_option('shopblocks_default_limit', 4));
    echo "<input type='number' name='shopblocks_default_limit' value='{$value}' min='1' />";
}

function shopblocks_enable_styles_callback() {
    $checked = checked(1, get_option('shopblocks_enable_styles', 1), false);
    echo "<input type='checkbox' name='shopblocks_enable_styles' value='1' {$checked} /> Enable ShopBlocks CSS";
}

function shopblocks_custom_css_callback() {
    $value = esc_textarea(get_option('shopblocks_custom_css', ''));
    echo "<textarea name='shopblocks_custom_css' rows='10' style='width:100%;font-family:monospace;'>{$value}</textarea>";
    echo "<p class='description'>This CSS will be output globally. Use with caution.</p>";
}

// 5. Output Custom CSS in Head (non-scoped)
add_action('wp_head', function() {
    $css = get_option('shopblocks_custom_css', '');
    if (!empty($css)) {
        echo '<style id="shopblocks-custom-css">' . wp_strip_all_tags($css, true) . '</style>';
    }
});