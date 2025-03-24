<?php
/**
 * Plugin Name: Elementor Headless API
 * Description: Exposes Elementor-rendered pages as static HTML via REST API for headless usage.
 * Version: 0.2
 * Author: Your Name
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

define('EHA_PATH', plugin_dir_path(__FILE__));
define('EHA_URL', plugin_dir_url(__FILE__));

require_once EHA_PATH . 'includes/class-api-routes.php';
require_once EHA_PATH . 'includes/class-renderer.php';

new EHA_Api_Routes();

// Optional: Clear cache on post update
add_action('save_post', function($post_id) {
    if (get_post_type($post_id) !== 'page' && get_post_type($post_id) !== 'post') return;
    delete_transient('eha_cached_html_' . $post_id);
});