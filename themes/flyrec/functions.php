<?php
/**
 * FlyRec Theme Functions
 * Custom WordPress tema za profesionalno snimanje dronom
 */

// =============================================
// GOOGLE TAG MANAGER / ANALYTICS – samo na produkciji
// Na lokalu (Local by Flywheel postavlja WP_ENVIRONMENT_TYPE=local u
// wp-config.php) i na staging okruženjima ovi skriptovi se uopšte ne
// učitavaju, da testiranje/development ne zagađuje statistiku.
// =============================================
function flyrec_is_production() {
    return 'production' === wp_get_environment_type();
}

// =============================================
// GOOGLE TAG MANAGER (head)
// =============================================
add_action( 'wp_head', function () {
    if ( ! flyrec_is_production() ) return;
    ?>
    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-T2SNL2RX');</script>
    <!-- End Google Tag Manager -->
    <?php
}, 1 );

// =============================================
// GOOGLE TAG MANAGER (body, noscript
// =============================================
add_action( 'wp_body_open', function () {
    if ( ! flyrec_is_production() ) return;
    ?>
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-T2SNL2RX"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
    <?php
} );

// =============================================
// GOOGLE ANALYTICS (gtag.js)
// =============================================
add_action( 'wp_head', function () {
    if ( ! flyrec_is_production() ) return;
    ?>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-6HNH72GWJT"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());

      gtag('config', 'G-6HNH72GWJT');
    </script>
    <?php
} );

// =============================================
// THEME SETUP
// =============================================
function flyrec_setup() {
    load_theme_textdomain( 'flyrec', get_template_directory() . '/languages' );

    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'html5', [ 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ] );
    add_theme_support( 'custom-logo', [
        'height'      => 60,
        'width'       => 200,
        'flex-height' => true,
        'flex-width'  => true,
    ] );

    register_nav_menus( [
        'primary' => __( 'Primarna Navigacija', 'flyrec' ),
        'footer'  => __( 'Footer Navigacija', 'flyrec' ),
    ] );

    add_image_size( 'flyrec-thumb',     640, 360,  true );
    add_image_size( 'flyrec-portfolio', 800, 600,  true );
}
add_action( 'after_setup_theme', 'flyrec_setup' );

// =============================================
// ENQUEUE STYLES & SCRIPTS
// =============================================
function flyrec_enqueue_scripts() {
    $ver = wp_get_theme()->get( 'Version' );

    // Google Fonts – Barlow (tanak geometrijski sans-serif, odgovara stilu loga)
    wp_enqueue_style(
        'flyrec-fonts',
        'https://fonts.googleapis.com/css2?family=Barlow:wght@200;300;400;500;600;700&display=swap',
        [],
        null
    );

    // Font Awesome 6 za ikone
    wp_enqueue_style(
        'font-awesome',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css',
        [],
        '6.5.0'
    );

    // Glavni CSS
    wp_enqueue_style(
        'flyrec-main',
        get_template_directory_uri() . '/assets/css/main.css',
        [ 'flyrec-fonts', 'font-awesome' ],
        $ver
    );

    // style.css (WordPress obavezno)
    wp_enqueue_style( 'flyrec-style', get_stylesheet_uri(), [ 'flyrec-main' ], $ver );

    // Glavni JS (u footer-u)
    wp_enqueue_script(
        'flyrec-main',
        get_template_directory_uri() . '/assets/js/main.js',
        [],
        $ver,
        true
    );

    // Prosleđivanje PHP podataka u JS
    wp_localize_script( 'flyrec-main', 'flyrecData', [
        'ajaxUrl' => admin_url( 'admin-ajax.php' ),
        'nonce'   => wp_create_nonce( 'flyrec_contact_nonce' ),
        'i18n'    => [
            'themeToLight'  => __( 'Pređi na svetlu temu', 'flyrec' ),
            'themeToDark'   => __( 'Pređi na tamnu temu', 'flyrec' ),
            'themeLight'    => __( 'Svetla tema', 'flyrec' ),
            'themeDark'     => __( 'Tamna tema', 'flyrec' ),
            'sendError'     => __( 'Greška pri slanju. Pokušajte ponovo.', 'flyrec' ),
            'networkError'  => __( 'Mrežna greška. Proverite konekciju i pokušajte ponovo.', 'flyrec' ),
        ],
    ] );
}
add_action( 'wp_enqueue_scripts', 'flyrec_enqueue_scripts' );

