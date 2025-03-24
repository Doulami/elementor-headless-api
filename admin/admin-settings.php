<?php

if (!defined('ABSPATH')) exit;

class EHA_Admin_Settings {
    public function __construct() {
        add_action('admin_menu', [$this, 'add_settings_page']);
        add_action('admin_init', [$this, 'register_settings']);
    }

    public function add_settings_page() {
        add_options_page(
            'Elementor Headless Settings',
            'Headless Elementor',
            'manage_options',
            'eha-settings',
            [$this, 'render_settings_page']
        );
    }

    public function register_settings() {
        register_setting('eha_settings_group', 'eha_allowed_post_types');

        add_settings_section('eha_main_section', '', null, 'eha-settings');

        add_settings_field(
            'eha_allowed_post_types',
            'Allowed Post Types',
            [$this, 'render_post_types_field'],
            'eha-settings',
            'eha_main_section'
        );
    }

    public function render_post_types_field() {
        $selected = get_option('eha_allowed_post_types', ['post', 'page']);
        $post_types = get_post_types(['public' => true], 'objects');

        foreach ($post_types as $slug => $type) {
            printf(
                '<label><input type="checkbox" name="eha_allowed_post_types[]" value="%1$s" %2$s> %3$s</label><br>',
                esc_attr($slug),
                in_array($slug, $selected) ? 'checked' : '',
                esc_html($type->labels->name)
            );
        }
    }

    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1>Elementor Headless API Settings</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('eha_settings_group');
                do_settings_sections('eha-settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
}

new EHA_Admin_Settings();
