<?php

class EHA_Renderer {

    public function render_elementor_page( $post_id ) {
        if ( ! Elementor\\Plugin::$instance->documents->get( $post_id )->is_built_with_elementor() ) {
            return '<!-- Not an Elementor page -->';
        }

        ob_start();
        echo Elementor\\Plugin::instance()->frontend->get_builder_content_for_display( $post_id );
        $html = ob_get_clean();

        // Future: clean scripts, add placeholder replacements, etc.
        return apply_filters( 'eha_rendered_html', $html, $post_id );
    }
}