// =============================================
// CUSTOM POST TYPE: SNIMCI (VIDEOS)
// =============================================
function flyrec_register_cpts() {

    // CPT: Snimci (Video radovi)
    $video_labels = [
        'name'               => __( 'Snimci',                 'flyrec' ),
        'singular_name'      => __( 'Snimak',                 'flyrec' ),
        'add_new'            => __( 'Dodaj snimak',           'flyrec' ),
        'add_new_item'       => __( 'Dodaj novi snimak',      'flyrec' ),
        'edit_item'          => __( 'Uredi snimak',           'flyrec' ),
        'new_item'           => __( 'Novi snimak',            'flyrec' ),
        'view_item'          => __( 'Pogledaj snimak',        'flyrec' ),
        'search_items'       => __( 'Pretraži snimke',        'flyrec' ),
        'not_found'          => __( 'Nema snimaka',           'flyrec' ),
        'not_found_in_trash' => __( 'Nema snimaka u korpi',   'flyrec' ),
        'menu_name'          => __( 'Video Radovi',        'flyrec' ),
    ];

    register_post_type( 'flyrec_video', [
        'labels'        => $video_labels,
        'public'        => true,
        'show_in_menu'  => true,
        'menu_icon'     => 'dashicons-video-alt3',
        'supports'      => [ 'title', 'editor', 'thumbnail' ],
        'has_archive'   => false,
        'rewrite'       => [ 'slug' => 'snimci' ],
        'show_in_rest'  => true,
        'menu_position' => 5,
    ] );

}
add_action( 'init', 'flyrec_register_cpts' );

