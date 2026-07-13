<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Bezbedno čuvanje i upravljanje Instagram access tokenom.
 *
 * Token se čuva enkriptovan (AES-256-CBC) u wp_options (autoload=no), koristeći
 * WordPress AUTH_KEY/AUTH_SALT iz wp-config.php kao ključ — token nikad nije
 * čitljiv u bazi kao plain text i nikad se ne ispisuje u HTML na frontend-u.
 */
class Fig_Token_Manager {

    const OPT_TOKEN        = 'fig_access_token_enc';
    const OPT_EXPIRES      = 'fig_token_expires_at';
    const OPT_IG_USER_ID   = 'fig_ig_user_id';
    const OPT_IG_USERNAME  = 'fig_ig_username';
    const OPT_CONNECTED_AT = 'fig_connected_at';

    /** Izvodi 32-bajtni ključ za enkripciju iz WP salt konstanti. */
    private static function encryption_key() {
        $material = ( defined( 'AUTH_KEY' ) ? AUTH_KEY : '' ) . ( defined( 'AUTH_SALT' ) ? AUTH_SALT : '' );
        if ( '' === $material ) {
            // Fallback ako salt konstante nisu definisane (retko) — i dalje
            // jedinstveno po sajtu, izvedeno iz baze prefiksa i site URL-a.
            $material = DB_NAME . site_url();
        }
        return hash( 'sha256', $material, true );
    }

    public static function save_token( $plain_token, $expires_in_seconds = null ) {
        if ( empty( $plain_token ) ) {
            return false;
        }

        $iv        = openssl_random_pseudo_bytes( 16 );
        $encrypted = openssl_encrypt( $plain_token, 'aes-256-cbc', self::encryption_key(), 0, $iv );
        if ( false === $encrypted ) {
            return false;
        }

        update_option( self::OPT_TOKEN, base64_encode( $iv . $encrypted ), false );

        if ( $expires_in_seconds ) {
            update_option( self::OPT_EXPIRES, time() + (int) $expires_in_seconds, false );
        }

        return true;
    }

    public static function get_token() {
        $stored = get_option( self::OPT_TOKEN, '' );
        if ( '' === $stored ) {
            return '';
        }

        $raw = base64_decode( $stored, true );
        if ( false === $raw || strlen( $raw ) < 17 ) {
            return '';
        }

        $iv        = substr( $raw, 0, 16 );
        $encrypted = substr( $raw, 16 );
        $plain     = openssl_decrypt( $encrypted, 'aes-256-cbc', self::encryption_key(), 0, $iv );

        return false === $plain ? '' : $plain;
    }

    public static function has_token() {
        return '' !== self::get_token();
    }

    /** Maskirani prikaz tokena za admin ekran (nikad pun token). */
    public static function masked_token() {
        $token = self::get_token();
        if ( '' === $token ) {
            return '';
        }
        $len = strlen( $token );
        return str_repeat( '•', min( 24, max( 8, $len - 6 ) ) ) . substr( $token, -6 );
    }

    public static function get_expires_at() {
        return (int) get_option( self::OPT_EXPIRES, 0 );
    }

    public static function days_until_expiry() {
        $expires = self::get_expires_at();
        if ( ! $expires ) {
            return null;
        }
        return (int) floor( ( $expires - time() ) / DAY_IN_SECONDS );
    }

    public static function is_expired() {
        $expires = self::get_expires_at();
        return $expires && $expires < time();
    }

    public static function needs_refresh_soon() {
        $days = self::days_until_expiry();
        return null !== $days && $days <= FIG_TOKEN_REFRESH_THRESHOLD_DAYS;
    }

    public static function set_account_info( $ig_user_id, $ig_username ) {
        update_option( self::OPT_IG_USER_ID, sanitize_text_field( $ig_user_id ), false );
        update_option( self::OPT_IG_USERNAME, sanitize_text_field( $ig_username ), false );
        update_option( self::OPT_CONNECTED_AT, time(), false );
    }

    public static function get_ig_user_id() {
        return get_option( self::OPT_IG_USER_ID, '' );
    }

    public static function get_ig_username() {
        return get_option( self::OPT_IG_USERNAME, '' );
    }

    public static function get_connected_at() {
        return (int) get_option( self::OPT_CONNECTED_AT, 0 );
    }

    public static function disconnect() {
        delete_option( self::OPT_TOKEN );
        delete_option( self::OPT_EXPIRES );
        delete_option( self::OPT_IG_USER_ID );
        delete_option( self::OPT_IG_USERNAME );
        delete_option( self::OPT_CONNECTED_AT );
    }
}
