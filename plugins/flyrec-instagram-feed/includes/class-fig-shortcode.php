<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Shortcode [flyrec_instagram_feed] + deljena render logika koju koriste i
 * Gutenberg blok (Fig_Block) i Elementor widget (Fig_Elementor) — jedan
 * izvor istine za grid markup, da se izbegne dupliranje.
 */
class Fig_Shortcode {

    const TAG = 'flyrec_instagram_feed';

    private static $modal_printed = false;
    private static $assets_enqueued = false;

    public static function init() {
        add_shortcode( self::TAG, [ __CLASS__, 'render' ] );
        add_action( 'wp_enqueue_scripts', [ __CLASS__, 'register_assets' ] );
    }

    public static function register_assets() {
        wp_register_style( 'fig-frontend', FIG_URL . 'public/css/frontend.css', [], FIG_VERSION );
        wp_register_script( 'fig-frontend', FIG_URL . 'public/js/frontend.js', [], FIG_VERSION, true );
    }

    private static function enqueue_assets() {
        if ( self::$assets_enqueued ) {
            return;
        }
        self::$assets_enqueued = true;

        wp_enqueue_style( 'fig-frontend' );
        wp_enqueue_script( 'fig-frontend' );
        wp_localize_script( 'fig-frontend', 'figFrontend', [
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( Fig_Ajax::NONCE_PUBLIC ),
            'i18n'    => [
                'loading'      => __( 'Učitavanje…', 'flyrec-instagram-feed' ),
                'embedFailed'  => __( 'Video se ne može prikazati ovde.', 'flyrec-instagram-feed' ),
                'openInstagram'=> __( 'Otvori na Instagramu', 'flyrec-instagram-feed' ),
                'loadMore'     => __( 'Učitaj još', 'flyrec-instagram-feed' ),
            ],
        ] );
    }

    /**
     * Mapira "ljudske" shortcode alias-e (reels, video, image, carousel, all)
     * u interne content_type ključeve (REELS, VIDEO, IMAGE, CAROUSEL_ALBUM).
     */
    private static function map_type_atts( $type_string ) {
        $aliases = [
            'reels'    => 'REELS',
            'reel'     => 'REELS',
            'video'    => 'VIDEO',
            'videos'   => 'VIDEO',
            'image'    => 'IMAGE',
            'images'   => 'IMAGE',
            'photo'    => 'IMAGE',
            'photos'   => 'IMAGE',
            'carousel' => 'CAROUSEL_ALBUM',
            'carousel_album' => 'CAROUSEL_ALBUM',
        ];

        $canonical   = array_keys( Fig_Helpers::content_types() ); // REELS, VIDEO, IMAGE, CAROUSEL_ALBUM
        $type_string = trim( (string) $type_string );

        if ( '' === $type_string || 'all' === strtolower( $type_string ) ) {
            return $canonical;
        }

        $parts  = array_map( 'trim', explode( ',', $type_string ) );
        $mapped = [];
        foreach ( $parts as $part ) {
            // Već kanonski ključ (npr. dolazi direktno iz podešavanja "Tipovi sadržaja").
            if ( in_array( $part, $canonical, true ) ) {
                $mapped[] = $part;
                continue;
            }
            // "Ljudski" alias iz shortcode/blok atributa (reels, video, photo...).
            $lower = strtolower( $part );
            if ( isset( $aliases[ $lower ] ) ) {
                $mapped[] = $aliases[ $lower ];
            }
        }
        return $mapped ? array_values( array_unique( $mapped ) ) : $canonical;
    }

    /**
     * @return array { posts: WP_Post[], has_more: bool, total: int }
     */
    public static function query_items( $limit, $offset = 0, $type_csv = 'all' ) {
        $content_types = self::map_type_atts( $type_csv );

        $query = new WP_Query( [
            'post_type'      => Fig_CPT::POST_TYPE,
            'post_status'    => 'publish',
            'posts_per_page' => $limit,
            'offset'         => $offset,
            'meta_query'     => [
                'relation' => 'AND',
                [
                    'relation' => 'OR',
                    [ 'key' => '_fig_hidden', 'compare' => 'NOT EXISTS' ],
                    [ 'key' => '_fig_hidden', 'value' => '1', 'compare' => '!=' ],
                ],
                [ 'key' => '_fig_content_type', 'value' => $content_types, 'compare' => 'IN' ],
            ],
            'meta_key' => '_fig_order',
            'orderby'  => [ 'meta_value_num' => 'ASC', 'date' => 'DESC' ],
        ] );

        return [
            'posts'    => $query->posts,
            'has_more' => ( $offset + $limit ) < $query->found_posts,
            'total'    => $query->found_posts,
        ];
    }

