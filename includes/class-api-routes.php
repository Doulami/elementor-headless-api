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

    public function get_page_by_id($data) {
        return $this->format_response(get_post($data['id']));
    }

    public function get_page_by_slug($data) {
        $slug = $data['slug'];
        $post = get_page_by_path($slug, OBJECT, get_option('eha_allowed_post_types', ['page', 'post']));
        return $this->format_response($post);
    }

    public function get_all_pages($data) {
        $args = [
            'post_type'   => get_option('eha_allowed_post_types', ['page', 'post']),
            'post_status' => 'publish',
            'numberposts' => -1,
        ];
        $posts = get_posts($args);

        $result = array_map(function($post) {
            return [
                'id'    => $post->ID,
                'slug'  => $post->post_name,
                'title' => get_the_title($post),
            ];
        }, $posts);

        return rest_ensure_response($result);
    }

    public function get_status() {
        return [
            'plugin'            => 'Elementor Headless API',
            'version'           => '0.2',
            'elementor_version' => defined('ELEMENTOR_VERSION') ? ELEMENTOR_VERSION : 'unknown',
            'php_version'       => phpversion(),
            'allowed_post_types'=> get_option('eha_allowed_post_types', []),
        ];
    }

    private function format_response($post) {
        if (! $post) {
            return new WP_Error('not_found', 'Page not found', ['status' => 404]);
        }

        $fields = isset($_GET['fields']) ? explode(',', $_GET['fields']) : [];
        $format = isset($_GET['format']) ? $_GET['format'] : (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json') ? 'json' : 'html');

        $renderer = new EHA_Renderer();
        $html     = $renderer->render_elementor_page($post->ID);

        if ($format === 'json') {
            $response = [
                'id'    => $post->ID,
                'slug'  => $post->post_name,
                'title' => get_the_title($post),
                'html'  => $html,
            ];

            if (! empty($fields)) {
                $response = array_intersect_key($response, array_flip($fields));
            }

            return rest_ensure_response($response);
        }

        return $html;
    }
}
