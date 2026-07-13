<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Sitne zajedničke pomoćne funkcije korišćene kroz ceo plugin.
 */
class Fig_Helpers {

    /** Tipovi sadržaja koje plugin razlikuje u adminu i shortcode-u. */
    public static function content_types() {
        return [
            'REELS'   => __( 'Reels', 'flyrec-instagram-feed' ),
            'VIDEO'   => __( 'Video', 'flyrec-instagram-feed' ),
            'IMAGE'   => __( 'Fotografija', 'flyrec-instagram-feed' ),
            'CAROUSEL_ALBUM' => __( 'Carousel', 'flyrec-instagram-feed' ),
        ];
    }

    /**
     * Mapira Instagram media_type/media_product_type u interni "content type"
     * koji koristimo za filtriranje (REELS/VIDEO/IMAGE/CAROUSEL_ALBUM).
     */
    public static function map_content_type( $media_type, $media_product_type ) {
        if ( 'CAROUSEL_ALBUM' === $media_type ) {
            return 'CAROUSEL_ALBUM';
        }
        if ( 'VIDEO' === $media_type ) {
            return 'REELS' === $media_product_type ? 'REELS' : 'VIDEO';
        }
        return 'IMAGE';
    }

    /** Vraća opcije plugina sa fallback podrazumevanim vrednostima. */
    public static function get_settings() {
        $defaults = [
            'sync_interval'      => 'twicedaily', // hourly | twicedaily | daily | manual
            'items_limit'        => 12,
            'content_types'      => [ 'REELS', 'VIDEO', 'IMAGE', 'CAROUSEL_ALBUM' ],
            'show_caption'       => true,
            'show_date'          => true,
            'click_action'       => 'lightbox', // lightbox | instagram | embed
            'columns'            => 4,
            'delete_on_uninstall'=> false,
        ];
        $saved = get_option( 'fig_settings', [] );
        if ( ! is_array( $saved ) ) {
            $saved = [];
        }
        return wp_parse_args( $saved, $defaults );
    }

    public static function update_settings( array $partial ) {
        $current = self::get_settings();
        $updated = array_merge( $current, $partial );
        update_option( 'fig_settings', $updated, false );
        return $updated;
    }

    /** Skraćuje caption za korišćenje kao naslov posta. */
    public static function excerpt_title( $caption, $fallback_date = '' ) {
        $caption = trim( wp_strip_all_tags( (string) $caption ) );
        if ( '' === $caption ) {
            return $fallback_date
                ? sprintf( __( 'Instagram objava – %s', 'flyrec-instagram-feed' ), $fallback_date )
                : __( 'Instagram objava', 'flyrec-instagram-feed' );
        }
        if ( function_exists( 'mb_substr' ) ) {
            $short = mb_substr( $caption, 0, 70 );
        } else {
            $short = substr( $caption, 0, 70 );
        }
        return $short . ( strlen( $caption ) > 70 ? '…' : '' );
    }

    /** Formatirani datum za admin/frontend prikaz, po WP lokalnim podešavanjima. */
    public static function format_date( $timestamp ) {
        if ( ! $timestamp ) {
            return '';
        }
        return date_i18n( get_option( 'date_format' ), $timestamp );
    }

    /** Da li je trenutni korisnik ovlašćen da menja podešavanja plugina. */
    public static function current_user_can_manage() {
        return current_user_can( 'manage_options' );
    }
}
