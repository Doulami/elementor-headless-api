<?php
// admin/admin-settings.php

if (!defined('ABSPATH')) {
    exit;
}

add_action('admin_menu', 'eha_register_settings_page');
add_action('admin_init', 'eha_register_settings');






function eha_register_settings_page() {
    // Move the Settings page to a top-level menu under Elementor Headless API
    add_menu_page(
        'Elementor Headless API Settings',   // Page title
        'Elementor Headless API',            // Menu title
        'manage_options',                    // Capability
        'eha-settings',                      // Menu slug
        'eha_render_settings_page',          // Callback function for rendering settings page
        'dashicons-admin-generic',           // Icon
        100                                  // Position in the admin menu
    );
}

function eha_register_settings() {
    register_setting('eha-settings_group', 'eha-settings');
    register_setting('eha-settings_group', 'eha_product_template_id');


    // General
    add_settings_section('eha_general', 'General Settings', '__return_false', 'eha-settings');

    add_settings_field('allowed_post_types', 'Allowed Post Types', 'eha_render_post_types_field', 'eha-settings', 'eha_general');
    add_settings_field('enable_cache', 'Enable Cache', 'eha_render_checkbox', 'eha-settings', 'eha_general', ['id' => 'enable_cache']);
    add_settings_field('cache_duration', 'Cache Duration (hours)', 'eha_render_number', 'eha-settings', 'eha_general', ['id' => 'cache_duration']);
    add_settings_field('enable_debug', 'Enable Debug Info', 'eha_render_checkbox', 'eha-settings', 'eha_general', ['id' => 'enable_debug']);
    add_settings_field('allow_nocache', 'Allow ?nocache=1 Bypass', 'eha_render_checkbox', 'eha-settings', 'eha_general', ['id' => 'allow_nocache']);

    // HTML Output
    add_settings_section('eha_html_output', 'HTML Output Settings', '__return_false', 'eha-settings');

    add_settings_field('inject_header', 'Inject Header Template', 'eha_render_select2_template_field', 'eha-settings', 'eha_html_output', ['id' => 'inject_header']);
    add_settings_field('inject_footer', 'Inject Footer Template', 'eha_render_select2_template_field', 'eha-settings', 'eha_html_output', ['id' => 'inject_footer']);
    
    
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

    add_settings_field('eha_product_template_id', 'Woo Product Template ID', 'eha_render_woo_template_select2', 'eha-settings', 'eha_woo'); 
     


    add_settings_section('eha_tokens', 'Preview & Tokens', '__return_false', 'eha-settings');
    add_settings_field('enable_tokens', 'Enable Token Previews', 'eha_render_checkbox', 'eha-settings', 'eha_tokens', ['id' => 'enable_tokens']);
    add_settings_field('token_expiry', 'Token Expiry (hours)', 'eha_render_number', 'eha-settings', 'eha_tokens', ['id' => 'token_expiry']);
    add_settings_field('tokens_private_only', 'Allow Token Access to Private Posts Only', 'eha_render_checkbox', 'eha-settings', 'eha_tokens', ['id' => 'tokens_private_only']);

    // Translation Fix
    add_settings_section('eha_dev', 'Developer Tools', '__return_false', 'eha-settings');
    add_settings_field('suppress_textdomain_notice', 'Suppress translation loading notice', 'eha_render_checkbox', 'eha-settings', 'eha_dev', ['id' => 'suppress_textdomain_notice']);

}

