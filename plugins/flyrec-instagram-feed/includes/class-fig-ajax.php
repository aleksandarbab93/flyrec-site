<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Svi AJAX endpointi plugina — admin akcije (poveži nalog, sync sada,
 * refresh tokena, prekini vezu, obriši podatke) i javni frontend endpointi
 * (učitaj još, lazy oEmbed za lightbox).
 *
 * Svaki handler: proverava nonce, proverava capability (za admin akcije),
 * sanitizuje ulaz, i vraća wp_send_json_success/error — nikad ne dozvoljava
 * da API greška obori zahtev.
 */
class Fig_Ajax {

    const NONCE_ADMIN  = 'fig_admin_nonce';
    const NONCE_PUBLIC = 'fig_public_nonce';

    public static function init() {
        // Admin-only akcije.
        add_action( 'wp_ajax_fig_connect_token', [ __CLASS__, 'connect_token' ] );
        add_action( 'wp_ajax_fig_manual_sync',   [ __CLASS__, 'manual_sync' ] );
        add_action( 'wp_ajax_fig_refresh_token', [ __CLASS__, 'refresh_token' ] );
        add_action( 'wp_ajax_fig_disconnect',    [ __CLASS__, 'disconnect' ] );
        add_action( 'wp_ajax_fig_clear_data',    [ __CLASS__, 'clear_data' ] );
        add_action( 'wp_ajax_fig_reorder_media', [ __CLASS__, 'reorder_media' ] );

        // Javni frontend endpointi (rade i za ulogovane i za goste).
        add_action( 'wp_ajax_fig_load_more',        [ __CLASS__, 'load_more' ] );
        add_action( 'wp_ajax_nopriv_fig_load_more', [ __CLASS__, 'load_more' ] );
        add_action( 'wp_ajax_fig_get_embed',        [ __CLASS__, 'get_embed' ] );
        add_action( 'wp_ajax_nopriv_fig_get_embed', [ __CLASS__, 'get_embed' ] );
    }

    // =============================================
    // ADMIN AKCIJE
    // =============================================

    private static function guard_admin() {
        check_ajax_referer( self::NONCE_ADMIN, 'nonce' );
        if ( ! Fig_Helpers::current_user_can_manage() ) {
            wp_send_json_error( [ 'message' => __( 'Nemate dozvolu za ovu akciju.', 'flyrec-instagram-feed' ) ], 403 );
        }
    }

    public static function connect_token() {
        self::guard_admin();

        $token      = isset( $_POST['token'] ) ? sanitize_text_field( wp_unslash( $_POST['token'] ) ) : '';
        $app_secret = isset( $_POST['app_secret'] ) ? sanitize_text_field( wp_unslash( $_POST['app_secret'] ) ) : '';

        if ( '' === $token ) {
            wp_send_json_error( [ 'message' => __( 'Unesite access token.', 'flyrec-instagram-feed' ) ] );
        }

        // Ako je unet App Secret, prvo pokušaj exchange short-lived → long-lived.
        $client = new Fig_Api_Client( $token );

        if ( '' !== $app_secret ) {
            $exchanged = $client->exchange_for_long_lived_token( $token, $app_secret );
            if ( ! is_wp_error( $exchanged ) && ! empty( $exchanged['access_token'] ) ) {
                $token  = $exchanged['access_token'];
                $client = new Fig_Api_Client( $token );
                update_option( 'fig_app_secret_enc', self::simple_encode( $app_secret ), false );
            }
        }

        $account = $client->validate_and_fetch_account();
        if ( is_wp_error( $account ) ) {
            wp_send_json_error( [ 'message' => $account->get_error_message() ] );
        }

        // Pretpostavljamo long-lived token (60 dana) ako exchange nije rađen.
        Fig_Token_Manager::save_token( $token, 60 * DAY_IN_SECONDS );
        Fig_Token_Manager::set_account_info( $account['id'], $account['username'] );

        $sync = Fig_Sync::run();

        wp_send_json_success( [
            'message'  => sprintf(
                /* translators: %s: Instagram username */
                __( 'Nalog @%s uspešno povezan.', 'flyrec-instagram-feed' ),
                $account['username']
            ),
            'username' => $account['username'],
            'sync'     => $sync,
        ] );
    }

    public static function manual_sync() {
        self::guard_admin();
        $result = Fig_Sync::run();
        if ( $result['success'] ) {
            wp_send_json_success( $result );
        }
        wp_send_json_error( $result );
    }

    public static function refresh_token() {
        self::guard_admin();

        $client = new Fig_Api_Client();
        $result = $client->refresh_long_lived_token();

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( [ 'message' => $result->get_error_message() ] );
        }

        Fig_Token_Manager::save_token( $result['access_token'], $result['expires_in'] ?? ( 60 * DAY_IN_SECONDS ) );

