<?php
/**
 * Pokreće se samo kada korisnik potpuno deinstalira plugin ("Delete" na
 * Plugins ekranu), ne pri običnoj deaktivaciji.
 *
 * Podaci (sinhronizovane objave) se brišu SAMO ako je admin eksplicitno
 * uključio "Obriši sve podatke pri deinstalaciji" u podešavanjima —
 * podrazumevano se čuvaju, da se slučajno ne izgubi sadržaj.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

$settings = get_option( 'fig_settings', [] );
$delete_data = ! empty( $settings['delete_on_uninstall'] );

// Token i konekcija se uvek brišu — nema razloga da ostane u bazi.
delete_option( 'fig_access_token_enc' );
delete_option( 'fig_token_expires_at' );
delete_option( 'fig_ig_user_id' );
delete_option( 'fig_ig_username' );
delete_option( 'fig_connected_at' );
delete_option( 'fig_app_id' );
delete_option( 'fig_app_secret_enc' );
delete_option( 'fig_token_refresh_error' );
delete_option( 'fig_last_sync_at' );
delete_option( 'fig_last_sync_status' );
delete_option( 'fig_last_sync_message' );
delete_option( 'fig_settings' );

wp_clear_scheduled_hook( 'fig_sync_event' );
wp_clear_scheduled_hook( 'fig_token_refresh_event' );

if ( $delete_data ) {
    $post_ids = get_posts( [
        'post_type'      => 'flyrec_media',
        'post_status'    => 'any',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'no_found_rows'  => true,
    ] );

    foreach ( $post_ids as $post_id ) {
        wp_delete_post( $post_id, true );
    }
}
