<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * WP-Cron zakazivanje: periodična sinhronizacija objava + periodičan
 * refresh access tokena (nezavisno od izabranog intervala sinhronizacije,
 * da token nikad ne istekne "u tišini").
 *
 * Napomena: WP-Cron se pokreće na posetu sajta, pa na sajtovima sa vrlo
 * malim saobraćajem intervali mogu kasniti. Za potpuno pouzdano izvršavanje
 * u tačno zakazano vreme, preporuka u dokumentaciji je da se doda pravi
 * serverski cron koji poziva wp-cron.php (uobičajeno kod hosting panela).
 */
class Fig_Cron {

    const SYNC_HOOK          = 'fig_sync_event';
    const TOKEN_REFRESH_HOOK = 'fig_token_refresh_event';

    public static function init() {
        add_filter( 'cron_schedules', [ __CLASS__, 'register_schedules' ] );
        add_action( self::SYNC_HOOK, [ 'Fig_Sync', 'run' ] );
        add_action( self::TOKEN_REFRESH_HOOK, [ __CLASS__, 'maybe_refresh_token' ] );
        add_action( 'update_option_fig_settings', [ __CLASS__, 'reschedule_on_settings_change' ], 10, 2 );
    }

    public static function register_schedules( $schedules ) {
        if ( ! isset( $schedules['twicedaily'] ) ) {
            $schedules['twicedaily'] = [
                'interval' => 12 * HOUR_IN_SECONDS,
                'display'  => __( 'Dva puta dnevno', 'flyrec-instagram-feed' ),
            ];
        }
        return $schedules;
    }

    public static function activate() {
        // Registruj CPT odmah da rewrite pravila budu tačna posle flush-a.
        Fig_CPT::register();
        flush_rewrite_rules();

        self::schedule_sync( Fig_Helpers::get_settings()['sync_interval'] );

        if ( ! wp_next_scheduled( self::TOKEN_REFRESH_HOOK ) ) {
            wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', self::TOKEN_REFRESH_HOOK );
        }
    }

    public static function deactivate() {
        wp_clear_scheduled_hook( self::SYNC_HOOK );
        wp_clear_scheduled_hook( self::TOKEN_REFRESH_HOOK );
        flush_rewrite_rules();
    }

    public static function reschedule_on_settings_change( $old_value, $new_value ) {
        $old_interval = $old_value['sync_interval'] ?? '';
        $new_interval = $new_value['sync_interval'] ?? '';
        if ( $old_interval !== $new_interval ) {
            self::schedule_sync( $new_interval );
        }
    }

    /**
     * (Re)zakazuje sync cron prema izabranom intervalu. 'manual' briše
     * zakazani event — sync se onda pokreće samo preko dugmeta u adminu.
     */
    public static function schedule_sync( $interval ) {
        wp_clear_scheduled_hook( self::SYNC_HOOK );

        if ( 'manual' === $interval ) {
            return;
        }

        $allowed = [ 'hourly', 'twicedaily', 'daily' ];
        if ( ! in_array( $interval, $allowed, true ) ) {
            $interval = 'twicedaily';
        }

        wp_schedule_event( time() + 5 * MINUTE_IN_SECONDS, $interval, self::SYNC_HOOK );
    }

    /**
     * Osvežava token ako ističe u narednih FIG_TOKEN_REFRESH_THRESHOLD_DAYS.
     * Pokreće se dnevno preko crona.
     */
    public static function maybe_refresh_token() {
        if ( ! Fig_Token_Manager::has_token() ) {
            return;
        }
        if ( ! Fig_Token_Manager::needs_refresh_soon() ) {
            return;
        }

        $client = new Fig_Api_Client();
        $result = $client->refresh_long_lived_token();

        if ( is_wp_error( $result ) ) {
            // Ne rušimo sajt — samo logujemo status, admin ekran će pokazati
            // upozorenje ako token uskoro istekne a refresh ne uspeva.
            update_option( 'fig_token_refresh_error', $result->get_error_message(), false );
            return;
        }

        if ( ! empty( $result['access_token'] ) ) {
            Fig_Token_Manager::save_token( $result['access_token'], $result['expires_in'] ?? ( 60 * DAY_IN_SECONDS ) );
            delete_option( 'fig_token_refresh_error' );
        }
    }

    public static function next_sync_timestamp() {
        $ts = wp_next_scheduled( self::SYNC_HOOK );
        return $ts ?: 0;
    }
}
