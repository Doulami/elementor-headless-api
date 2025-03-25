<?php

use Elementor\Plugin as ElementorPlugin;

class EHA_Renderer {

    public function render_elementor_page($post_id) {
        $cache_key     = 'eha_cached_html_' . $post_id;
        $bypass_cache  = isset($_GET['nocache']) && $_GET['nocache'] === '1' && EHA_Settings::allow_nocache();
        $debug         = isset($_GET['debug']) && $_GET['debug'] === '1' && EHA_Settings::debug_enabled();
        $as_json       = isset($_GET['format']) && strtolower($_GET['format']) === 'json' && EHA_Settings::json_enabled();
        $fields_param  = isset($_GET['fields']) ? explode(',', $_GET['fields']) : [];

        if (!$bypass_cache && EHA_Settings::cache_enabled()) {
            $cached = get_transient($cache_key);
            if ($cached) {
                if ($debug) {
                    $html .= '<!-- Elementor rendering active. Header/Footer not injected: ';
                    $html .= 'Header ID=' . ($header_id ?: 'none') . ', Footer ID=' . ($footer_id ?: 'none');
                    $html .= ' -->';
                }
                return $this->final_output($post_id, $cached, true, $debug, $as_json, $fields_param);
            }
        }

        $html = '';

        if (ElementorPlugin::$instance->documents->get($post_id)->is_built_with_elementor()) {

            if (EHA_Settings::include_global_styles()) {
                ob_start();
                ElementorPlugin::$instance->frontend->enqueue_styles();
                do_action('elementor/frontend/after_enqueue_styles');
                wp_print_styles(); // Captures styles
                $styles = ob_get_clean();
            } else {
                $styles = '';
            }

            $header_id = EHA_Settings::inject_header_id();
            $footer_id = EHA_Settings::inject_footer_id();

            $header_html = '';
            $footer_html = '';

            if (!empty($header_id) && is_numeric($header_id)) {
                $header_html = ElementorPlugin::instance()->frontend->get_builder_content_for_display($header_id);
            }

            if (!empty($footer_id) && is_numeric($footer_id)) {
                $footer_html = ElementorPlugin::instance()->frontend->get_builder_content_for_display($footer_id);
            }

            $html = ElementorPlugin::instance()->frontend->get_builder_content_for_display($post_id);
            $html = $styles . $header_html . $html . $footer_html;

        } else {
            $html = apply_filters('the_content', get_post_field('post_content', $post_id));
        }

        if (EHA_Settings::strip_wp_noise()) {
            $html = $this->clean_html($html);
        }

        set_transient($cache_key, $html, 12 * HOUR_IN_SECONDS);

        if ($debug) {
            $html .= '<!-- Elementor rendering active. Header/Footer not injected: ';
            $html .= 'Header ID=' . ($header_id ?: 'none') . ', Footer ID=' . ($footer_id ?: 'none');
            $html .= ' -->';
        }
        return $this->final_output($post_id, $html, false, $debug, $as_json, $fields_param);
    }

    private function final_output($post_id, $html, $cached, $debug, $as_json, $fields) {
        if (!$as_json) return $html;

        $post = get_post($post_id);
        $data = [];

        $include = empty($fields) ? ['id','title','slug','html'] : $fields;

        if (in_array('id', $include))     $data['id']    = $post->ID;
        if (in_array('title', $include))  $data['title'] = get_the_title($post);
        if (in_array('slug', $include))   $data['slug']  = $post->post_name;
        if (in_array('type', $include))   $data['type']  = $post->post_type;
        if (in_array('html', $include))   $data['html']  = $html;

        if (in_array('acf', $include) && function_exists('get_fields')) {
            $acf = get_fields($post_id);
            if ($acf) $data['acf'] = $acf;
        }

        if (in_array('meta', $include)) {
            $meta = get_post_meta($post_id);
            if ($meta) $data['meta'] = $meta;
        }

        if (in_array('terms', $include)) {
            $taxes = get_object_taxonomies($post, 'names');
            $terms = [];
            foreach ($taxes as $taxonomy) {
                $t = wp_get_post_terms($post_id, $taxonomy, ['fields' => 'slugs']);
                if (!empty($t)) $terms[$taxonomy] = $t;
            }
            $data['terms'] = $terms;
        }

        if ($debug) {
            $data['debug'] = [
                'cache_used'        => $cached,
                'elementor_version' => defined('ELEMENTOR_VERSION') ? ELEMENTOR_VERSION : 'unknown',
                'rendered_at'       => current_time('mysql'),
                'template'          => get_page_template_slug($post_id) ?: 'default',
                'header_footer_missing' => (!EHA_Settings::inject_header_id() && !EHA_Settings::inject_footer_id() && get_page_template_slug($post_id) !== 'elementor_canvas')
            ];
        }

        return rest_ensure_response($data);
    }

    private function clean_html($html) {
        $html = preg_replace('/<!--(.|\s)*?-->/', '', $html);
        $html = preg_replace('/<script[^>]*admin-bar[^>]*>.*?<\\/script>/is', '', $html);
        $html = preg_replace('/<div id="wpadminbar".*?<\\/div>/is', '', $html);
        return $html;
    }
    
    /**
     * Render a product using a fallback Elementor template.
     *
     * @param int $product_id
     * @param int $template_id
     * @return string Rendered HTML
     */
    public function render_product_with_template($product_id, $template_id) {
        if (!function_exists('elementor_theme_do_location')) {
            return new WP_Error('elementor_not_available', 'Elementor is not active.', ['status' => 500]);
        }

        // Set global $product context for shortcodes/widgets to work
        global $product;
        $product = wc_get_product($product_id);

        // Capture rendered HTML of template
        $html = \Elementor\Plugin::$instance->frontend->get_builder_content_for_display($template_id);

        return new WP_REST_Response($html, 200);
    }

}

