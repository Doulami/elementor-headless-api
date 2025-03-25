<?php

if (!defined('ABSPATH')) {
    exit;
}


class EHA_Settings {
    
    public static function is_inline_css_enabled() {
        return get_option('elementor_css_print_method') === 'internal';
    }


    protected static $settings = null;

    public static function get($key, $default = null) {
        if (self::$settings === null) {
            self::$settings = get_option('eha_settings', []);
        }
        return isset(self::$settings[$key]) ? self::$settings[$key] : $default;
    }

    public static function is_enabled($key) {
        return self::get($key) ? true : false;
    }

    public static function get_allowed_post_types() {
        return self::get('allowed_post_types', []);
    }

    public static function post_type_allowed($post_type) {
        $allowed = self::get_allowed_post_types();
        return in_array($post_type, $allowed);
    }

    public static function cache_enabled() {
        return self::is_enabled('enable_cache');
    }

    public static function get_cache_ttl() {
        $ttl = intval(self::get('cache_duration', 12));
        return max(1, $ttl) * HOUR_IN_SECONDS;
    }

    public static function allow_nocache() {
        return self::is_enabled('allow_nocache');
    }

    public static function debug_enabled() {
        return self::is_enabled('enable_debug');
    }

    public static function include_global_styles() {
        return self::is_enabled('include_global_styles');
    }

    public static function strip_wp_noise() {
        return self::is_enabled('strip_wp_noise');
    }

    public static function inject_header_id() {
        return absint(self::get('inject_header'));
    }

    public static function inject_footer_id() {
        return absint(self::get('inject_footer'));
    }

    public static function json_enabled() {
        return self::is_enabled('enable_json');
    }

    public static function include_acf() {
        return self::is_enabled('include_acf');
    }

    public static function include_meta() {
        return self::is_enabled('include_meta');
    }

    public static function include_terms() {
        return self::is_enabled('include_terms');
    }

    public static function tokens_enabled() {
        return self::is_enabled('enable_tokens');
    }

    public static function token_expiry_hours() {
        return intval(self::get('token_expiry', 24));
    }

    public static function tokens_private_only() {
        return self::is_enabled('tokens_private_only');
    }

    public static function suppress_textdomain_notice() {
        return self::is_enabled('suppress_textdomain_notice');
    }
    
    /**
     * Get the Elementor template ID used for WooCommerce product rendering.
     */
    public static function get_product_template_id() {
        return intval(get_option('eha_product_template_id'));
    }
}


