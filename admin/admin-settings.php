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

    add_settings_field('inject_header', 'Inject Header Template', 'eha_render_elementor_template_dropdown', 'eha-settings', 'eha_html_output', ['id' => 'inject_header']);
    add_settings_field('inject_footer', 'Inject Footer Template', 'eha_render_elementor_template_dropdown', 'eha-settings', 'eha_html_output', ['id' => 'inject_footer']);
    
    
    add_settings_field('include_global_styles', 'Include Elementor Global Styles', 'eha_render_checkbox', 'eha-settings', 'eha_html_output', ['id' => 'include_global_styles']);
    add_settings_field('strip_wp_noise', 'Strip WP Header/Footer/AdminBar Noise', 'eha_render_checkbox', 'eha-settings', 'eha_html_output', ['id' => 'strip_wp_noise']);

    // JSON Output
    add_settings_section('eha_json', 'JSON Output Settings', '__return_false', 'eha-settings');
    add_settings_field('enable_json', 'Enable JSON Mode (?format=json)', 'eha_render_checkbox', 'eha-settings', 'eha_json', ['id' => 'enable_json']);
    add_settings_field('include_acf', 'Include ACF Fields', 'eha_render_checkbox', 'eha-settings', 'eha_json', ['id' => 'include_acf']);
    add_settings_field('include_meta', 'Include post_meta in JSON', 'eha_render_checkbox', 'eha-settings', 'eha_json', ['id' => 'include_meta']);
    add_settings_field('include_terms', 'Include Related Terms (taxonomy)', 'eha_render_checkbox', 'eha-settings', 'eha_json', ['id' => 'include_terms']);

    // Token Preview

    // WooCommerce
    add_settings_section('eha_woo', 'WooCommerce Integration', '__return_false', 'eha-settings');

    add_settings_field('eha_product_template_id', 'Woo Product Template ID', 'eha_render_elementor_template_dropdown', 'eha-settings', 'eha_woo'); 
     


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

    // Get all post types
    $post_types = get_post_types([], 'objects');

    // Output the "Check All" checkbox
    echo "<label><input type='checkbox' id='eha-check-all'> <strong>Select All</strong></label><br/><br/>";

    // Output the individual post type checkboxes
    foreach ($post_types as $pt) {
        if (!isset($pt->show_ui) || !$pt->show_ui) {
            continue;
        }

        $checked = in_array($pt->name, $allowed) ? 'checked' : '';
        echo "<label><input class='eha-post-type-checkbox' type='checkbox' name='eha-settings[allowed_post_types][]' value='{$pt->name}' $checked> {$pt->labels->singular_name}</label><br/>";
    }

    // Add JavaScript to handle "Check All"
    ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const checkAll = document.getElementById('eha-check-all');
            const checkboxes = document.querySelectorAll('.eha-post-type-checkbox');

            if (!checkAll) return;

            // Update all when "check all" is toggled
            checkAll.addEventListener('change', function () {
                checkboxes.forEach(cb => cb.checked = checkAll.checked);
            });

            // Update "check all" based on individual checks
            checkboxes.forEach(cb => {
                cb.addEventListener('change', () => {
                    checkAll.checked = Array.from(checkboxes).every(cb => cb.checked);
                });
            });

            // Initial sync
            checkAll.checked = Array.from(checkboxes).every(cb => cb.checked);
        });
    </script>
    <?php
}

