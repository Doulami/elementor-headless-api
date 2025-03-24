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

require_once EHA_PATH . 'includes/class-renderer.php';
require_once EHA_PATH . 'includes/class-api-routes.php';
require_once EHA_PATH . 'includes/class-preview-tokens.php';

if (is_admin()) {
    require_once EHA_PATH . 'admin/admin-settings.php';
}

new EHA_Api_Routes();
new EHA_Preview_Tokens();
