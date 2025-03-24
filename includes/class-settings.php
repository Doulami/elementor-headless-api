<?php

class EHA_Settings {

    public static function init() {
        add_action('admin_menu', [__CLASS__, 'add_admin_menu']);
        add_action('admin_init', [__CLASS__, 'register_settings']);
    }

    
    public static function add_admin_menu() {
        add_options_page(
            'Elementor Headless API',
            'Elementor Headless API',
            'manage_options',
            'elementor-headless-api',
            [__CLASS__, 'render_settings_page']
        );
    }

    public static function register_settings() {
        register_setting('eha_settings_group', 'eha_allowed_post_types');

        add_settings_section(
            'eha_main_section',
            'Main Settings',
            null,
            'elementor-headless-api'
        );

        add_settings_field(
            'eha_allowed_post_types',
            'Allowed Post Types',
            [__CLASS__, 'allowed_post_types_field'],
            'elementor-headless-api',
            'eha_main_section'
        );
        register_setting('eha_settings_group', 'eha_header_template_id');
        register_setting('eha_settings_group', 'eha_footer_template_id');

        add_settings_field(
            'eha_header_template_id',
            'Header Template ID',
            [__CLASS__, 'header_template_field'],
            'elementor-headless-api',
            'eha_main_section'
        );

        add_settings_field(
            'eha_footer_template_id',
            'Footer Template ID',
            [__CLASS__, 'footer_template_field'],
            'elementor-headless-api',
            'eha_main_section'
        );

    }

    public static function allowed_post_types_field() {
        $post_types = get_post_types(['public' => true], 'objects');
        $selected = (array) get_option('eha_allowed_post_types', ['page', 'post']);

        foreach ($post_types as $slug => $pt) {
            echo '<label><input type="checkbox" name="eha_allowed_post_types[]" value="' . esc_attr($slug) . '" ' . checked(in_array($slug, $selected), true, false) . '> ' . esc_html($pt->label) . '</label><br>';
        }
    }
    public static function header_template_field() {
        $value = get_option('eha_header_template_id', '');
        echo '<input type="number" name="eha_header_template_id" value="' . esc_attr($value) . '" class="regular-text">';
        echo '<p class="description">Enter the Elementor Template ID to inject as the Header.</p>';
    }
    
    public static function footer_template_field() {
        $value = get_option('eha_footer_template_id', '');
        echo '<input type="number" name="eha_footer_template_id" value="' . esc_attr($value) . '" class="regular-text">';
        echo '<p class="description">Enter the Elementor Template ID to inject as the Footer.</p>';
    }
    

    public static function render_settings_page() {
        echo '<div class="wrap">';
        echo '<h1>Elementor Headless API Settings</h1>';
        echo '<form method="post" action="options.php">';
        settings_fields('eha_settings_group');
        do_settings_sections('elementor-headless-api');
        submit_button();
        echo '</form></div>';
    }
}

EHA_Settings::init();
