<?php

class EHA_API_Routes {

    public function __construct() {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    public function register_routes() {
        register_rest_route('headless-elementor/v1', '/page/(?P<id>\\d+)', [
            'methods'  => 'GET',
            'callback' => [$this, 'get_page_by_id'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route('headless-elementor/v1', '/slug/(?P<slug>[a-zA-Z0-9-_]+)', [
            'methods'  => 'GET',
            'callback' => [$this, 'get_page_by_slug'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route('headless-elementor/v1', '/pages', [
            'methods'  => 'GET',
            'callback' => [$this, 'get_all_pages'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route('headless-elementor/v1', '/status', [
            'methods'  => 'GET',
            'callback' => [$this, 'get_status'],
            'permission_callback' => '__return_true',
        ]);
    }

    public function get_page_by_id($request) {
        $post_id = intval($request['id']);
        $post    = get_post($post_id);

        if (! $post || !EHA_Settings::post_type_allowed($post->post_type)) {
            return new WP_Error('not_allowed', 'Post type not allowed or post not found.', ['status' => 404]);
        }

        $renderer = new EHA_Renderer();
        return $renderer->render_elementor_page($post_id);
    }

    public function get_page_by_slug($request) {
        $slug = sanitize_title($request['slug']);

        $query = new WP_Query([
            'name'           => $slug,
            'post_type'      => EHA_Settings::get_allowed_post_types(),
            'posts_per_page' => 1,
            'post_status'    => 'publish',
        ]);

        if (! $query->have_posts()) {
            return new WP_Error('not_found', 'Page not found or not allowed.', ['status' => 404]);
        }

        $post = $query->posts[0];
        $renderer = new EHA_Renderer();
        return $renderer->render_elementor_page($post->ID);
    }

    public function get_all_pages($request) {
        $posts = get_posts([
            'post_type'      => EHA_Settings::get_allowed_post_types(),
            'post_status'    => 'publish',
            'posts_per_page' => -1,
        ]);

        $result = [];

        foreach ($posts as $post) {
            $result[] = [
                'id'    => $post->ID,
                'title' => get_the_title($post),
                'slug'  => $post->post_name,
                'type'  => $post->post_type,
                'link'  => get_permalink($post),
            ];
        }

        return $result;
    }

    public function get_status($request) {
        return [
            'status'   => 'ok',
            'time'     => current_time('mysql'),
            'version'  => ELEMENTOR_VERSION,
            'allowed_post_types' => EHA_Settings::get_allowed_post_types(),
        ];
    }
}
