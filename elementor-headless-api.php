<?php
/**
 * Plugin Name: Elementor Headless API
 * Description: Exposes Elementor-rendered pages as static HTML via REST API for headless usage.
 * Version: 0.2
 * Author: Your Name
 */

if (!defined('ABSPATH')) exit;

define('EHA_PATH', plugin_dir_path(__FILE__));
define('EHA_URL', plugin_dir_url(__FILE__));

// ✅ Load settings and classes
require_once EHA_PATH . 'includes/class-settings.php';
require_once EHA_PATH . 'includes/class-renderer.php';
require_once EHA_PATH . 'includes/class-api-routes.php';
require_once EHA_PATH . 'includes/class-preview-tokens.php';
require_once EHA_PATH . 'includes/class-template-suggestions-api.php';

// ✅ Load Woo routes only if WooCommerce is active
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    require_once EHA_PATH .  'includes/class-woocommerce-api-routes.php';
}

// ✅ Load admin settings page if in admin
if (is_admin()) {
    require_once EHA_PATH . 'admin/admin-settings.php';
}

// ✅ Register REST API routes
add_action( 'rest_api_init', function () {
    

    if ( class_exists( 'EHA_WooCommerce_API_Routes' ) ) {
        EHA_WooCommerce_API_Routes::register_routes();
    }
});

// ✅ Instantiate your key classes early
new EHA_API_Routes();
new EHA_Preview_Tokens();