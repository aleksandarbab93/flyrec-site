<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Admin stranica "Flyrec Instagram Feed" — konekcija, sinhronizacija,
 * prikaz i napredna podešavanja. Koristi WP Settings API za čuvanje
 * "Prikaz"/"Sinhronizacija" polja (standardan POST na options.php), a AJAX
 * (Fig_Ajax) za akcije koje traže trenutnu povratnu informaciju bez reloada
 * (konekcija, ručni sync, refresh tokena, brisanje podataka).
 */
class Fig_Admin_Settings {

    const PAGE_SLUG = 'flyrec-instagram-feed';

    public static function init() {
        add_action( 'admin_menu', [ __CLASS__, 'register_menu' ] );
        add_action( 'admin_init', [ __CLASS__, 'register_settings' ] );
        add_action( 'admin_init', [ 'Fig_Sync', 'maybe_migrate_order_defaults' ] );
        add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_assets' ] );
    }

    /**
     * Podešavanja se dodaju kao submenu POD postojeći "Instagram Feed"
     * meni koji WordPress automatski pravi za flyrec_media CPT (umesto
     * zasebnog top-level menija) — tako sve živi na jednom mestu u sidebaru.
     */
    public static function register_menu() {
        add_submenu_page(
            'edit.php?post_type=' . Fig_CPT::POST_TYPE,
            __( 'Flyrec Instagram Feed', 'flyrec-instagram-feed' ),
            __( 'Podešavanja', 'flyrec-instagram-feed' ),
            'manage_options',
            self::PAGE_SLUG,
            [ __CLASS__, 'render_page' ]
        );
    }

    public static function register_settings() {
        register_setting( 'fig_settings_group', 'fig_settings', [
            'sanitize_callback' => [ __CLASS__, 'sanitize_settings' ],
        ] );
        register_setting( 'fig_settings_group', 'fig_app_id', [
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '',
        ] );
    }

    public static function sanitize_settings( $input ) {
        $allowed_types     = array_keys( Fig_Helpers::content_types() );
        $allowed_intervals = [ 'hourly', 'twicedaily', 'daily', 'manual' ];
        $allowed_clicks    = [ 'lightbox', 'instagram', 'embed' ];

        $content_types = isset( $input['content_types'] ) && is_array( $input['content_types'] )
            ? array_values( array_intersect( $allowed_types, $input['content_types'] ) )
            : [];
        if ( empty( $content_types ) ) {
            $content_types = $allowed_types;
        }

        return [
            'sync_interval'       => in_array( $input['sync_interval'] ?? '', $allowed_intervals, true ) ? $input['sync_interval'] : 'twicedaily',
            'items_limit'         => max( 1, min( 50, absint( $input['items_limit'] ?? 12 ) ) ),
            'content_types'       => $content_types,
            'show_caption'        => ! empty( $input['show_caption'] ),
            'show_date'           => ! empty( $input['show_date'] ),
            'show_views'          => ! empty( $input['show_views'] ),
            'click_action'        => in_array( $input['click_action'] ?? '', $allowed_clicks, true ) ? $input['click_action'] : 'lightbox',
            'columns'             => max( 1, min( 6, absint( $input['columns'] ?? 4 ) ) ),
            'delete_on_uninstall' => ! empty( $input['delete_on_uninstall'] ),
        ];
    }

    public static function enqueue_assets( $hook ) {
        // Hook naziv za submenu stranicu CPT-a: "{post_type}_page_{slug}".
        if ( Fig_CPT::POST_TYPE . '_page_' . self::PAGE_SLUG !== $hook ) {
            return;
        }

        wp_enqueue_style( 'fig-admin', FIG_URL . 'admin/css/admin.css', [], FIG_VERSION );
        wp_enqueue_script( 'fig-admin', FIG_URL . 'admin/js/admin.js', [ 'jquery' ], FIG_VERSION, true );

        wp_localize_script( 'fig-admin', 'figAdmin', [
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( Fig_Ajax::NONCE_ADMIN ),
            'i18n'    => [
                'confirmDisconnect'     => __( 'Sigurno želite da prekinete vezu sa Instagram nalogom?', 'flyrec-instagram-feed' ),
                'confirmClear'          => __( 'Sigurno želite da obrišete sve sinhronizovane objave? Ova akcija se ne može poništiti.', 'flyrec-instagram-feed' ),
                'working'               => __( 'Radim…', 'flyrec-instagram-feed' ),
                'enterToken'            => __( 'Unesite access token pre povezivanja.', 'flyrec-instagram-feed' ),
                'connectError'          => __( 'Greška pri povezivanju.', 'flyrec-instagram-feed' ),
                'networkErrorConnect'   => __( 'Mrežna greška pri povezivanju.', 'flyrec-instagram-feed' ),
                'networkErrorSync'      => __( 'Mrežna greška pri sinhronizaciji.', 'flyrec-instagram-feed' ),
                'networkErrorRefresh'   => __( 'Mrežna greška pri osvežavanju tokena.', 'flyrec-instagram-feed' ),
                'networkError'          => __( 'Mrežna greška.', 'flyrec-instagram-feed' ),
                'done'                  => __( 'Gotovo.', 'flyrec-instagram-feed' ),
            ],
        ] );
    }

    public static function render_page() {
        if ( ! Fig_Helpers::current_user_can_manage() ) {
            wp_die( esc_html__( 'Nemate dozvolu za pristup ovoj stranici.', 'flyrec-instagram-feed' ) );
        }

        $connected     = Fig_Token_Manager::has_token();
        $expired       = Fig_Token_Manager::is_expired();
        $days_left     = Fig_Token_Manager::days_until_expiry();
        $username      = Fig_Token_Manager::get_ig_username();
        $connected_at  = Fig_Token_Manager::get_connected_at();
        $last_sync     = Fig_Sync::get_last_sync_status();
        $next_sync     = Fig_Cron::next_sync_timestamp();
        $settings      = Fig_Helpers::get_settings();
        $app_id        = get_option( 'fig_app_id', '' );

        include FIG_DIR . 'admin/views/settings-page.php';
    }
}
