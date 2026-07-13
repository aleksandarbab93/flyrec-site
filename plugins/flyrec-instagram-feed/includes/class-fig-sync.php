<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Sinhronizacija Instagram objava → flyrec_media CPT postovi.
 *
 * Dedupe: svaka objava se identifikuje preko _fig_media_id, tako da ponovno
 * pokretanje sync-a ažurira postojeće postove umesto da pravi duplikate.
 * Ne briše lokalne postove koji "ispadnu" iz trenutnog fetch prozora —
 * admin ih po potrebi ručno briše kroz standardni WP admin.
 */
class Fig_Sync {

    const OPT_LAST_SYNC_AT     = 'fig_last_sync_at';
    const OPT_LAST_SYNC_STATUS = 'fig_last_sync_status'; // success | error
    const OPT_LAST_SYNC_MSG    = 'fig_last_sync_message';

    /**
     * Podrazumevani "redosled" za novo sinhronizovane objave koje admin
     * nije ručno pozicionirao. Namerno veliki broj — pošto je sortiranje
     * ASC (manji broj = prikazuje se pre ostalih), svaka objava kojoj admin
     * ručno dodeli mali broj (1, 2, 3...) automatski isplivava iznad svih
     * "nedirnutih" objava, umesto da se izgubi među njima na vrednosti 0.
     */
    const UNORDERED = 100000;

    /**
     * Pokreće sinhronizaciju. Bezbedna za pozivanje iz cron-a ili ručno
     * (AJAX) — nikad ne baca izuzetak ka pozivaocu, samo vraća rezultat.
     *
     * @return array { success: bool, message: string, fetched: int, created: int, updated: int }
     */
    public static function run() {
        if ( ! Fig_Token_Manager::has_token() ) {
            return self::finish( false, __( 'Instagram nalog nije povezan — nema tokena.', 'flyrec-instagram-feed' ) );
        }

        if ( Fig_Token_Manager::is_expired() ) {
            return self::finish( false, __( 'Instagram access token je istekao. Poveži nalog ponovo u podešavanjima.', 'flyrec-instagram-feed' ) );
        }

        $settings = Fig_Helpers::get_settings();
        $limit    = max( 1, min( 50, (int) $settings['items_limit'] + 10 ) ); // malo veći buffer za filtriranje po tipu

        $client   = new Fig_Api_Client();
        $result   = $client->get_media( $limit );

        if ( is_wp_error( $result ) ) {
            return self::finish( false, $result->get_error_message() );
        }

        $created = 0;
        $updated = 0;

        foreach ( $result['data'] as $item ) {
            if ( empty( $item['id'] ) ) {
                continue;
            }
            $is_new = self::upsert_media_item( $item );
            $is_new ? $created++ : $updated++;
        }

        $total = count( $result['data'] );

        return self::finish(
            true,
            sprintf(
                /* translators: 1: total fetched, 2: new posts, 3: updated posts */
                __( 'Sinhronizovano %1$d objava (%2$d novih, %3$d ažuriranih).', 'flyrec-instagram-feed' ),
                $total,
                $created,
                $updated
            ),
            $total,
            $created,
            $updated
        );
    }