function eha_render_settings_page() {
    ?>
    <div class="wrap">
        <h1>Elementor Headless API Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('eha-settings_group');
            do_settings_sections('eha-settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

function eha_render_post_types_field() {
    $options = get_option('eha-settings');
    $allowed = isset($options['allowed_post_types']) ? $options['allowed_post_types'] : [];

    $post_types = get_post_types(['public' => true], 'objects');
    foreach ($post_types as $pt) {
        $checked = in_array($pt->name, $allowed) ? 'checked' : '';
        echo "<label><input type='checkbox' name='eha-settings[allowed_post_types][]' value='{$pt->name}' $checked> {$pt->labels->singular_name}</label><br/>";
    }
}

function eha_render_checkbox($args) {
    $options = get_option('eha-settings');
    $id = $args['id'];
    $checked = isset($options[$id]) && $options[$id] ? 'checked' : '';
    echo "<input type='checkbox' name='eha-settings[$id]' value='1' $checked />";
    if ($id === 'include_global_styles' && get_option('elementor_experiment_improved_css_loading') !== 'active') {
        echo '<div class=\"notice notice-warning\" style=\"margin:10px 0;padding:10px;background:#fff3cd;border-left:5px solid #ffcc00;\">';
        echo '<p><strong>Performance Tip:</strong> Elementor’s Inline CSS mode is currently <strong>disabled</strong>. For cleaner HTML and faster rendering, consider enabling it.</p>';
        echo '<p><a href=\"admin.php?page=elementor#tab-advanced\" target=\"_blank\" class=\"button button-small\">Go to Elementor Settings</a></p>';
        echo '</div>';
    }
}

function eha_render_number($args) {
    $options = get_option('eha-settings');
    $id = $args['id'];
    $val = isset($options[$id]) ? intval($options[$id]) : '';
    echo "<input type='number' name='eha-settings[$id]' value='$val' />";
}

function eha_render_elementor_template_dropdown($args) {
    $options = get_option('eha-settings');
    $id = $args['id'];
    $selected = isset($options[$id]) ? $options[$id] : '';

    $templates = get_posts([
        'post_type' => 'elementor_library',
        'posts_per_page' => -1,
        'post_status' => 'publish'
    ]);

    echo "<select name='eha-settings[$id]'>";
    echo "<option value=''>-- None --</option>";
    foreach ($templates as $tpl) {
        $is_selected = selected($selected, $tpl->ID, false);
        echo "<option value='{$tpl->ID}' $is_selected>{$tpl->post_title}</option>";
    }
    
    if ($id === 'inject_header') {
        $current_post_id = isset($_GET['post']) ? intval($_GET['post']) : 0;
        $template_slug = $current_post_id ? get_page_template_slug($current_post_id) : '';
        $using_canvas = $template_slug === 'elementor_canvas';
        $settings = get_option('eha-settings');
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


add_action('admin_enqueue_scripts', 'eha_enqueue_template_search_assets');
function eha_enqueue_template_search_assets($hook) {
    if ($hook !== 'settings_page_eha-settings') return;

    wp_enqueue_script('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', ['jquery'], null, true);
    wp_enqueue_style('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css');

    wp_add_inline_script('select2', "
    jQuery(document).ready(function($) {
        $('.eha-template-search').each(function() {
            const \$select = $(this);
            const currentVal = \$select.data('current');
            const currentText = \$select.data('label');

            if (currentVal && currentText) {
                const option = new Option(currentText, currentVal, true, true);
                \$select.append(option).trigger('change');
                \$select.data('placeholder', currentText);
            }

            \$select.select2({
                placeholder: \$select.data('placeholder') || 'Select a template...',
                ajax: {
                    url: '/wp-json/headless-elementor/v1/template-suggestions',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return { search: params.term };
                    },
                    processResults: function(data) {
                        return {
                            results: data.map(item => ({ id: item.id, text: item.label }))
                        };
                    }
                },
                minimumInputLength: 1,
                width: '100%'
                 });
             });
         });
    ");
}


function eha_render_select2_template_field($args) {
    $options = get_option('eha-settings');
    $id = $args['id'];
    $val = isset($options[$id]) ? intval($options[$id]) : '';
    $label = $val ? get_the_title($val) : '';
    echo "<select  style='width:280px' class='eha-template-search' name='eha-settings[$id]' data-current='$val' data-label='" . esc_attr($label) . "'></select>";
}

function eha_render_woo_template_select2() {
    $val = get_option('eha_product_template_id');
    $label = $val ? get_the_title($val) : '';
    echo "<select style='width:280px' class='eha-template-search' name='eha_product_template_id' data-current='$val' data-label='" . esc_attr($label) . "'></select>";
}

// Hook to add the custom admin page for tools/debugging
add_action('admin_menu', 'eha_add_tools_debug_page');

function eha_add_tools_debug_page() {
    // Add a new menu under Elementor Headless API for tools/debugging
    add_menu_page(
        'Render Page for Debugging',        // Page title
        'Tools',                           // Menu title
        'manage_options',                  // Capability
        'eha-render-debug',                // Menu slug
        'eha_render_debug_interface',       // Callback function for rendering the page
        'dashicons-tools',                 // Icon
        102                                // Position (set after Render Page to keep them ordered)
    );
}



add_action('admin_enqueue_scripts', 'eha_enqueue_code_editor');

function eha_get_all_pages() {
    // Fetch all published pages
    $args = [
        'post_type'      => 'page',        // We are fetching pages
        'posts_per_page' => -1,            // Fetch all pages (no limit)
        'post_status'    => 'publish',     // Only published pages
    ];

    // Use get_posts() to retrieve the pages
    $pages = get_posts($args);

    // Return the list of pages
    return $pages;
}
function eha_get_all_stylesheets($page_id) {
    // Initialize an empty array to store stylesheets
    $stylesheets = [
        'theme' => [],
        'elementor' => [],
        'global' => [],  // Added a 'global' group for global styles
        'other' => []
    ];

    // Get stylesheets enqueued by WordPress (theme and plugins)
    global $wp_styles;

    foreach ($wp_styles->queue as $handle) {
        $stylesheet = $wp_styles->registered[$handle];
        $stylesheet_url = $stylesheet->src;

        // Group by theme, Elementor, global, or other sources
        if (strpos($stylesheet_url, get_stylesheet_directory_uri()) !== false) {
            // If the stylesheet is from the theme
            $stylesheets['theme'][] = ['id' => $handle, 'url' => $stylesheet_url, 'label' => $stylesheet->handle];
        } elseif (strpos($stylesheet_url, 'elementor') !== false) {
            // If the stylesheet is from Elementor
            $stylesheets['elementor'][] = ['id' => $handle, 'url' => $stylesheet_url, 'label' => $stylesheet->handle];
        } else {
            // Other sources
            $stylesheets['other'][] = ['id' => $handle, 'url' => $stylesheet_url, 'label' => $stylesheet->handle];
        }
    }

    // Get Elementor global styles - manually inspect the styles
    if (class_exists('Elementor\Plugin')) {
        // Check if Elementor frontend exists and pull global stylesheets
        $elementor_styles = ElementorPlugin::$instance->frontend->get_stylesheets();
        
        // Push global styles into the 'global' group
        foreach ($elementor_styles as $style) {
            // Verify that $style contains a valid URL
            if (isset($style['src'])) {
                $stylesheets['global'][] = [
                    'id' => $style['id'] ?? '',
                    'url' => $style['src'],
                    'label' => $style['handle'] ?? 'Global Style'
                ];
            }
        }
    }

    return $stylesheets;
}

function eha_render_debug_interface() {
    // Get all pages (use the previously defined function)
    $pages = eha_get_all_pages();

    ?>
    <div class="wrap">
        <h1>Render Page with Stylesheets - Debug Tools</h1>
        <form method="POST">
            <!-- Page Selector -->
            <label for="page-selector">Select a Page</label>
            <select name="page-id" id="page-selector">
                <?php foreach ($pages as $page) : ?>
                    <option value="<?php echo esc_attr($page->ID); ?>"><?php echo esc_html($page->post_title); ?></option>
                <?php endforeach; ?>
            </select>

            <!-- Stylesheet Selector -->
            <h3>Select Stylesheets</h3>

            <?php 
            // Get all stylesheets for the selected page
            $stylesheets = eha_get_all_stylesheets($pages[0]->ID); // Default to the first page

            // Grouped stylesheets
            foreach ($stylesheets as $group => $group_stylesheets) : ?>
                <h4><?php echo ucfirst($group); ?> Stylesheets</h4>
                <?php foreach ($group_stylesheets as $stylesheet) : ?>
                    <label for="stylesheet-<?php echo esc_attr($stylesheet['id']); ?>">
                        <input type="checkbox" name="stylesheets[]" id="stylesheet-<?php echo esc_attr($stylesheet['id']); ?>" value="<?php echo esc_attr($stylesheet['url']); ?>" checked />
                        <?php echo esc_html($stylesheet['label']); ?>
                    </label><br>
                <?php endforeach; ?>
            <?php endforeach; ?>

            <!-- Render Button -->
            <button type="submit" name="render-page">Render Page</button>
        </form>

        <hr>

        <!-- HTML Editor for the rendered page -->
        <h2>Rendered HTML (Code Editor)</h2>
        <div id="editor" style="height: 400px; width: 100%;"></div>  <!-- Container for the Ace Editor -->

        <?php
        if (isset($_POST['render-page'])) {
            $page_id = $_POST['page-id'];
            $selected_stylesheets = isset($_POST['stylesheets']) ? $_POST['stylesheets'] : [];

            // Render the page HTML with selected stylesheets
            $html_content = eha_render_page_html($page_id, $selected_stylesheets);

            echo '<h3>Rendered HTML</h3>';
            echo '<pre>' . esc_html($html_content) . '</pre>';
        }
        ?>
    </div>

    <script type="text/javascript">
        // Initialize Ace Editor
        var editor = ace.edit("editor");
        editor.setTheme("ace/theme/monokai"); 
        editor.getSession().setMode("ace/mode/html");

        // Set the content of the editor with the rendered HTML
        editor.setValue('<?php echo addslashes($html_content); ?>');
    </script>
    <?php
}

function eha_enqueue_code_editor() {
    // Only load on the tools/debug page
    if (isset($_GET['page']) && $_GET['page'] == 'eha-render-debug') {
        // Enqueue Ace editor
        wp_enqueue_script('ace-editor', 'https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.12/ace.js', [], null, true);
        wp_enqueue_style('ace-editor-style', 'https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.12/theme-monokai.min.css', [], null);
    }
}