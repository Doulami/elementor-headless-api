<?php
// admin/admin-settings.php

if (!defined('ABSPATH')) {
    exit;
}

add_action('admin_menu', 'eha_register_settings_page');
add_action('admin_init', 'eha_register_settings');
    register_setting('eha_settings_group', 'eha_product_template_id');





function eha_register_settings_page() {
    add_options_page(
        'Elementor Headless API Settings',
        'Elementor Headless API',
        'manage_options',
        'eha-settings',
        'eha_render_settings_page'
    );
}

function eha_register_settings() {
    register_setting('eha_settings_group', 'eha_settings');

    // General
    add_settings_section('eha_general', 'General Settings', '__return_false', 'eha-settings');

    add_settings_field('allowed_post_types', 'Allowed Post Types', 'eha_render_post_types_field', 'eha-settings', 'eha_general');
    add_settings_field('enable_cache', 'Enable Cache', 'eha_render_checkbox', 'eha-settings', 'eha_general', ['id' => 'enable_cache']);
    add_settings_field('cache_duration', 'Cache Duration (hours)', 'eha_render_number', 'eha-settings', 'eha_general', ['id' => 'cache_duration']);
    add_settings_field('enable_debug', 'Enable Debug Info', 'eha_render_checkbox', 'eha-settings', 'eha_general', ['id' => 'enable_debug']);
    add_settings_field('allow_nocache', 'Allow ?nocache=1 Bypass', 'eha_render_checkbox', 'eha-settings', 'eha_general', ['id' => 'allow_nocache']);

    // HTML Output
    add_settings_section('eha_html_output', 'HTML Output Settings', '__return_false', 'eha-settings');
    add_settings_field('inject_header', 'Inject Header Template', 'eha_render_elementor_template_dropdown', 'eha-settings', 'eha_html_output', ['id' => 'inject_header']);
    add_settings_field('inject_footer', 'Inject Footer Template', 'eha_render_elementor_template_dropdown', 'eha-settings', 'eha_html_output', ['id' => 'inject_footer']);
    add_settings_field('include_global_styles', 'Include Elementor Global Styles', 'eha_render_checkbox', 'eha-settings', 'eha_html_output', ['id' => 'include_global_styles']);
    add_settings_field('strip_wp_noise', 'Strip WP Header/Footer Noise', 'eha_render_checkbox', 'eha-settings', 'eha_html_output', ['id' => 'strip_wp_noise']);

    // JSON Output
    add_settings_section('eha_json', 'JSON Output Settings', '__return_false', 'eha-settings');
    add_settings_field('enable_json', 'Enable JSON Mode (?format=json)', 'eha_render_checkbox', 'eha-settings', 'eha_json', ['id' => 'enable_json']);
    add_settings_field('include_acf', 'Include ACF Fields', 'eha_render_checkbox', 'eha-settings', 'eha_json', ['id' => 'include_acf']);
    add_settings_field('include_meta', 'Include post_meta in JSON', 'eha_render_checkbox', 'eha-settings', 'eha_json', ['id' => 'include_meta']);
    add_settings_field('include_terms', 'Include Related Terms (taxonomy)', 'eha_render_checkbox', 'eha-settings', 'eha_json', ['id' => 'include_terms']);

    // Token Preview

    // WooCommerce
    add_settings_section('eha_woo', 'WooCommerce Integration', '__return_false', 'eha-settings');

    add_settings_field(
        'eha_product_template_id',
        'Woo Product Template ID',
        function () {
            $value = get_option('eha_product_template_id', '');
            echo '<input type="number" name="eha_product_template_id" value="' . esc_attr($value) . '" class="regular-text" />';
            echo '<p class="description">Enter the Elementor template ID to use when a Woo product is not built with Elementor directly.</p>';
        },
        'eha-settings',
        'eha_woo'
    );

    register_setting('eha_settings_group', 'eha_product_template_id');

    add_settings_section('eha_tokens', 'Preview & Tokens', '__return_false', 'eha-settings');
    add_settings_field('enable_tokens', 'Enable Token Previews', 'eha_render_checkbox', 'eha-settings', 'eha_tokens', ['id' => 'enable_tokens']);
    add_settings_field('token_expiry', 'Token Expiry (hours)', 'eha_render_number', 'eha-settings', 'eha_tokens', ['id' => 'token_expiry']);
    add_settings_field('tokens_private_only', 'Allow Token Access to Private Posts Only', 'eha_render_checkbox', 'eha-settings', 'eha_tokens', ['id' => 'tokens_private_only']);

    // Translation Fix
    add_settings_section('eha_dev', 'Developer Tools', '__return_false', 'eha-settings');
    add_settings_field('suppress_textdomain_notice', 'Suppress translation loading notice', 'eha_render_checkbox', 'eha-settings', 'eha_dev', ['id' => 'suppress_textdomain_notice']);
    add_settings_section(
        'eha_woo_section',
        'WooCommerce Settings',
        null,
        'eha_settings'
    );

    add_settings_field(
        'eha_product_template_id',
        'Woo Product Template ID',
        function() {
            $value = get_option('eha_product_template_id', '');
            echo '<input type="number" name="eha_product_template_id" value="' . esc_attr($value) . '" class="regular-text" />';
            echo '<p class="description">Enter the Elementor template ID to use for WooCommerce product rendering fallback.</p>';
        },
        'eha_settings',
        'eha_woo_section'
    );
}

