<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Custom Post Type "flyrec_media" — po jedna Instagram objava = jedan post.
 *
 * Meta polja (sva sa prefiksom _fig_):
 *   _fig_media_id            Instagram Media ID (jedinstven, sprečava duplikate)
 *   _fig_media_type          IMAGE | VIDEO | CAROUSEL_ALBUM
 *   _fig_content_type        REELS | VIDEO | IMAGE | CAROUSEL_ALBUM (interna klasifikacija)
 *   _fig_permalink           Link ka originalnoj Instagram objavi
 *   _fig_media_url           CDN URL (za video/sliku) — može isteći, obnavlja se sync-om
 *   _fig_thumbnail_url       Thumbnail (za video/reels)
 *   _fig_timestamp           Unix timestamp originalne objave
 *   _fig_synced_at           Unix timestamp poslednje sinhronizacije ovog posta
 *   _fig_hidden              '1' ako je admin ručno isključio iz grida
 *   _fig_order               Ceo broj za ručno sortiranje (manji = prvi)
 *   _fig_children             JSON niz child media objekata (za carousel)
 */
class Fig_CPT {

    const POST_TYPE = 'flyrec_media';

    public static function register() {
        register_post_type( self::POST_TYPE, [
            'labels' => [
                'name'               => __( 'Instagram objave', 'flyrec-instagram-feed' ),
                'singular_name'      => __( 'Instagram objava', 'flyrec-instagram-feed' ),
                'menu_name'          => __( 'Instagram Feed', 'flyrec-instagram-feed' ),
                'edit_item'          => __( 'Uredi objavu', 'flyrec-instagram-feed' ),
                'view_item'          => __( 'Pogledaj objavu', 'flyrec-instagram-feed' ),
                'search_items'       => __( 'Pretraži objave', 'flyrec-instagram-feed' ),
                'not_found'          => __( 'Još nema sinhronizovanih objava. Idi na Flyrec Instagram Feed → Sinhronizacija.', 'flyrec-instagram-feed' ),
                'not_found_in_trash' => __( 'Nema objava u korpi', 'flyrec-instagram-feed' ),
            ],
            'public'        => true,
            'show_ui'       => true,
            'show_in_menu'  => true,
            'menu_icon'     => 'dashicons-instagram',
            'supports'      => [ 'title' ],
            'has_archive'   => false,
            'rewrite'       => false,
            'show_in_rest'  => false,
            'capability_type' => 'post',
            'menu_position' => 7,
        ] );

        add_action( 'add_meta_boxes', [ __CLASS__, 'add_meta_boxes' ] );
        add_action( 'save_post_' . self::POST_TYPE, [ __CLASS__, 'save_meta' ] );

        add_filter( 'manage_' . self::POST_TYPE . '_posts_columns', [ __CLASS__, 'columns' ] );
        add_action( 'manage_' . self::POST_TYPE . '_posts_custom_column', [ __CLASS__, 'column_content' ], 10, 2 );
        add_filter( 'manage_edit-' . self::POST_TYPE . '_sortable_columns', [ __CLASS__, 'sortable_columns' ] );

        add_action( 'pre_get_posts', [ __CLASS__, 'admin_default_order' ] );
        add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_reorder_assets' ] );
        add_action( 'admin_notices', [ __CLASS__, 'reorder_notice' ] );
    }

    /**
     * Na admin listi ove CPT (bez aktivnog sortiranja po drugoj koloni)
     * podrazumevano sortiramo po _fig_order — isti redosled koji se vidi
     * na sajtu, tako da drag & drop u listi ima smisla. Takođe podižemo
     * broj stavki po strani da admin vidi (i može da prevlači) sve objave
     * odjednom, bez paginacije koja bi zbunila reorder.
     */
    public static function admin_default_order( $query ) {
        if ( ! is_admin() || ! $query->is_main_query() ) {
            return;
        }
        if ( self::POST_TYPE !== $query->get( 'post_type' ) ) {
            return;
        }
        if ( ! $query->get( 'orderby' ) ) {
            $query->set( 'meta_key', '_fig_order' );
            $query->set( 'orderby', [ 'meta_value_num' => 'ASC', 'date' => 'DESC' ] );
        }
        if ( ! $query->get( 'posts_per_page' ) || (int) $query->get( 'posts_per_page' ) === get_option( 'posts_per_page' ) ) {
            $query->set( 'posts_per_page', 200 );
        }
    }

