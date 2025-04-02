<?php
/**
 * Template Suggestions API
 */

if (!defined('ABSPATH')) {
    exit;
}

class EHA_Template_Suggestions_API {

    public static function register_routes() {
        register_rest_route(
            'headless-elementor/v1',
            '/template-suggestions',
            [
                'methods'  => 'GET',
                'callback' => [__CLASS__, 'handle_request'],
                'permission_callback' => '__return_true',
            ]
        );
    }

    public static function handle_request($request) {
        $search = sanitize_text_field($request->get_param('search'));
        $post_types = get_post_types(['public' => true], 'names');

        $query_args = [
            'post_type'      => $post_types,
            'posts_per_page' => 20,
            's'              => $search,
            'post_status'    => 'publish',
        ];

        $query = new WP_Query($query_args);
        $results = [];

        foreach ($query->posts as $post) {
            $label = get_post_type_object($post->post_type)->labels->singular_name;
            $results[] = [
                'id'    => $post->ID,
                'label' => "[{$label}] " . $post->post_title,
            ];
        }

        return rest_ensure_response($results);
    }
}

// Hook into REST init
add_action('rest_api_init', ['EHA_Template_Suggestions_API', 'register_routes']);
