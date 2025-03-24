<?php

class EHA_Preview_Tokens {

    public function __construct() {
        add_action('add_meta_boxes', [$this, 'add_token_meta_box']);
        add_action('save_post', [$this, 'save_preview_token']);
    }

    public function add_token_meta_box() {
        $post_types = get_option('eha_allowed_post_types', ['post', 'page']);

        foreach ($post_types as $type) {
            add_meta_box(
                'eha_preview_token_box',
                'Headless Preview Token',
                [$this, 'render_meta_box'],
                $type,
                'side'
            );
        }
    }

    public function render_meta_box($post) {
        $token = get_post_meta($post->ID, '_eha_preview_token', true);
        if (!$token) {
            $token = bin2hex(random_bytes(12));
            echo '<p><strong>Preview Token:</strong> Will be generated on update.</p>';
        } else {
            echo "<p><strong>Preview Token:</strong><br><code>{$token}</code></p>";
        }
        echo '<input type="hidden" name="eha_preview_token_nonce" value="' . wp_create_nonce('eha_preview_token_save') . '">';
    }

    public function save_preview_token($post_id) {
        if (
            defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ||
            wp_is_post_autosave($post_id) ||
            wp_is_post_revision($post_id)
        ) return;

        if (
            !isset($_POST['eha_preview_token_nonce']) ||
            !wp_verify_nonce($_POST['eha_preview_token_nonce'], 'eha_preview_token_save')
        ) return;

        $existing = get_post_meta($post_id, '_eha_preview_token', true);
        if (!$existing) {
            $token = bin2hex(random_bytes(12));
            update_post_meta($post_id, '_eha_preview_token', $token);
        }
    }

    public static function validate_token($post_id, $token) {
        $stored = get_post_meta($post_id, '_eha_preview_token', true);
        return $token && $stored && hash_equals($stored, $token);
    }
}