    public static function enqueue_reorder_assets( $hook ) {
        if ( 'edit.php' !== $hook || self::POST_TYPE !== ( $_GET['post_type'] ?? '' ) ) {
            return;
        }

        wp_enqueue_script( 'jquery-ui-sortable' );
        wp_enqueue_script( 'fig-reorder', FIG_URL . 'admin/js/reorder.js', [ 'jquery', 'jquery-ui-sortable' ], FIG_VERSION, true );
        wp_enqueue_style( 'fig-reorder', FIG_URL . 'admin/css/reorder.css', [], FIG_VERSION );

        wp_localize_script( 'fig-reorder', 'figReorder', [
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( Fig_Ajax::NONCE_ADMIN ),
            'i18n'    => [
                'saving' => __( 'Čuvanje redosleda…', 'flyrec-instagram-feed' ),
                'saved'  => __( 'Redosled sačuvan.', 'flyrec-instagram-feed' ),
                'error'  => __( 'Greška pri čuvanju redosleda — pokušaj ponovo.', 'flyrec-instagram-feed' ),
            ],
        ] );
    }

    public static function reorder_notice() {
        $screen = get_current_screen();
        if ( ! $screen || 'edit-' . self::POST_TYPE !== $screen->id ) {
            return;
        }
        echo '<div class="notice notice-info"><p>'
            . esc_html__( '💡 Prevuci redove (za ⠿ ikonicu) da promeniš redosled prikaza na sajtu — čuva se automatski.', 'flyrec-instagram-feed' )
            . '</p></div>';
    }

    public static function add_meta_boxes() {
        add_meta_box(
            'fig_media_details',
            __( '📷 Detalji Instagram objave', 'flyrec-instagram-feed' ),
            [ __CLASS__, 'render_meta_box' ],
            self::POST_TYPE,
            'normal',
            'high'
        );
    }