function eha_render_settings_page() {
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

function eha_render_post_types_field() {
    $options = get_option('eha_settings');
    $allowed = isset($options['allowed_post_types']) ? $options['allowed_post_types'] : [];

    $post_types = get_post_types(['public' => true], 'objects');
    foreach ($post_types as $pt) {
        $checked = in_array($pt->name, $allowed) ? 'checked' : '';
        echo "<label><input type='checkbox' name='eha_settings[allowed_post_types][]' value='{$pt->name}' $checked> {$pt->labels->singular_name}</label><br/>";
    }
}

function eha_render_checkbox($args) {
    $options = get_option('eha_settings');
    $id = $args['id'];
    $checked = isset($options[$id]) && $options[$id] ? 'checked' : '';
    echo "<input type='checkbox' name='eha_settings[$id]' value='1' $checked />";
    if ($id === 'include_global_styles' && get_option('elementor_experiment_improved_css_loading') !== 'active') {
        echo '<div class=\"notice notice-warning\" style=\"margin:10px 0;padding:10px;background:#fff3cd;border-left:5px solid #ffcc00;\">';
        echo '<p><strong>Performance Tip:</strong> Elementor’s Inline CSS mode is currently <strong>disabled</strong>. For cleaner HTML and faster rendering, consider enabling it.</p>';
        echo '<p><a href=\"admin.php?page=elementor#tab-advanced\" target=\"_blank\" class=\"button button-small\">Go to Elementor Settings</a></p>';
        echo '</div>';
    }
}

function eha_render_number($args) {
    $options = get_option('eha_settings');
    $id = $args['id'];
    $val = isset($options[$id]) ? intval($options[$id]) : '';
    echo "<input type='number' name='eha_settings[$id]' value='$val' />";
}

function eha_render_elementor_template_dropdown($args) {
    $options = get_option('eha_settings');
    $id = $args['id'];
    $selected = isset($options[$id]) ? $options[$id] : '';

    $templates = get_posts([
        'post_type' => 'elementor_library',
        'posts_per_page' => -1,
        'post_status' => 'publish'
    ]);

    echo "<select name='eha_settings[$id]'>";
    echo "<option value=''>-- None --</option>";
    foreach ($templates as $tpl) {
        $is_selected = selected($selected, $tpl->ID, false);
        echo "<option value='{$tpl->ID}' $is_selected>{$tpl->post_title}</option>";
    }
    
    if ($id === 'inject_header') {
        $current_post_id = isset($_GET['post']) ? intval($_GET['post']) : 0;
        $template_slug = $current_post_id ? get_page_template_slug($current_post_id) : '';
        $using_canvas = $template_slug === 'elementor_canvas';
        $settings = get_option('eha_settings');
        if (empty($settings['inject_header']) && empty($settings['inject_footer']) && !$using_canvas) {
            echo '<div class=\"notice notice-warning\" style=\"margin:10px 0;padding:10px;background:#fff3cd;border-left:5px solid #ffcc00;\">';
            echo '<p><strong>Heads up:</strong> No Elementor Header/Footer templates selected, and this page is not using the <code>Elementor Canvas</code> layout. Your theme’s layout may not appear in headless output.</p>';
            if (!empty($template_slug)) {
                echo '<p>Current Template: <code>' . esc_html($template_slug) . '</code></p>';
            }
            echo '</div>';
        }
    }

    echo "</select>";
}
