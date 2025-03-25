<?php

class EHA_Preview_Tokens {

    public function __construct() {
        add_action('add_meta_boxes', [$this, 'add_token_meta_box']);
        add_action('save_post', [$this, 'save_preview_token']);
    }

    public function add_token_meta_box() {
        $post_types = EHA_Settings::get_allowed_post_types();

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
        $generated = get_post_meta($post->ID, '_eha_token_generated_at', true);

        if (!$token) {
            $token = bin2hex(random_bytes(12));
            echo '<p><strong>Preview Token:</strong> Will be generated on update.</p>';
        } else {
            echo "<p><strong>Preview Token:</strong><br><code>{$token}</code></p>";
            echo "<p><em>Use ?token={$token} to access this post via API.</em></p>";

            if ($generated) {
                $expiry = EHA_Settings::token_expiry_hours();
                $expires_at = strtotime($generated) + ($expiry * HOUR_IN_SECONDS);
                $expires_str = date('Y-m-d H:i:s', $expires_at);
                echo "<p><small>Generated at: {$generated}<br>Expires at: {$expires_str}</small></p>";
            }
        }

        echo "<input type='hidden' name='eha_preview_token' value='{$token}' />";
        echo "<input type='hidden' name='eha_token_generated_at' value='" . current_time('mysql') . "' />";
    }

    public function save_preview_token($post_id) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

        if (isset($_POST['eha_preview_token'])) {
            update_post_meta($post_id, '_eha_preview_token', sanitize_text_field($_POST['eha_preview_token']));
            update_post_meta($post_id, '_eha_token_generated_at', sanitize_text_field($_POST['eha_token_generated_at']));
        }
    }

    public static function validate_token($post_id, $token) {
        if (!EHA_Settings::tokens_enabled()) return false;

        $stored = get_post_meta($post_id, '_eha_preview_token', true);
        if (!$stored || $token !== $stored) return false;

        // Check expiry
        $generated = get_post_meta($post_id, '_eha_token_generated_at', true);
        if ($generated) {
            $expiry_hours = EHA_Settings::token_expiry_hours();
            $expires_at = strtotime($generated) + ($expiry_hours * HOUR_IN_SECONDS);
            if (time() > $expires_at) return false;
        }

        // Restrict to private posts if enabled
        if (EHA_Settings::tokens_private_only()) {
            $post = get_post($post_id);
            if ($post->post_status !== 'private') return false;
        }

        return true;
    }
}
