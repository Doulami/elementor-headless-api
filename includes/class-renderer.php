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
                return $this->final_output($post_id, $cached, true, $debug, $as_json, $fields_param);
            }
        }

        $html = '';

        if (ElementorPlugin::$instance->documents->get($post_id)->is_built_with_elementor()) {
            ob_start();

            // Inject Header
            $header_id = EHA_Settings::inject_header_id();
            if ($header_id) {
                echo ElementorPlugin::instance()->frontend->get_builder_content_for_display($header_id);
            }

            // Main Content
            echo ElementorPlugin::instance()->frontend->get_builder_content_for_display($post_id, true);

            // Inject Footer
            $footer_id = EHA_Settings::inject_footer_id();
            if ($footer_id) {
                echo ElementorPlugin::instance()->frontend->get_builder_content_for_display($footer_id);
            }

            $html = ob_get_clean();

            // Add global styles if enabled
            if (EHA_Settings::include_global_styles()) {
                $html = $this->inject_global_styles($html);
            }

            // Strip WP head/footer noise if enabled
            if (EHA_Settings::strip_wp_noise()) {
                $html = $this->clean_html_output($html);
            }
        }

        if (EHA_Settings::cache_enabled()) {
            set_transient($cache_key, $html, EHA_Settings::get_cache_ttl());
        }

        return $this->final_output($post_id, $html, false, $debug, $as_json, $fields_param);
    }

    protected function final_output($post_id, $html, $cache_used = false, $debug = false, $as_json = false, $fields = []) {
        if ($as_json) {
            $response = [
                'id'    => $post_id,
                'html'  => in_array('html', $fields) || empty($fields) ? $html : null,
                'debug' => $debug ? [
                    'cache_used'        => $cache_used,
                    'elementor_version' => \Elementor\Plugin::instance()->get_version(),
                    'rendered_at'       => current_time('mysql')
                ] : null
            ];

            if (EHA_Settings::include_meta()) {
                $response['meta'] = get_post_meta($post_id);
            }

            if (EHA_Settings::include_terms()) {
                $response['terms'] = wp_get_post_terms($post_id, get_object_taxonomies(get_post_type($post_id)));
            }

            if (EHA_Settings::include_acf() && function_exists('get_fields')) {
                $response['acf'] = get_fields($post_id);
            }

            return $response;
        }

        if ($debug) {
            $debug_block = sprintf(
                "<!-- Debug: Elementor v%s | Rendered at %s | Cache used: %s -->",
                \Elementor\Plugin::instance()->get_version(),
                current_time('mysql'),
                $cache_used ? 'true' : 'false'
            );
            $html .= $debug_block;
        }

        return $html;
    }

    protected function clean_html_output($html) {
        // TODO: Implement stripping of <head>, admin bars, etc.
        return $html;
    }

    protected function inject_global_styles($html) {
        ob_start();
        \Elementor\Plugin::instance()->frontend->enqueue_styles();
        return $html . ob_get_clean();
    }
}
