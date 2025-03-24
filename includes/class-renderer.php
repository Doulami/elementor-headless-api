<?php

class EHA_Renderer {

    public function render_elementor_page($post_id) {
        $cache_key = 'eha_cached_html_' . $post_id;

        // 1. Try to get from cache
        $cached = get_transient($cache_key);
        if ($cached) {
            return $cached;
        }

        // 2. Ensure Elementor is active before rendering
        if ( ! class_exists( 'Elementor\\Plugin' ) ) {
            return '<!-- Elementor not available -->';
        }

        $document = Elementor\\Plugin::$instance->documents->get( $post_id );
        if ( ! $document || ! $document->is_built_with_elementor() ) {
            return '<!-- Not an Elementor page -->';
        }

        ob_start();
        echo Elementor\\Plugin::instance()->frontend->get_builder_content_for_display( $post_id );
        $html = ob_get_clean();

        // 3. Cache it for 12 hours
        set_transient( $cache_key, $html, 12 * HOUR_IN_SECONDS );

        return apply_filters( 'eha_rendered_html', $html, $post_id );
    }

}
