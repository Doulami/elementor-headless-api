<?php

class EHA_Renderer {

    public function render_elementor_page($post_id) {
        $cache_key = 'eha_cached_html_' . $post_id;

        $bypass_cache = isset($_GET['nocache']) && $_GET['nocache'] === '1';
        $debug        = isset($_GET['debug']) && $_GET['debug'] === '1';

        if (! $bypass_cache) {
            $cached = get_transient($cache_key);
            if ($cached) {
                return $this->wrap_debug($cached, $post_id, true, $debug);
            }
        }

        $html = '';

        if (\Elementor\Plugin::$instance->documents->get($post_id)->is_built_with_elementor()) {
            // Inject Header/Footer templates
            $header_id = get_option('eha_header_template_id');
            $footer_id = get_option('eha_footer_template_id');

            $header_html = $header_id ? \Elementor\Plugin::instance()->frontend->get_builder_content_for_display($header_id) : '';
            $footer_html = $footer_id ? \Elementor\Plugin::instance()->frontend->get_builder_content_for_display($footer_id) : '';

            ob_start();
            echo \Elementor\Plugin::instance()->frontend->get_builder_content_for_display($post_id);
            $main_html = ob_get_clean();

            $html = "<!-- EHA:HEADER -->\n" . $header_html .
                    "\n<!-- EHA:MAIN -->\n" . $main_html .
                    "\n<!-- EHA:FOOTER -->\n" . $footer_html;
        } else {
            $html = apply_filters('the_content', get_post_field('post_content', $post_id));
        }

        $html = $this->sanitize_output($html);
        set_transient($cache_key, $html, 12 * HOUR_IN_SECONDS);

        return $this->wrap_debug($html, $post_id, false, $debug);
    }

    private function sanitize_output($html) {
        $html = preg_replace('/<\?php.*?\?>/s', '', $html);
        return trim($html);
    }

    private function wrap_debug($html, $post_id, $cache_used, $debug) {
        if (! $debug) return $html;

        $info = sprintf(
            "\n<!-- Debug Info: Elementor %s | Cache Used: %s | Rendered at: %s -->",
            defined('ELEMENTOR_VERSION') ? ELEMENTOR_VERSION : 'unknown',
            $cache_used ? 'true' : 'false',
            current_time('mysql')
        );

        return $html . $info;
    }
}