function eha_render_checkbox($args) {
    $options = get_option('eha-settings');
    $id = $args['id'];
    $checked = isset($options[$id]) && $options[$id] ? 'checked="checked"' : '';
    $value = isset($options[$id]) ? $options[$id] : '0';
    
    // Only hide the label for 'include_global_styles'
    if ($id === 'include_global_styles') {
        echo '<label style="display:none;">';
        echo "<input type='hidden' name='eha-settings[$id]' value='0'>"; // Hidden field for unchecked state
        echo "<input type='checkbox' name='eha-settings[$id]' value='1' $checked />";
        echo ' Include Elementor Global Styles';
        echo '</label>';
    } else {
        echo '<label>';
        echo "<input type='hidden' name='eha-settings[$id]' value='0'>"; // Hidden field for unchecked state
        echo "<input type='checkbox' name='eha-settings[$id]' value='1' $checked />";
        echo '</label>';
    }

    // Additional message for 'include_global_styles'
    if ($id === 'include_global_styles') {
        $css_method = get_option('elementor_css_print_method');
        if ($css_method !== 'internal') {
            echo '<span style="margin-left: 10px; color: #856404;"><strong>⚠️ Performance Tip:</strong> Set Elementor CSS method to <strong>Internal Embedding</strong> for cleaner HTML. ';
            echo '<a href="admin.php?page=elementor-settings#tab-performance" target="_blank">Change Settings</a></span>';
        } else {
            echo '<span style="margin-left: 10px; color: #155724;"><strong>✅ All Good:</strong> Internal Embedding is enabled. ';
            echo '<a href="admin.php?page=elementor-settings#tab-performance" target="_blank">Change Settings</a></span>';
        }
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

    $select_id = 'eha-settings-' . esc_attr($id);

    // Load Select2 assets (only once)
    static $select2_loaded = false;
    if (!$select2_loaded) {
        echo '<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />';
        echo '<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>';
        echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
                const selects = document.querySelectorAll(".eha-select2");
                selects.forEach(function(select) {
                    jQuery(select).select2();
                });
            });
        </script>';
        $select2_loaded = true;
    }

    echo "<select name='eha-settings[$id]' id='$select_id' class='eha-select2' >";
    echo "<option value=''>-- None --</option>";

    $all_post_types = get_post_types([], 'names'); // get even non-public ones
    $templates = get_posts([
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'post_type'      => $all_post_types,
    ]);

    foreach ($templates as $tpl) {
        $is_selected = selected($selected, $tpl->ID, false);
        echo "<option value='{$tpl->ID}' $is_selected>{$tpl->post_title}</option>";
    }

    echo "</select>";

    // Optional notice for canvas template when no header/footer selected
    if ($id === 'inject_header') {
        $current_post_id = isset($_GET['post']) ? intval($_GET['post']) : 0;
        $template_slug = $current_post_id ? get_page_template_slug($current_post_id) : '';
        $using_canvas = $template_slug === 'elementor_canvas';
        $settings = get_option('eha-settings');

        if (empty($settings['inject_header']) && empty($settings['inject_footer']) && !$using_canvas) {
            echo '<div class="notice notice-warning" style="margin:10px 0;padding:10px;background:#fff3cd;border-left:5px solid #ffcc00;">';
            echo '<p><strong>Heads up:</strong> No Header/Footer selected, and this page is not using the <code>Elementor Canvas</code> layout. The headless output might not include your theme layout.</p>';
            if (!empty($template_slug)) {
                echo '<p>Current Template: <code>' . esc_html($template_slug) . '</code></p>';
            }
            echo '</div>';
        }
    }
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
        'global' => [],  // For global styles like Elementor's injected styles
        'other' => []
    ];

    // Get all enqueued stylesheets from WordPress (theme and plugins)
    global $wp_styles;

    // Loop through all enqueued styles
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
	
/*/ Check if Elementor is active and initialized
if (class_exists('Elementor\Plugin') && Elementor\Plugin::$instance->frontend) {
    try {
 
        
 
        
        // Method 3: Last resort - generate from settings
        if (empty($global_css)) {

  if (empty($global_css)) {
    echo "<!-- Entering kit fallback section -->\n";
    
    $kit = Elementor\Plugin::$instance->kits_manager->get_active_kit();
    //var_dump('Kit object:', $kit); // Check if kit exists
    
    if ($kit) {
        echo "<!-- Kit exists -->\n";
        
        // 1. First try getting all kit settings
        $all_settings = $kit->get_settings();
        var_dump('All kit settings:', $all_settings);
        
        // 2. Specifically check for custom CSS
        $custom_css = $kit->get_settings('custom_css');
        var_dump('Custom CSS:', $custom_css);
        
        // 3. Check system colors and fonts which generate global CSS
        $system_items = [
            'system_colors',
            'system_typography',
            'custom_colors',
            'custom_typography'
        ];
        
        foreach ($system_items as $item) {
            $settings = $kit->get_settings($item);
            var_dump("{$item} settings:", $settings);
        }
        
        // 4. Try to force-generate the CSS
        if (method_exists($kit, 'get_frontend_settings')) {
            $frontend_settings = $kit->get_frontend_settings();
            var_dump('Frontend settings:', $frontend_settings);
            
            if (!empty($frontend_settings['custom_css'])) {
                $global_css = $frontend_settings['custom_css'];
                echo "<!-- Found custom CSS in frontend settings -->\n";
            }
        }
        
        if (empty($global_css)) {
            echo "<!-- Still no CSS found -->\n";
            error_log('Elementor Kit exists but no global CSS found in: ' . print_r($all_settings, true));
        }
    } else {
        echo "<!-- No active kit found -->\n";
        error_log('No active Elementor kit found');
    }
}
        }

        if (empty($global_css)) {
            error_log('Elementor global styles are empty. Check your Elementor Site Settings.');
        }
    } catch (Exception $e) {
        error_log('Error getting Elementor styles: ' . $e->getMessage());
    }
} else {
    error_log('Elementor is not active or not initialized.');
}
  /*/

    return $stylesheets;
}


add_action('admin_enqueue_scripts', 'eha_enqueue_code_editor');
function eha_enqueue_code_editor($hook) {
    if (isset($_GET['page']) && $_GET['page'] == 'eha-render-debug') {
        // Enqueue Ace editor core script
        wp_enqueue_script('ace-editor', 'https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.12/ace.js', [], null, true);

        // Enqueue the Monokai theme and JavaScript mode script
        wp_enqueue_script('ace-editor-monokai', 'https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.12/theme-monokai.js', ['ace-editor'], null, true);
        wp_enqueue_script('ace-editor-javascript', 'https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.12/mode-javascript.js', ['ace-editor'], null, true);
    }
}
 