        wp_send_json_success( [ 'message' => __( 'Token je uspešno osvežen.', 'flyrec-instagram-feed' ) ] );
    }

    public static function disconnect() {
        self::guard_admin();
        Fig_Token_Manager::disconnect();
        wp_send_json_success( [ 'message' => __( 'Veza sa Instagram nalogom je prekinuta. Sinhronizovane objave su zadržane.', 'flyrec-instagram-feed' ) ] );
    }

    public static function clear_data() {
        self::guard_admin();
        $count = Fig_Sync::clear_synced_data();
        wp_send_json_success( [
            'message' => sprintf(
                /* translators: %d: number of deleted posts */
                __( 'Obrisano %d sinhronizovanih objava. Pokreni sinhronizaciju ponovo kad želiš.', 'flyrec-instagram-feed' ),
                $count
            ),
        ] );
    }

    /**
     * Čuva novi redosled objava posle drag & drop u admin listi.
     * Prima niz post ID-jeva u željenom redosledu, dodeljuje sekvencijalne
     * _fig_order vrednosti (1, 2, 3...) tim tačno tim ID-jevima.
     */
    public static function reorder_media() {
        self::guard_admin();

        $order = isset( $_POST['order'] ) && is_array( $_POST['order'] ) ? array_map( 'absint', $_POST['order'] ) : [];

        if ( empty( $order ) ) {
            wp_send_json_error( [ 'message' => __( 'Nema podataka za čuvanje redosleda.', 'flyrec-instagram-feed' ) ] );
        }

        $position = 1;
        foreach ( $order as $post_id ) {
            if ( Fig_CPT::POST_TYPE !== get_post_type( $post_id ) ) {
                continue;
            }
            update_post_meta( $post_id, '_fig_order', $position );
            $position++;
        }

        wp_send_json_success( [ 'message' => __( 'Redosled sačuvan.', 'flyrec-instagram-feed' ) ] );
    }

    /** Vrlo jednostavno (reverzibilno) enkodovanje App Secret-a — isti mehanizam kao token. */
    private static function simple_encode( $value ) {
        $iv        = openssl_random_pseudo_bytes( 16 );
        $key       = hash( 'sha256', ( defined( 'AUTH_KEY' ) ? AUTH_KEY : DB_NAME ), true );
        $encrypted = openssl_encrypt( $value, 'aes-256-cbc', $key, 0, $iv );
        return base64_encode( $iv . $encrypted );
    }

    // =============================================
    // JAVNI FRONTEND ENDPOINTI
    // =============================================

    public static function load_more() {
        check_ajax_referer( self::NONCE_PUBLIC, 'nonce' );

        $offset       = isset( $_POST['offset'] ) ? absint( $_POST['offset'] ) : 0;
        $limit        = isset( $_POST['limit'] ) ? absint( $_POST['limit'] ) : 12;
        $columns      = isset( $_POST['columns'] ) ? absint( $_POST['columns'] ) : 4;
        $type         = isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : 'all';
        $click_action = isset( $_POST['click_action'] ) ? sanitize_text_field( wp_unslash( $_POST['click_action'] ) ) : 'lightbox';

        $items = Fig_Shortcode::query_items( $limit, $offset, $type );
        $html  = '';
        foreach ( $items['posts'] as $post ) {
            $html .= Fig_Shortcode::render_card( $post, $click_action );
        }

        wp_send_json_success( [
            'html'     => $html,
            'has_more' => $items['has_more'],
        ] );
    }

    public static function get_embed() {
        check_ajax_referer( self::NONCE_PUBLIC, 'nonce' );

        $post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
        if ( ! $post_id || Fig_CPT::POST_TYPE !== get_post_type( $post_id ) ) {
            wp_send_json_error( [ 'message' => __( 'Objava nije pronađena.', 'flyrec-instagram-feed' ) ] );
        }

        $permalink = get_post_meta( $post_id, '_fig_permalink', true );

        $settings   = Fig_Helpers::get_settings();
        $app_id     = get_option( 'fig_app_id', '' );
        $app_secret = get_option( 'fig_app_secret_enc', '' ) ? self::simple_decode( get_option( 'fig_app_secret_enc', '' ) ) : '';

        if ( ! $permalink ) {
            wp_send_json_error( [ 'message' => __( 'Nema linka ka Instagram objavi.', 'flyrec-instagram-feed' ) ] );
        }

        $client = new Fig_Api_Client();
        $embed  = $client->get_oembed( $permalink, $app_id, $app_secret );

        if ( is_wp_error( $embed ) || empty( $embed['html'] ) ) {
            // Fallback signal ka frontend JS-u da prikaže samo link ka Instagramu.
            wp_send_json_error( [
                'message'   => __( 'Embed trenutno nije dostupan.', 'flyrec-instagram-feed' ),
                'permalink' => esc_url( $permalink ),
            ] );
        }

        wp_send_json_success( [ 'html' => $embed['html'] ] );
    }

    private static function simple_decode( $value ) {
        $raw = base64_decode( $value, true );
        if ( false === $raw || strlen( $raw ) < 17 ) {
            return '';
        }
        $iv  = substr( $raw, 0, 16 );
        $enc = substr( $raw, 16 );
        $key = hash( 'sha256', ( defined( 'AUTH_KEY' ) ? AUTH_KEY : DB_NAME ), true );
        $out = openssl_decrypt( $enc, 'aes-256-cbc', $key, 0, $iv );
        return false === $out ? '' : $out;
    }
}
