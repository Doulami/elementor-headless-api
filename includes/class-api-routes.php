<?php

class EHA_Api_Routes {

    public function __construct() {
        add_action('rest_api_init', [ $this, 'register_routes' ]);
    }

    public function register_routes() {
        register_rest_route('headless-elementor/v1', '/page/(?P<id>\d+)', [
            'methods' => 'GET',
            'callback' => [$this, 'serve_page_by_id'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route('headless-elementor/v1', '/slug/(?P<slug>[a-zA-Z0-9-_]+)', [
            'methods' => 'GET',
            'callback' => [$this, 'serve_page_by_slug'],
            'permission_callback' => '__return_true',
        ]);
    }

    public function serve_page_by_id($request) {
        $post_id = absint($request['id']);
        return $this->render_response($post_id);
    }

    public function serve_page_by_slug($request) {
        $slug = sanitize_title($request['slug']);
        $post = get_page_by_path($slug, OBJECT, get_post_types(['public' => true], 'names'));

        if (!$post) {
            return new WP_REST_Response(['error' => 'Page not found.'], 404);
        }

        return $this->render_response($post->ID);
    }

    private function render_response($post_id) {
        $post_type = get_post_type($post_id);
        $status = get_post_status($post_id);

        if ($status !== 'publish') {
            $token = $_GET['token'] ?? '';
            if (!EHA_Preview_Tokens::validate_token($post_id, $token)) {
                return new WP_REST_Response(['error' => 'Unauthorized preview.'], 401);
            }
        }

        if (! $this->is_allowed_post_type($post_type)) {
            return new WP_REST_Response(['error' => 'Post type not allowed.'], 403);
        }

        $renderer = new EHA_Renderer();
        $html = $renderer->render_elementor_page($post_id);

        return new WP_REST_Response([
            'id'    => $post_id,
            'slug'  => get_post_field('post_name', $post_id),
            'title' => get_the_title($post_id),
            'html'  => $html,
        ]);
    }

    private function is_allowed_post_type($post_type) {
        $allowed = get_option('eha_allowed_post_types', ['post', 'page']);
        return in_array($post_type, $allowed);
    }
}