add_action('admin_head', 'eha_add_editor_styles');
function eha_add_editor_styles() {
    ?>
    <style type="text/css">
        #editor {
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            height: 500px; /* Adjust as needed */
            font-size: 14px;
        }
    </style>
    <?php
}

  function eha_render_debug_interface() {
    $pages = eha_get_all_pages();
    $current_page_id = isset($_POST['page-id']) ? intval($_POST['page-id']) : ($pages[0]->ID ?? 0);
    ?>
    <div class="wrap">
        <h1>Page Render Debugger</h1>
        
        <!-- Form Section -->
        <form method="POST">
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="page-selector">Select Page</label></th>
                    <td>
                        <select name="page-id" id="page-selector" class="regular-text">
                            <?php foreach ($pages as $page) : ?>
                                <option value="<?php echo esc_attr($page->ID); ?>" <?php selected($page->ID, $current_page_id); ?>>
                                    <?php echo esc_html($page->post_title); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <button type="submit" name="render-page" class="button button-primary">
                    <span class="dashicons dashicons-editor-code"></span> Render Page
                </button>
            </p>
        </form>

        <?php if (isset($_POST['render-page'])) : ?>
            <?php
            $renderer = new EHA_Renderer();
            $html_content = $renderer->render_elementor_page($current_page_id);

            if (is_wp_error($html_content)) {
                echo '<div class="notice notice-error"><p>Error: ' . esc_html($html_content->get_error_message()) . '</p></div>';
            } else {
                // Get header and footer content
                $header_id = EHA_Settings::inject_header_id();
                $footer_id = EHA_Settings::inject_footer_id();
                
                $header_html = '';
                $footer_html = '';
                
                if ($header_id && is_numeric($header_id)) {
                    $header_html = ElementorPlugin::instance()->frontend->get_builder_content_for_display($header_id);
                }
                
                if ($footer_id && is_numeric($footer_id)) {
                    $footer_html = ElementorPlugin::instance()->frontend->get_builder_content_for_display($footer_id);
                }
                
                // Get all CSS for the page
                ob_start();
                wp_head();
                $styles = ob_get_clean();
                
                // Prepare the preview HTML with proper base URL
                $preview_html = '<!DOCTYPE html><html><head>';
                $preview_html .= '<base href="' . site_url() . '">';
                $preview_html .= $styles;
                $preview_html .= '</head><body>';
                $preview_html .= $header_html;
                $preview_html .= $html_content;
                $preview_html .= $footer_html;
                $preview_html .= '</body></html>';
                ?>
                
                <hr>
                
                <!-- Live Preview Section -->
                <h2>Complete Page Preview</h2>
                <div style="border: 1px solid #ddd; padding: 20px; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                    <iframe id="live-preview-iframe" srcdoc="<?php echo htmlspecialchars($preview_html, ENT_QUOTES); ?>" style="width: 100%; height: 600px; border: 1px solid #eee;"></iframe>
                    <div style="margin-top: 15px; text-align: center;">
                        <button onclick="document.getElementById('live-preview-iframe').contentWindow.location.reload();" class="button button-secondary" style="margin-right: 10px;">
                            <span class="dashicons dashicons-update"></span> Refresh Preview
                        </button>
                        <button onclick="window.open('', 'fullscreenPreview', 'width='+screen.width+',height='+screen.height).document.write(document.getElementById('live-preview-iframe').srcdoc);" class="button button-secondary" style="margin-left: 10px;">
                            <span class="dashicons dashicons-editor-expand"></span> Fullscreen
                        </button>
                    </div>
                </div>

                <!-- HTML Breakdown Section -->
                <div style="margin-top: 30px;">
                    <h2>HTML Breakdown</h2>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div>
                            <h3>Header Content</h3>
                            <textarea readonly style="width: 100%; height: 200px; font-family: monospace;"><?php echo htmlspecialchars($header_html); ?></textarea>
                        </div>
                        <div>
                            <h3>Main Content</h3>
                            <textarea readonly style="width: 100%; height: 200px; font-family: monospace;"><?php echo htmlspecialchars($html_content); ?></textarea>
                        </div>
                        <div>
                            <h3>Footer Content</h3>
                            <textarea readonly style="width: 100%; height: 200px; font-family: monospace;"><?php echo htmlspecialchars($footer_html); ?></textarea>
                        </div>
                        <div>
                            <h3>CSS Styles</h3>
                            <textarea readonly style="width: 100%; height: 200px; font-family: monospace;"><?php echo htmlspecialchars($styles); ?></textarea>
                        </div>
                    </div>
                </div>
            <?php } ?>
        <?php endif; ?>
    </div>
    <?php
}