// =============================================
// META BOXES ZA VIDEO CPT
// =============================================
function flyrec_add_meta_boxes() {

    // Meta box za video detalje
    add_meta_box(
        'flyrec_video_details',
        __( '📹 Detalji snimka', 'flyrec' ),
        'flyrec_video_meta_callback',
        'flyrec_video',
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'flyrec_add_meta_boxes' );

function flyrec_video_meta_callback( $post ) {
    wp_nonce_field( 'flyrec_video_save', 'flyrec_video_nonce' );

    $url   = get_post_meta( $post->ID, '_flyrec_video_url',   true );
    $type  = get_post_meta( $post->ID, '_flyrec_video_type',  true ) ?: 'youtube';
    $order = get_post_meta( $post->ID, '_flyrec_video_order', true ) ?: 0;
    ?>
    <style>
        .flyrec-meta-table { width:100%; border-collapse:separate; border-spacing:0 10px; }
        .flyrec-meta-table th { width:220px; font-weight:600; padding:8px 0; vertical-align:top; }
        .flyrec-meta-table td { padding:4px 0; }
        .flyrec-meta-table input[type=url],
        .flyrec-meta-table input[type=number] { width:100%; padding:8px 12px; border:1px solid #ddd; border-radius:4px; font-size:14px; }
        .flyrec-meta-table select { padding:8px 12px; border:1px solid #ddd; border-radius:4px; font-size:14px; }
        .flyrec-meta-info { margin-top:16px; padding:14px 16px; background:#fff8f8; border-left:4px solid #e8312a; border-radius:0 4px 4px 0; font-size:13px; line-height:1.7; }
    </style>

    <table class="flyrec-meta-table">
        <tr>
            <th><label for="flyrec_video_url"><?php esc_html_e( 'Video URL', 'flyrec' ); ?></label></th>
            <td>
                <input type="url"
                    id="flyrec_video_url"
                    name="flyrec_video_url"
                    value="<?php echo esc_attr( $url ); ?>"
                    placeholder="https://www.youtube.com/watch?v=XXXXXXXXXXX"
                />
                <p class="description"><?php esc_html_e( 'YouTube, Vimeo ili Instagram URL snimka.', 'flyrec' ); ?></p>
            </td>
        </tr>
        <tr>
            <th><label for="flyrec_video_type"><?php esc_html_e( 'Platforma', 'flyrec' ); ?></label></th>
            <td>
                <select id="flyrec_video_type" name="flyrec_video_type">
                    <option value="youtube"   <?php selected( $type, 'youtube' );   ?>>YouTube</option>
                    <option value="vimeo"     <?php selected( $type, 'vimeo' );     ?>>Vimeo</option>
                    <option value="instagram" <?php selected( $type, 'instagram' ); ?>>Instagram Reel</option>
                </select>
            </td>
        </tr>
        <tr>
            <th><label for="flyrec_video_order"><?php esc_html_e( 'Redosled prikaza', 'flyrec' ); ?></label></th>
            <td>
                <input type="number"
                    id="flyrec_video_order"
                    name="flyrec_video_order"
                    value="<?php echo esc_attr( $order ); ?>"
                    min="0" max="999"
                    style="width:100px;"
                />
                <p class="description"><?php esc_html_e( 'Manji broj = prikazuje se pre ostalih.', 'flyrec' ); ?></p>
            </td>
        </tr>
    </table>

    <div class="flyrec-meta-info">
        <strong><?php esc_html_e( 'Uputstvo:', 'flyrec' ); ?></strong><br>
        <?php
        /* translators: %s: field name "Naslov" (Title) */
        printf( esc_html__( '• %s — unesi gore u standardno "Naslov" polje.', 'flyrec' ), '<strong>' . esc_html__( 'Naslov', 'flyrec' ) . '</strong>' );
        ?><br>
        <?php
        /* translators: %s: field name "Opis" (Description) */
        printf( esc_html__( '• %s — unesi u "Sadržaj" editor ispod.', 'flyrec' ), '<strong>' . esc_html__( 'Opis', 'flyrec' ) . '</strong>' );
        ?><br>
        <?php
        /* translators: %s: field name "Thumbnail" */
        printf( esc_html__( '• %s — postavi kroz "Istaknuta slika" u desnom panelu.', 'flyrec' ), '<strong>' . esc_html__( 'Thumbnail', 'flyrec' ) . '</strong>' );
        ?><br>
        • <strong><?php esc_html_e( 'YouTube primer:', 'flyrec' ); ?></strong> <code>https://www.youtube.com/watch?v=dQw4w9WgXcQ</code>
    </div>
    <?php
}

function flyrec_save_video_meta( $post_id ) {
    if ( ! isset( $_POST['flyrec_video_nonce'] ) ) return;
    if ( ! wp_verify_nonce( $_POST['flyrec_video_nonce'], 'flyrec_video_save' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    if ( isset( $_POST['flyrec_video_url'] ) ) {
        update_post_meta( $post_id, '_flyrec_video_url', esc_url_raw( $_POST['flyrec_video_url'] ) );
    }
    if ( isset( $_POST['flyrec_video_type'] ) ) {
        update_post_meta( $post_id, '_flyrec_video_type', sanitize_text_field( $_POST['flyrec_video_type'] ) );
    }
    if ( isset( $_POST['flyrec_video_order'] ) ) {
        update_post_meta( $post_id, '_flyrec_video_order', absint( $_POST['flyrec_video_order'] ) );
    }
}
add_action( 'save_post_flyrec_video', 'flyrec_save_video_meta' );

// =============================================
// HELPER: KONVERZIJA URL → EMBED URL
// =============================================
function flyrec_get_embed_url( $url, $type = 'youtube' ) {
    if ( 'youtube' === $type ) {
        $id = '';
        // youtube.com/watch?v=ID ili youtu.be/ID ili shorts ili embed
        if ( preg_match( '/(?:v=|youtu\.be\/|\/embed\/|\/shorts\/)([a-zA-Z0-9_-]{11})/', $url, $m ) ) {
            $id = $m[1];
        }
        if ( $id ) {
            return 'https://www.youtube.com/embed/' . $id . '?rel=0&modestbranding=1&showinfo=0&color=white';
        }
    }

    if ( 'vimeo' === $type ) {
        if ( preg_match( '/vimeo\.com\/(\d+)/', $url, $m ) ) {
            return 'https://player.vimeo.com/video/' . $m[1] . '?color=e8312a&title=0&byline=0&portrait=0';
        }
    }

    if ( 'instagram' === $type ) {
        if ( preg_match( '/instagram\.com\/(?:p|reel)\/([a-zA-Z0-9_-]+)/', $url, $m ) ) {
            return 'https://www.instagram.com/p/' . $m[1] . '/embed/';
        }
    }

    return $url;
}

// =============================================
// HELPER: YOUTUBE EMBED ZA HERO POZADINU (autoplay, loop, bez kontrola)
// =============================================
function flyrec_get_hero_youtube_embed( $url ) {
    if ( ! preg_match( '/(?:v=|youtu\.be\/|\/embed\/|\/shorts\/)([a-zA-Z0-9_-]{11})/', $url, $m ) ) {
        return '';
    }
    $id = $m[1];
    return 'https://www.youtube.com/embed/' . $id . '?' . http_build_query( [
        'autoplay'       => 1,
        'mute'           => 1,
        'loop'           => 1,
        'playlist'       => $id, // potrebno da bi loop=1 radio za jedan video
        'controls'       => 0,
        'showinfo'       => 0,
        'rel'            => 0,
        'modestbranding' => 1,
        'iv_load_policy' => 3,
        'disablekb'      => 1,
        'playsinline'    => 1,
        'enablejsapi'    => 0,
    ] );
}

// =============================================
// WORDPRESS CUSTOMIZER
// =============================================
function flyrec_customize_register( $wp_customize ) {

    // ── LOGO SLIKE ────────────────────────────
    // Ovo je najbrži način postavljanja logoa bez editovanja koda
    $wp_customize->add_section( 'flyrec_logos', [
        'title'       => __( '🖼 Logo Slike', 'flyrec' ),
        'priority'    => 25,
        'description' => __( 'Postavi bijeli logo za hero/tamnu pozadinu i tamni logo za bijeli header nakon skrola.', 'flyrec' ),
    ] );

    $wp_customize->add_setting( 'flyrec_logo_light', [
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ] );
    $wp_customize->add_control(
        new WP_Customize_Image_Control( $wp_customize, 'flyrec_logo_light', [
            'label'       => __( 'Bijeli logo (hero / dark tema)', 'flyrec' ),
            'description' => __( 'PNG sa bijelim/svijetlim tekstom. Prikazuje se na transparentnom headeru i u tamnoj temi.', 'flyrec' ),
            'section'     => 'flyrec_logos',
        ] )
    );

    $wp_customize->add_setting( 'flyrec_logo_dark', [
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ] );
    $wp_customize->add_control(
        new WP_Customize_Image_Control( $wp_customize, 'flyrec_logo_dark', [
            'label'       => __( 'Tamni logo (bijeli header / light tema)', 'flyrec' ),
            'description' => __( 'PNG sa tamnim tekstom. Prikazuje se kada korisnik skroluje u svijetloj temi.', 'flyrec' ),
            'section'     => 'flyrec_logos',
        ] )
    );

    // ── HERO ──────────────────────────────────
    $wp_customize->add_section( 'flyrec_hero', [
        'title'    => __( '🎬 Hero Sekcija', 'flyrec' ),
        'priority' => 30,
    ] );

    // Tip pozadine: video ili slika
    $wp_customize->add_setting( 'flyrec_hero_bg_type', [
        'default'           => 'video',
        'sanitize_callback' => function( $val ) { return in_array( $val, [ 'video', 'image' ] ) ? $val : 'video'; },
        'transport'         => 'postMessage',
    ] );
    $wp_customize->add_control( 'flyrec_hero_bg_type', [
        'label'   => __( 'Tip pozadine', 'flyrec' ),
        'section' => 'flyrec_hero',
        'type'    => 'select',
        'choices' => [
            'video' => __( '🎬 Video (MP4)', 'flyrec' ),
            'image' => __( '🖼 Slika',       'flyrec' ),
        ],
    ] );

    $wp_customize->add_setting( 'flyrec_hero_image_url', [
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
        'transport'         => 'postMessage',
    ] );
    $wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'flyrec_hero_image_url', [
        'label'       => __( 'Hero slika', 'flyrec' ),
        'description' => __( 'Prikazuje se samo kada je tip pozadine "Slika". Preporučena veličina: 1920×1080px.', 'flyrec' ),
        'section'     => 'flyrec_hero',
    ] ) );

    // Napomena: hero naslov/podnaslov/dugmad su od sada definisani po jeziku
    // direktno u front-page.php ($flyrec_hero_i18n), ne kroz Customizer –
    // tako se ispravno prevode na sr/en/ru. Ovde ostaje samo video/slika pozadina.
    $hero_settings = [
        'flyrec_hero_video_url' => [
            'default' => '',
            'label'   => __( 'Hero video URL (MP4 iz Media Library ili YouTube link)', 'flyrec' ),
            'type'    => 'url',
            'desc'    => __( 'Nalepi MP4 URL iz Media Library ili YouTube link (npr. https://www.youtube.com/watch?v=XXXXXXXXXXX). Video ide u loop, bez zvuka i bez YouTube kontrola. Ako ostane prazno, prikazuje se Hero slika.', 'flyrec' ),
        ],
    ];

    foreach ( $hero_settings as $key => $args ) {
        $wp_customize->add_setting( $key, [
            'default'           => $args['default'],
            'sanitize_callback' => ( $args['type'] === 'url' ) ? 'esc_url_raw' : 'sanitize_text_field',
            'transport'         => 'postMessage',
        ] );
        $wp_customize->add_control( $key, [
            'label'       => $args['label'],
            'section'     => 'flyrec_hero',
            'type'        => $args['type'],
            'description' => $args['desc'] ?? '',
        ] );
    }

    // ── KONTAKT ───────────────────────────────
    $wp_customize->add_section( 'flyrec_contact', [
        'title'    => __( '📞 Kontakt Informacije', 'flyrec' ),
        'priority' => 40,
    ] );

    // Napomena: "Lokacija" je od sada definisana po jeziku direktno u
    // front-page.php/footer.php ($flyrec_hero_i18n), jer se naziv mesta
    // prevodi (npr. "Tivat, Crna Gora" → "Tivat, Montenegro"). Telefon/email/
    // Instagram ostaju ovde jer su isti bez obzira na jezik.
    $contact_settings = [
        'flyrec_contact_phone'     => [ __( 'Telefon',       'flyrec' ), '+381 60 000 0000',             'text' ],
        'flyrec_contact_email'     => [ __( 'Email',         'flyrec' ), 'info@flyrec.rs',                'text' ],
        'flyrec_contact_instagram' => [ __( 'Instagram URL', 'flyrec' ), 'https://instagram.com/flyrec',  'url'  ],
    ];

    foreach ( $contact_settings as $key => [ $label, $default, $type ] ) {
        $wp_customize->add_setting( $key, [
            'default'           => $default,
            'sanitize_callback' => ( $type === 'url' ) ? 'esc_url_raw' : 'sanitize_text_field',
        ] );
        $wp_customize->add_control( $key, [
            'label'   => $label,
            'section' => 'flyrec_contact',
            'type'    => $type,
        ] );
    }
}
add_action( 'customize_register', 'flyrec_customize_register' );

// =============================================
// AJAX: KONTAKT FORMA
// =============================================
function flyrec_handle_contact() {
    check_ajax_referer( 'flyrec_contact_nonce', 'nonce' );

    $name       = sanitize_text_field( $_POST['name']       ?? '' );
    $email      = sanitize_email(      $_POST['email']      ?? '' );
    $phone      = sanitize_text_field( $_POST['phone']      ?? '' );
    $service    = sanitize_text_field( $_POST['service']    ?? '' );
    $message    = sanitize_textarea_field( $_POST['message'] ?? '' );

    if ( ! $name || ! $email || ! $message ) {
        wp_send_json_error( [ 'message' => __( 'Molimo popunite sva obavezna polja.', 'flyrec' ) ] );
    }

    if ( ! is_email( $email ) ) {
        wp_send_json_error( [ 'message' => __( 'Unesite ispravnu email adresu.', 'flyrec' ) ] );
    }

    $to      = get_theme_mod( 'flyrec_contact_email', get_option( 'admin_email' ) );
    /* translators: %s: sender's name */
    $subject = sprintf( __( 'Novi upit – %s', 'flyrec' ), $name );
    /* translators: 1: name, 2: email, 3: phone, 4: service, 5: message */
    $body    = sprintf(
        __( "Ime: %1\$s\nEmail: %2\$s\nTelefon: %3\$s\nVrsta usluge: %4\$s\n\nPoruka:\n%5\$s", 'flyrec' ),
        $name, $email, $phone, $service ?: '—', $message
    );
    $headers = [
        'Content-Type: text/plain; charset=UTF-8',
        "Reply-To: {$name} <{$email}>",
    ];

    if ( wp_mail( $to, $subject, $body, $headers ) ) {
        wp_send_json_success( [ 'message' => __( 'Hvala! Poruka je uspešno poslata. Javićemo Vam se uskoro.', 'flyrec' ) ] );
    } else {
        wp_send_json_error( [ 'message' => __( 'Greška pri slanju. Kontaktirajte nas direktno na email.', 'flyrec' ) ] );
    }
}
add_action( 'wp_ajax_flyrec_contact',        'flyrec_handle_contact' );
add_action( 'wp_ajax_nopriv_flyrec_contact', 'flyrec_handle_contact' );

// =============================================
// ACF PODRŠKA (ako je plugin instaliran)
// =============================================
if ( class_exists( 'ACF' ) && function_exists( 'acf_add_options_page' ) ) {
    acf_add_options_page( [
        'page_title' => __( 'FlyRec Podešavanja', 'flyrec' ),
        'menu_title' => __( 'FlyRec Settings', 'flyrec' ),
        'menu_slug'  => 'flyrec-settings',
        'capability' => 'manage_options',
    ] );
}

// =============================================
// ADMIN KOLONE ZA VIDEO CPT
// =============================================
function flyrec_video_columns( $columns ) {
    return [
        'cb'                => $columns['cb'],
        'title'             => __( 'Naslov',    'flyrec' ),
        'flyrec_thumb'      => __( 'Thumbnail', 'flyrec' ),
        'flyrec_video_type' => __( 'Platforma', 'flyrec' ),
        'flyrec_order'      => __( 'Redosled',  'flyrec' ),
        'date'              => __( 'Datum',      'flyrec' ),
    ];
}
add_filter( 'manage_flyrec_video_posts_columns', 'flyrec_video_columns' );

function flyrec_video_column_content( $column, $post_id ) {
    if ( 'flyrec_thumb' === $column ) {
        $thumb = get_the_post_thumbnail( $post_id, [ 80, 45 ] );
        echo $thumb ?: '<span style="color:#999;">' . esc_html__( 'Nema', 'flyrec' ) . '</span>';
    }
    if ( 'flyrec_video_type' === $column ) {
        $type = get_post_meta( $post_id, '_flyrec_video_type', true );
        $icons = [ 'youtube' => '▶️ YouTube', 'vimeo' => '🎞 Vimeo', 'instagram' => '📸 Instagram' ];
        echo esc_html( $icons[ $type ] ?? ucfirst( $type ) );
    }
    if ( 'flyrec_order' === $column ) {
        echo esc_html( get_post_meta( $post_id, '_flyrec_video_order', true ) ?: '0' );
    }
}
add_action( 'manage_flyrec_video_posts_custom_column', 'flyrec_video_column_content', 10, 2 );

// Helper: dohvati auto thumbnail za video URL bez uploadovane slike
// Redosled: YouTube CDN (bez API) → Vimeo public API (keširano) → '' (gradient fallback)
function flyrec_get_auto_thumbnail( $url, $type ) {
    if ( 'youtube' === $type ) {
        if ( preg_match( '/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/|shorts\/))([A-Za-z0-9_-]{11})/', $url, $m ) ) {
            // maxresdefault nije uvek dostupan za starije videe – hqdefault je sigurniji
            return 'https://img.youtube.com/vi/' . $m[1] . '/hqdefault.jpg';
        }
    }

    if ( 'vimeo' === $type ) {
        if ( preg_match( '/vimeo\.com\/(\d+)/', $url, $m ) ) {
            $cache_key = 'flyrec_vimeo_thumb_' . $m[1];
            $cached    = get_transient( $cache_key );
            if ( false !== $cached ) return $cached;

            $response = wp_remote_get(
                'https://vimeo.com/api/v2/video/' . $m[1] . '.json',
                [ 'timeout' => 5, 'sslverify' => true ]
            );
            $thumb = '';
            if ( ! is_wp_error( $response ) ) {
                $data = json_decode( wp_remote_retrieve_body( $response ), true );
                $thumb = $data[0]['thumbnail_large'] ?? $data[0]['thumbnail_medium'] ?? '';
            }
            // Kešira rezultat 7 dana (ili 1 dan ako nije pronađen)
            set_transient( $cache_key, $thumb, $thumb ? WEEK_IN_SECONDS : DAY_IN_SECONDS );
            return $thumb;
        }
    }

    // Instagram: oEmbed API zahteva autorizaciju od 2020 → ne pokušavamo
    return '';
}

// Helper: auto-detektuj tip videa iz URL-a (koristi se i u PHP i JS)
function flyrec_detect_video_type( $url ) {
    if ( preg_match( '/\.(mp4|webm|ogg)(\?|$)/i', $url ) ) return 'mp4';
    // Media Library uploads (nema ekstenzije u URL-u, ali je iz uploads foldera)
    if ( preg_match( '/\/wp-content\/uploads\//i', $url ) ) return 'mp4';
    if ( preg_match( '/(?:youtube\.com|youtu\.be)/i', $url ) ) return 'youtube';
    if ( preg_match( '/vimeo\.com/i', $url ) ) return 'vimeo';
    if ( preg_match( '/instagram\.com/i', $url ) ) return 'instagram';
    return 'external';
}

// =============================================
// SADRŽAJ PO JEZIKU – hero naslov/podnaslov/dugmad + naziv lokacije
// Namerno NIJE u Customizer-u: ovi tekstovi se razlikuju po jeziku
// (sr/en/ru), a Customizer čuva samo jednu vrednost za ceo sajt.
// Za izmenu teksta, uredi ovaj niz direktno.
// =============================================
function flyrec_get_i18n_content() {
    $lang = function_exists( 'pll_current_language' ) ? pll_current_language() : 'sr';

    $content = [
        'sr' => [
            'hero_title'    => 'Profesionalno snimanje dronom iz vazduha',
            'hero_subtitle' => 'Cinematic aerial video, fotografije i produkcija za brendove, evente, nekretnine i turizam.',
            'cta1_text'     => 'Pogledaj radove',
            'cta2_text'     => 'Zakaži snimanje',
            'location'      => 'Tivat, Crna Gora',
        ],
        'en' => [
            'hero_title'    => 'Professional aerial drone filming',
            'hero_subtitle' => 'Cinematic aerial video, photography and production for brands, events, real estate and tourism.',
            'cta1_text'     => 'View our work',
            'cta2_text'     => 'Book a shoot',
            'location'      => 'Tivat, Montenegro',
        ],
        'ru' => [
            'hero_title'    => 'Профессиональная аэросъёмка дроном',
            'hero_subtitle' => 'Кинематографичное аэровидео, фотография и продакшн для брендов, мероприятий, недвижимости и туризма.',
            'cta1_text'     => 'Смотреть работы',
            'cta2_text'     => 'Заказать съёмку',
            'location'      => 'Тиват, Черногория',
        ],
    ];

    return $content[ $lang ] ?? $content['sr'];
}

// =============================================
// ČIŠĆENJE WP HEAD
// =============================================
remove_action( 'wp_head', 'wp_generator' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'rsd_link' );
add_filter( 'the_generator', '__return_empty_string' );

// Isključi komentare (nije potrebno za drone studio sajt)
add_filter( 'comments_open', '__return_false', 20, 2 );
add_filter( 'pings_open',    '__return_false', 20, 2 );
add_action( 'admin_menu', function () {
    remove_menu_page( 'edit-comments.php' );
} );

// =============================================
// SPREČI NEPOTREBAN REDIRECT NA /en/home-2/ i sl.
// WP po defaultu preusmerava direktan pristup slug-u statične početne
// stranice na home_url() – ali kad Polylang menja page_on_front po jeziku
// (nema isti slug kao language home), WP zna pogrešno da odredi kanonski
// URL i umesto da ostane na /en/ preusmeri na /en/{slug-prevoda}/.
// =============================================
add_filter( 'redirect_canonical', function ( $redirect_url, $requested_url ) {
    if ( is_front_page() ) {
        return false;
    }
    return $redirect_url;
}, 10, 2 );
