<?php

class EHA_Api_Routes {

    public function __construct() {
        add_action( 'rest_api_init', [ $this, 'register_routes' ] );
    }

    public function register_routes() {
        register_rest_route( 'headless-elementor/v1', '/page/(?P<id>\\d+)', [
            'methods'  => 'GET',
            'callback' => [ $this, 'serve_page' ],
            'permission_callback' => '__return_true',
        ]);
    }

    public function serve_page( $request ) {
        $post_id = absint( $request['id'] );

        if ( ! $post_id || get_post_status( $post_id ) !== 'publish' ) {
            return new WP_REST_Response( [ 'error' => 'Page not found.' ], 404 );
        }

        require_once EHA_PATH . 'includes/class-renderer.php';
        $renderer = new EHA_Renderer();
        $html = $renderer->render_elementor_page( $post_id );

        return new WP_REST_Response( [
            'id'    => $post_id,
            'slug'  => get_post_field( 'post_name', $post_id ),
            'title' => get_the_title( $post_id ),
            'html'  => $html,
        ] );
    }
}