    /**
     * Glavna render funkcija — koriste je shortcode, Gutenberg blok i
     * Elementor widget, sa istim setom atributa.
     */
    public static function render( $atts ) {
        self::enqueue_assets();

        $settings = Fig_Helpers::get_settings();

        // Da bismo znali da li je 'type' EKSPLICITNO prosleđen (shortcode/blok/widget),
        // proveravamo sirovi $atts PRE shortcode_atts() spajanja sa podrazumevanim
        // vrednostima — bez ovoga ne bismo mogli razlikovati "nije prosleđeno" od
        // "eksplicitno prosleđeno all", pa bi admin podešavanje "Tipovi sadržaja"
        // uvek bilo pregaženo.
        $explicit_type = isset( $atts['type'] ) ? $atts['type'] : null;

        $atts = shortcode_atts( [
            'limit'        => $settings['items_limit'],
            'columns'      => $settings['columns'],
            'type'         => 'all',
            'click_action' => $settings['click_action'],
        ], $atts, self::TAG );

        $limit        = max( 1, min( 50, absint( $atts['limit'] ) ) );
        $columns      = max( 1, min( 6, absint( $atts['columns'] ) ) );
        $click_action = in_array( $atts['click_action'], [ 'lightbox', 'instagram', 'embed' ], true ) ? $atts['click_action'] : 'lightbox';

        $type = null !== $explicit_type
            ? sanitize_text_field( $explicit_type )
            : implode( ',', $settings['content_types'] );

        $items = self::query_items( $limit, 0, $type );

        if ( empty( $items['posts'] ) ) {
            return self::render_empty_state();
        }

        ob_start();
        ?>
        <div class="fig-grid-wrapper">
            <div class="fig-grid fig-grid--cols-<?php echo esc_attr( $columns ); ?>"
                 data-offset="<?php echo esc_attr( $limit ); ?>"
                 data-limit="<?php echo esc_attr( $limit ); ?>"
                 data-type="<?php echo esc_attr( $type ); ?>"
                 data-click-action="<?php echo esc_attr( $click_action ); ?>">
                <?php foreach ( $items['posts'] as $post ) : ?>
                    <?php echo self::render_card( $post, $click_action ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
                <?php endforeach; ?>
            </div>

            <div class="fig-grid-actions">
                <?php if ( $items['has_more'] ) : ?>
                    <button type="button" class="fig-btn fig-btn--load-more">
                        <?php esc_html_e( 'Učitaj još', 'flyrec-instagram-feed' ); ?>
                    </button>
                <?php endif; ?>

                <?php $ig_url = self::instagram_profile_url(); ?>
                <?php if ( $ig_url ) : ?>
                    <a href="<?php echo esc_url( $ig_url ); ?>" target="_blank" rel="noopener noreferrer" class="fig-btn fig-btn--ig">
                        <?php esc_html_e( 'Pogledajte na Instagramu', 'flyrec-instagram-feed' ); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <?php echo self::render_lightbox_modal(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
        <?php
        return ob_get_clean();
    }

    private static function instagram_profile_url() {
        $username = Fig_Token_Manager::get_ig_username();
        return $username ? 'https://instagram.com/' . rawurlencode( $username ) : '';
    }

    private static function render_empty_state() {
        if ( ! Fig_Helpers::current_user_can_manage() ) {
            return '';
        }
        return '<p class="fig-empty-admin-notice">'
            . esc_html__( 'Flyrec Instagram Feed: nema sinhronizovanih objava još. (Ova poruka je vidljiva samo administratorima.) Idi na Flyrec Instagram Feed → Konekcija.', 'flyrec-instagram-feed' )
            . '</p>';
    }

    public static function render_card( $post, $click_action ) {
        $settings      = Fig_Helpers::get_settings();
        $content_type  = get_post_meta( $post->ID, '_fig_content_type', true );
        $thumb         = get_post_meta( $post->ID, '_fig_thumbnail_url', true ) ?: get_post_meta( $post->ID, '_fig_media_url', true );
        $permalink     = get_post_meta( $post->ID, '_fig_permalink', true );
        $timestamp     = (int) get_post_meta( $post->ID, '_fig_timestamp', true );
        $caption       = get_post_field( 'post_content', $post );
        $is_vertical   = in_array( $content_type, [ 'REELS', 'VIDEO' ], true );
        $views         = get_post_meta( $post->ID, '_fig_views', true );
        $show_views    = $settings['show_views'] && 'REELS' === $content_type && '' !== $views;

        ob_start();
        ?>
        <div class="fig-item <?php echo $is_vertical ? 'fig-item--vertical' : 'fig-item--natural'; ?>"
             data-post-id="<?php echo esc_attr( $post->ID ); ?>"
             data-permalink="<?php echo esc_attr( $permalink ); ?>"
             data-click-action="<?php echo esc_attr( $click_action ); ?>"
             data-type="<?php echo esc_attr( $content_type ); ?>"
             role="button"
             tabindex="0"
             aria-label="<?php echo esc_attr( 'instagram' === $click_action ? __( 'Otvori na Instagramu', 'flyrec-instagram-feed' ) : __( 'Pogledaj objavu', 'flyrec-instagram-feed' ) ); ?>">

            <?php if ( $thumb ) : ?>
                <img src="<?php echo esc_url( $thumb ); ?>" alt="" class="fig-item-thumb" loading="lazy" decoding="async"
                     onerror="this.onerror=null;this.removeAttribute('src');this.classList.add('fig-item-thumb--placeholder');">
            <?php else : ?>
                <div class="fig-item-thumb fig-item-thumb--placeholder"></div>
            <?php endif; ?>

            <span class="fig-item-play" aria-hidden="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><polygon points="6 3 20 12 6 21"/></svg>
            </span>

            <?php if ( $show_views ) : ?>
                <span class="fig-item-views" aria-label="<?php
                    /* translators: %s: view count */
                    echo esc_attr( sprintf( __( '%s pregleda', 'flyrec-instagram-feed' ), Fig_Helpers::format_count( $views ) ) );
                ?>">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5C21.27 7.61 17 4.5 12 4.5zm0 12.5a5 5 0 110-10 5 5 0 010 10zm0-8a3 3 0 100 6 3 3 0 000-6z"/></svg>
                    <?php echo esc_html( Fig_Helpers::format_count( $views ) ); ?>
                </span>
            <?php endif; ?>

            <?php if ( $settings['show_caption'] || $settings['show_date'] ) : ?>
                <div class="fig-item-overlay">
                    <?php if ( $settings['show_caption'] && $caption ) : ?>
                        <p class="fig-item-caption"><?php echo esc_html( wp_trim_words( $caption, 12 ) ); ?></p>
                    <?php endif; ?>
                    <?php if ( $settings['show_date'] && $timestamp ) : ?>
                        <span class="fig-item-date"><?php echo esc_html( Fig_Helpers::format_date( $timestamp ) ); ?></span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    private static function render_lightbox_modal() {
        if ( self::$modal_printed ) {
            return '';
        }
        self::$modal_printed = true;

        ob_start();
        ?>
        <div class="fig-modal" id="figLightboxModal" aria-modal="true" role="dialog" aria-hidden="true">
            <button type="button" class="fig-modal-close" id="figLightboxClose" aria-label="<?php esc_attr_e( 'Zatvori', 'flyrec-instagram-feed' ); ?>">&times;</button>
            <div class="fig-modal-content" id="figLightboxContent">
                <div class="fig-modal-spinner" id="figLightboxSpinner"></div>
                <div class="fig-modal-embed" id="figLightboxEmbed"></div>
                <img class="fig-modal-img" id="figLightboxImg" src="" alt="" style="display:none;">
                <p class="fig-modal-fallback" id="figLightboxFallback" style="display:none;"></p>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
