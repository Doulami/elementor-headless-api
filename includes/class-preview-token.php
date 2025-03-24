<?php

class EHA_Preview_Tokens {

    public static function generate_token($post_id) {
        $key = self::get_secret_key();
        return hash_hmac('sha256', $post_id, $key);
    }

    public static function validate_token($post_id, $token) {
        if (! $token) return false;
        return hash_equals(self::generate_token($post_id), $token);
    }

    private static function get_secret_key() {
        $salt = defined('AUTH_SALT') ? AUTH_SALT : 'fallback_salt';
        return hash('sha256', $salt . '_eha_preview');
    }
}