    /**
     * Upisuje ili ažurira jedan media objekat kao flyrec_media post.
     *
     * @return bool true ako je post NOVO kreiran, false ako je samo ažuriran.
     */
    private static function upsert_media_item( array $item ) {
        $media_id     = sanitize_text_field( $item['id'] );
        $existing_id  = Fig_CPT::find_by_media_id( $media_id );
        $timestamp    = ! empty( $item['timestamp'] ) ? strtotime( $item['timestamp'] ) : time();
        $caption      = isset( $item['caption'] ) ? sanitize_textarea_field( $item['caption'] ) : '';
        $content_type = Fig_Helpers::map_content_type(
            $item['media_type'] ?? 'IMAGE',
            $item['media_product_type'] ?? ''
        );

        $post_data = [
            'post_type'   => Fig_CPT::POST_TYPE,
            'post_title'  => Fig_Helpers::excerpt_title( $caption, Fig_Helpers::format_date( $timestamp ) ),
            'post_content'=> $caption,
            'post_status' => 'publish',
            'post_date'   => date( 'Y-m-d H:i:s', $timestamp ),
        ];

        $is_new = ! $existing_id;

        if ( $existing_id ) {
            $post_data['ID'] = $existing_id;
            wp_update_post( $post_data );
            $post_id = $existing_id;
        } else {
            $post_id = wp_insert_post( $post_data, true );
            if ( is_wp_error( $post_id ) ) {
                return false;
            }
            // Podrazumevani redosled = broj sekundi od epohe, obrnuto, tako
            // da novije objave prirodno idu prve dok admin ručno ne promeni.
            update_post_meta( $post_id, '_fig_order', self::UNORDERED );
            update_post_meta( $post_id, '_fig_hidden', '' );
        }

        update_post_meta( $post_id, '_fig_media_id', $media_id );
        update_post_meta( $post_id, '_fig_media_type', sanitize_text_field( $item['media_type'] ?? '' ) );
        update_post_meta( $post_id, '_fig_content_type', $content_type );
        update_post_meta( $post_id, '_fig_permalink', esc_url_raw( $item['permalink'] ?? '' ) );
        update_post_meta( $post_id, '_fig_media_url', esc_url_raw( $item['media_url'] ?? '' ) );
        update_post_meta( $post_id, '_fig_thumbnail_url', esc_url_raw( $item['thumbnail_url'] ?? ( $item['media_url'] ?? '' ) ) );
        update_post_meta( $post_id, '_fig_timestamp', $timestamp );
        update_post_meta( $post_id, '_fig_synced_at', time() );

        if ( ! empty( $item['children']['data'] ) && is_array( $item['children']['data'] ) ) {
            $children = array_map( function ( $child ) {
                return [
                    'media_type'    => sanitize_text_field( $child['media_type'] ?? '' ),
                    'media_url'     => esc_url_raw( $child['media_url'] ?? '' ),
                    'thumbnail_url' => esc_url_raw( $child['thumbnail_url'] ?? '' ),
                ];
            }, $item['children']['data'] );
            update_post_meta( $post_id, '_fig_children', wp_json_encode( $children ) );
        } else {
            delete_post_meta( $post_id, '_fig_children' );
        }

        return $is_new;
    }

    private static function finish( $success, $message, $fetched = 0, $created = 0, $updated = 0 ) {
        update_option( self::OPT_LAST_SYNC_AT, time(), false );
        update_option( self::OPT_LAST_SYNC_STATUS, $success ? 'success' : 'error', false );
        update_option( self::OPT_LAST_SYNC_MSG, $message, false );

        return [
            'success' => $success,
            'message' => $message,
            'fetched' => $fetched,
            'created' => $created,
            'updated' => $updated,
        ];
    }

    public static function get_last_sync_at() {
        return (int) get_option( self::OPT_LAST_SYNC_AT, 0 );
    }

    public static function get_last_sync_status() {
        return [
            'status'  => get_option( self::OPT_LAST_SYNC_STATUS, '' ),
            'message' => get_option( self::OPT_LAST_SYNC_MSG, '' ),
            'at'      => self::get_last_sync_at(),
        ];
    }

    /**
     * Briše sve sinhronizovane objave (CPT postove) i resetuje status sync-a.
     * Token/konekcija ostaju netaknuti — samo se podaci brišu radi ponovne
     * čiste sinhronizacije.
     */
    public static function clear_synced_data() {
        $post_ids = get_posts( [
            'post_type'      => Fig_CPT::POST_TYPE,
            'post_status'    => 'any',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'no_found_rows'  => true,
        ] );

        foreach ( $post_ids as $post_id ) {
            wp_delete_post( $post_id, true );
        }

        delete_option( self::OPT_LAST_SYNC_AT );
        delete_option( self::OPT_LAST_SYNC_STATUS );
        delete_option( self::OPT_LAST_SYNC_MSG );

        return count( $post_ids );
    }

    /**
     * Jednokratna migracija: objave sinhronizovane pre uvođenja UNORDERED
     * sentinela imaju _fig_order = 0, što je nekad sudaralo sa ručno
     * postavljenim malim brojevima (npr. "1") — 0 je uvek ispadao ispred
     * "1" u ASC sortiranju, iako je admin očekivao suprotno. Ova migracija
     * prebacuje sve "nedirnute" (0 ili prazno) objave na novi sentinel,
     * jednom, po verziji baze.
     */
    public static function maybe_migrate_order_defaults() {
        if ( get_option( 'fig_db_version' ) === FIG_VERSION ) {
            return;
        }

        $post_ids = get_posts( [
            'post_type'      => Fig_CPT::POST_TYPE,
            'post_status'    => 'any',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'no_found_rows'  => true,
            'meta_query'     => [
                'relation' => 'OR',
                [ 'key' => '_fig_order', 'value' => '0', 'compare' => '=' ],
                [ 'key' => '_fig_order', 'compare' => 'NOT EXISTS' ],
            ],
        ] );

        foreach ( $post_ids as $post_id ) {
            update_post_meta( $post_id, '_fig_order', self::UNORDERED );
        }

        update_option( 'fig_db_version', FIG_VERSION, false );
    }
}
