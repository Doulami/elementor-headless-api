<?php
/**
 * WooCommerce API Routes for Headless Elementor
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class EHA_WooCommerce_API_Routes {

    /**
     * Register WooCommerce-specific REST routes
     */
    public static function register_routes() {

        // Only proceed if WooCommerce is active:
        if ( ! class_exists( 'WooCommerce' ) ) {
            return;
        }

        register_rest_route( 
            'headless-elementor/v1',
            '/woo/products',
            [
                'methods'             => 'GET',
                'callback'            => [ __CLASS__, 'get_products' ],
                'permission_callback' => '__return_true', // or your custom permission
            ]
        );

        register_rest_route(
            'headless-elementor/v1',
            '/woo/product/(?P<id>\d+)',
            [
                'methods'             => 'GET',
                'callback'            => [ __CLASS__, 'get_single_product' ],
                'permission_callback' => '__return_true',
            ]
        );

        // Optionally, you might want a route that fetches product by slug:
        register_rest_route(
            'headless-elementor/v1',
            '/woo/product/(?P<slug>[a-zA-Z0-9-]+)',
            [
                'methods'             => 'GET',
                'callback'            => [ __CLASS__, 'get_single_product_by_slug' ],
                'permission_callback' => '__return_true',
            ]
        );
    }

    /**
     * Handle GET /woo/products
     * Returns a JSON list of products (ID, title, price, etc.)
     */
    public static function get_products( $request ) {
        $args = [
            'status' => 'publish',
            'limit'  => -1,  // or set a limit
        ];

        $products = wc_get_products( $args );
        $data     = [];

        foreach ( $products as $product ) {
            $data[] = [
                'id'          => $product->get_id(),
                'name'        => $product->get_name(),
                'price'       => $product->get_price(),
                'regular_price' => $product->get_regular_price(),
                'sale_price'  => $product->get_sale_price(),
                'slug'        => $product->get_slug(),
                'permalink'   => $product->get_permalink(),
                // etc. add what you need
            ];
        }

        return new WP_REST_Response( $data, 200 );
    }

    /**
     * Handle GET /woo/product/{id}
     * Returns either JSON data or rendered HTML for a single product
     */
    
    public static function get_single_product( $request ) {
        $id = $request->get_param('id');
        $render = $request->get_param('render');

        $product = wc_get_product( $id );

        if ( ! $product ) {
            return new WP_Error(
                'no_product',
                'Product not found',
                [ 'status' => 404 ]
            );
        }

        if ( $render ) {
            $renderer = new EHA_Renderer();

            if ( \Elementor\Plugin::$instance->db->is_built_with_elementor( $id ) ) {
                return $renderer->render_elementor_page( $id );
            }

            $template_id = EHA_Settings::get_product_template_id();
            if ( $template_id ) {
                return $renderer->render_product_with_template( $id, $template_id );
            }

            return new WP_Error('no_template', 'No Elementor template configured for product rendering.', ['status' => 404]);
        }

        $id = $request->get_param('id');
        $product = wc_get_product( $id );

        if ( ! $product ) {
            return new WP_Error( 
                'no_product', 
                'Product not found', 
                [ 'status' => 404 ] 
            );
        }

        // If you want JSON data only, return an array of product info
        $data = [
            'id'          => $product->get_id(),
            'name'        => $product->get_name(),
            'description' => $product->get_description(),
            'price'       => $product->get_price(),
            'slug'        => $product->get_slug(),
            // ...
        ];

        // Or, if you want to render with Elementor, you can do something similar to how
        // pages are rendered in EHA_Renderer:
        // $html = EHA_Renderer::render_product( $id );
        // $data['html'] = $html;

        return new WP_REST_Response( $data, 200 );
    }

    /**
     * Optionally handle GET /woo/product/{slug}
     * Just like the above, but by slug
     */
    
    public static function get_single_product_by_slug( $request ) {
        $slug = $request->get_param('slug');
        $render = $request->get_param('render');

        $args = [
            'status' => 'publish',
            'limit'  => 1,
            'slug'   => $slug,
        ];
        $products = wc_get_products( $args );

        if ( empty( $products ) ) {
            return new WP_Error(
                'no_product',
                'Product not found by slug',
                [ 'status' => 404 ]
            );
        }

        $product = $products[0];

        if ( $render ) {
            $renderer = new EHA_Renderer();

            if ( \Elementor\Plugin::$instance->db->is_built_with_elementor( $product->get_id() ) ) {
                return $renderer->render_elementor_page( $product->get_id() );
            }

            $template_id = EHA_Settings::get_product_template_id();
            if ( $template_id ) {
                return $renderer->render_product_with_template( $product->get_id(), $template_id );
            }

            return new WP_Error('no_template', 'No Elementor template configured for product rendering.', ['status' => 404]);
        }

        $slug = $request->get_param('slug');

        $args = [
            'status' => 'publish',
            'limit'  => 1,
            'slug'   => $slug,
        ];
        $products = wc_get_products( $args );

        if ( empty( $products ) ) {
            return new WP_Error(
                'no_product',
                'Product not found by slug',
                [ 'status' => 404 ]
            );
        }

        $product = $products[0];

        // Return JSON or rendered HTML as desired
        $data = [
            'id'          => $product->get_id(),
            'name'        => $product->get_name(),
            'description' => $product->get_description(),
            'price'       => $product->get_price(),
            'slug'        => $product->get_slug(),
        ];

        return new WP_REST_Response( $data, 200 );
    }

}