    public static function render_meta_box( $post ) {
        wp_nonce_field( 'fig_media_save', 'fig_media_nonce' );

        $media_id   = get_post_meta( $post->ID, '_fig_media_id', true );
        $content    = get_post_meta( $post->ID, '_fig_content_type', true );
        $permalink  = get_post_meta( $post->ID, '_fig_permalink', true );
        $thumb      = get_post_meta( $post->ID, '_fig_thumbnail_url', true ) ?: get_post_meta( $post->ID, '_fig_media_url', true );
        $hidden     = get_post_meta( $post->ID, '_fig_hidden', true );
        $order      = get_post_meta( $post->ID, '_fig_order', true );
        $synced_at  = get_post_meta( $post->ID, '_fig_synced_at', true );
        ?>
        <style>
            .fig-meta-table { width: 100%; border-collapse: separate; border-spacing: 0 10px; }
            .fig-meta-table th { width: 200px; font-weight: 600; padding: 8px 0; vertical-align: top; }
            .fig-meta-table td { padding: 4px 0; }
            .fig-meta-preview { max-width: 220px; border-radius: 6px; display: block; margin-bottom: 12px; }
            .fig-meta-readonly { color: #555; font-family: monospace; font-size: 12px; }
        </style>

        <?php if ( $thumb ) : ?>
            <img src="<?php echo esc_url( $thumb ); ?>" alt="" class="fig-meta-preview">
        <?php endif; ?>

        <table class="fig-meta-table">
            <tr>
                <th><?php esc_html_e( 'Instagram Media ID', 'flyrec-instagram-feed' ); ?></th>
                <td><span class="fig-meta-readonly"><?php echo esc_html( $media_id ?: '—' ); ?></span></td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Tip sadržaja', 'flyrec-instagram-feed' ); ?></th>
                <td><span class="fig-meta-readonly"><?php echo esc_html( $content ?: '—' ); ?></span></td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Link ka Instagramu', 'flyrec-instagram-feed' ); ?></th>
                <td>
                    <?php if ( $permalink ) : ?>
                        <a href="<?php echo esc_url( $permalink ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $permalink ); ?></a>
                    <?php else : ?>—<?php endif; ?>
                </td>
            </tr>
            <tr>
                <th><label for="fig_order"><?php esc_html_e( 'Redosled prikaza', 'flyrec-instagram-feed' ); ?></label></th>
                <td>
                    <input type="number" id="fig_order" name="fig_order" value="<?php echo esc_attr( $order !== '' ? $order : Fig_Sync::UNORDERED ); ?>" min="0" max="999999" style="width:120px;">
                    <p class="description"><?php esc_html_e( 'Manji broj = prikazuje se pre ostalih. Nedirnute objave imaju veliku podrazumevanu vrednost — postavi manji broj (1, 2, 3...) da izdvojiš objavu na vrh. Lakše je prevući objave direktno u listi ispod (drag & drop).', 'flyrec-instagram-feed' ); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="fig_hidden"><?php esc_html_e( 'Sakrij iz grida', 'flyrec-instagram-feed' ); ?></label></th>
                <td>
                    <label>
                        <input type="checkbox" id="fig_hidden" name="fig_hidden" value="1" <?php checked( $hidden, '1' ); ?>>
                        <?php esc_html_e( 'Ne prikazuj ovu objavu na sajtu (ostaje sinhronizovana, samo se ne prikazuje)', 'flyrec-instagram-feed' ); ?>
                    </label>
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Poslednja sinhronizacija', 'flyrec-instagram-feed' ); ?></th>
                <td><span class="fig-meta-readonly"><?php echo esc_html( $synced_at ? Fig_Helpers::format_date( $synced_at ) : '—' ); ?></span></td>
            </tr>
        </table>
        <?php
    }

    public static function save_meta( $post_id ) {
        if ( ! isset( $_POST['fig_media_nonce'] ) || ! wp_verify_nonce( $_POST['fig_media_nonce'], 'fig_media_save' ) ) {
            return;
        }
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        update_post_meta( $post_id, '_fig_order', isset( $_POST['fig_order'] ) ? absint( $_POST['fig_order'] ) : Fig_Sync::UNORDERED );
        update_post_meta( $post_id, '_fig_hidden', isset( $_POST['fig_hidden'] ) ? '1' : '' );
    }

    public static function columns( $columns ) {
        $new = [
            'cb'             => $columns['cb'],
            'fig_drag'       => '',
            'fig_thumb'      => __( 'Thumbnail', 'flyrec-instagram-feed' ),
            'title'          => __( 'Naslov', 'flyrec-instagram-feed' ),
            'fig_type'       => __( 'Tip', 'flyrec-instagram-feed' ),
            'fig_visible'    => __( 'Prikazano', 'flyrec-instagram-feed' ),
            'fig_order'      => __( 'Redosled', 'flyrec-instagram-feed' ),
            'date'           => __( 'Datum objave', 'flyrec-instagram-feed' ),
        ];
        return $new;
    }

    public static function column_content( $column, $post_id ) {
        switch ( $column ) {
            case 'fig_drag':
                echo '<span class="fig-drag-handle" title="' . esc_attr__( 'Prevuci za promenu redosleda', 'flyrec-instagram-feed' ) . '" aria-hidden="true">⠿</span>';
                break;

            case 'fig_thumb':
                $thumb = get_post_meta( $post_id, '_fig_thumbnail_url', true ) ?: get_post_meta( $post_id, '_fig_media_url', true );
                echo $thumb
                    ? '<img src="' . esc_url( $thumb ) . '" style="width:60px;height:60px;object-fit:cover;border-radius:4px;">'
                    : '<span style="color:#999;">—</span>';
                break;

            case 'fig_type':
                $type   = get_post_meta( $post_id, '_fig_content_type', true );
                $labels = Fig_Helpers::content_types();
                echo esc_html( $labels[ $type ] ?? ( $type ?: '—' ) );
                break;

            case 'fig_visible':
                $hidden = get_post_meta( $post_id, '_fig_hidden', true );
                echo $hidden
                    ? '<span style="color:#b32d2e;">🚫 ' . esc_html__( 'Sakriveno', 'flyrec-instagram-feed' ) . '</span>'
                    : '<span style="color:#2a8f3c;">✅ ' . esc_html__( 'Prikazano', 'flyrec-instagram-feed' ) . '</span>';
                break;

            case 'fig_order':
                $order = get_post_meta( $post_id, '_fig_order', true );
                echo esc_html( '' !== $order ? $order : (string) Fig_Sync::UNORDERED );
                break;
        }
    }

    public static function sortable_columns( $columns ) {
        $columns['fig_order'] = 'fig_order';
        return $columns;
    }

    /**
     * Nalazi post po Instagram Media ID-u — koristi se u sync-u da se
     * spreči dupliranje.
     */
    public static function find_by_media_id( $media_id ) {
        $posts = get_posts( [
            'post_type'      => self::POST_TYPE,
            'post_status'    => 'any',
            'posts_per_page' => 1,
            'meta_key'       => '_fig_media_id',
            'meta_value'     => $media_id,
            'fields'         => 'ids',
            'no_found_rows'  => true,
        ] );
        return $posts ? $posts[0] : 0;
    }
}
