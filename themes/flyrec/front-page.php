<?php

/**
 * Front Page Template – Glavni landing page
 * Aktivira se kada je postavljen "Statična početna stranica" u Settings → Reading
 * ili automatski kao tema homepage.
 */
get_header();

// Customizer vrednosti (sa fallback tekstovima)
$hero_bg_type  = get_theme_mod('flyrec_hero_bg_type',    'video');
$hero_video    = get_theme_mod('flyrec_hero_video_url',  '');
$hero_image    = get_theme_mod('flyrec_hero_image_url',  '');

// Video ima prioritet ako je postavljen; ako nema videa, koristi se slika (fallback)
$hero_video_type = $hero_video ? flyrec_detect_video_type($hero_video) : '';
$show_hero_video = ($hero_bg_type !== 'image' && $hero_video);
$show_hero_image = (!$show_hero_video && $hero_image);

// Hero naslov/podnaslov/dugmad + naziv lokacije zavise od jezika – vidi functions.php
$i18n_content  = flyrec_get_i18n_content();
$hero_title    = $i18n_content['hero_title'];
$hero_subtitle = $i18n_content['hero_subtitle'];
$cta1_text     = $i18n_content['cta1_text'];
$cta2_text     = $i18n_content['cta2_text'];

$contact_email = get_theme_mod('flyrec_contact_email',     'info@flyrec.rs');
$contact_ig    = get_theme_mod('flyrec_contact_instagram', 'https://instagram.com/flyrec');
$contact_loc   = $i18n_content['location'];
$youtube_channel = get_theme_mod('flyrec_youtube_channel', 'https://www.youtube.com/@flyrec001');
?>

<main id="mainContent">

    <!-- =========================================
         1. HERO SEKCIJA – Full-screen video pozadina
         ========================================= -->
    <section class="hero" id="hero">

        <!-- Pozadina: video (MP4 ili YouTube) ili slika (podesi u Customizer → Hero Sekcija) -->
        <?php if ($show_hero_video && $hero_video_type === 'youtube') :
            $hero_youtube_embed = flyrec_get_hero_youtube_embed($hero_video);
            if ($hero_youtube_embed) : ?>
                <div class="hero-youtube-wrapper" aria-hidden="true">
                    <iframe
                        src="<?php echo esc_url($hero_youtube_embed); ?>"
                        title=""
                        frameborder="0"
                        allow="autoplay; encrypted-media"
                        tabindex="-1"
                        aria-hidden="true"></iframe>
                </div>
            <?php elseif ($hero_image) : ?>
                <div class="hero-image-wrapper" aria-hidden="true">
                    <img
                        src="<?php echo esc_url($hero_image); ?>"
                        alt=""
                        class="hero-image"
                        loading="eager"
                        decoding="async">
                </div>
            <?php endif;
        elseif ($show_hero_video) : ?>
            <div class="hero-video-wrapper">
                <video
                    class="hero-video"
                    autoplay
                    muted
                    loop
                    playsinline
                    preload="none"
                    aria-hidden="true">
                    <source src="<?php echo esc_url($hero_video); ?>" type="video/mp4">
                </video>
            </div>
        <?php elseif ($show_hero_image) : ?>
            <div class="hero-image-wrapper" aria-hidden="true">
                <img
                    src="<?php echo esc_url($hero_image); ?>"
                    alt=""
                    class="hero-image"
                    loading="eager"
                    decoding="async">
            </div>
        <?php endif; ?>

        <!-- Dark overlay za čitljivost teksta -->
        <div class="hero-overlay" aria-hidden="true"></div>

        <!-- Sadržaj -->
        <div class="hero-content">
            <div class="hero-badge fade-up">
                <span class="hero-badge-dot" aria-hidden="true"></span>
                <span><?php esc_html_e('Profesionalni Drone Studio', 'flyrec'); ?></span>
            </div>

            <!-- Promeni naslov u Customizer → Hero Sekcija → Glavni naslov -->
            <h1 class="hero-title fade-up delay-1">
                <?php echo esc_html($hero_title); ?>
            </h1>

            <!-- Promeni podnaslov u Customizer → Hero Sekcija → Podnaslov -->
            <p class="hero-subtitle fade-up delay-2">
                <?php echo esc_html($hero_subtitle); ?>
            </p>

            <div class="hero-cta fade-up delay-3">
                <!-- Promeni tekst dugmadi u Customizer → Hero Sekcija -->
                <a href="#portfolio" class="btn btn--primary"><?php echo esc_html($cta1_text); ?></a>
                <a href="#kontakt" class="btn btn--outline"><?php echo esc_html($cta2_text); ?></a>
            </div>
        </div>

        <!-- Scroll indikator -->
        <div class="hero-scroll-indicator" aria-hidden="true">
            <span><?php esc_html_e('Skroluj', 'flyrec'); ?></span>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <polyline points="6 9 12 15 18 9" />
            </svg>
        </div>

    </section>
    <!-- /HERO -->


    <!-- =========================================
         2. PORTFOLIO FEED – Instagram Reels grid
         ========================================= -->
    <section class="section section--portfolio-feed" id="portfolio">
        <div class="container">

            <div class="section-header fade-up">
                <span class="section-label"><?php esc_html_e('Iz vazduha', 'flyrec'); ?></span>
                <h2 class="section-title"><?php esc_html_e('Najnoviji snimci', 'flyrec'); ?></h2>
                <p class="section-subtitle">
                    <?php esc_html_e('Izbor naših najnovijih snimaka i trenutaka zabeleženih iz vazduha.', 'flyrec'); ?>
                </p>
            </div>

            <?php echo do_shortcode('[flyrec_instagram_feed]'); ?>

        </div>
    </section>
    <!-- /PORTFOLIO -->


    <!-- =========================================
         3. USLUGE – Kartice sa ikonama i hover efektom
         ========================================= -->
    <section class="section" id="usluge">
        <div class="container">

            <div class="section-header fade-up">
                <span class="section-label"><?php esc_html_e('Šta nudimo', 'flyrec'); ?></span>
                <h2 class="section-title"><?php esc_html_e('Naše usluge', 'flyrec'); ?></h2>
                <p class="section-subtitle">
                    <?php esc_html_e('Specijalizovani smo za sve vrste aerial snimanja dronom, od komercijalnih projekata do ličnih eventi.', 'flyrec'); ?>
                </p>
            </div>

            <!-- 3 kartice usluga – promijeni tekst direktno ovde -->
            <div class="services-grid">

                <!-- 1. DOGAĐAJI -->
                <div class="service-card fade-up delay-1">
                    <div class="service-icon" aria-hidden="true">
                        <!-- Video kamera (camcorder): tijelo + objektiv + bočni panel + REC indikator -->
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="1.5"
                            stroke-linecap="round" stroke-linejoin="round"
                            xmlns="http://www.w3.org/2000/svg">
                            <rect x="2" y="7.5" width="14" height="10" rx="2" />
                            <circle cx="9" cy="12.5" r="3" />
                            <circle cx="9" cy="12.5" r="1.2" />
                            <path d="M16 9.5L22 7V18L16 15.5" />
                            <circle cx="13.5" cy="9.5" r="0.9" fill="currentColor" stroke="none" />
                        </svg>
                    </div>
                    <h3 class="service-title"><?php esc_html_e('Događaji', 'flyrec'); ?></h3>
                    <p class="service-desc">
                        <?php esc_html_e('Profesionalno snimanje i fotografisanje proslava, poslovnih događaja, manifestacija i posebnih trenutaka — video i fotografije koje prenose atmosferu i najvažnije trenutke.', 'flyrec'); ?>
                    </p>
                </div>

                <!-- 2. NEKRETNINE -->
                <div class="service-card fade-up delay-2">
                    <div class="service-icon" aria-hidden="true">
                        <!-- Zgrada iz aerial perspektive (3 vidljive strane) + prozori + vrata -->
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="1.5"
                            stroke-linecap="round" stroke-linejoin="round"
                            xmlns="http://www.w3.org/2000/svg">
                            <rect x="2" y="8" width="12" height="13" />
                            <path d="M2 8L6 4H18L14 8" />
                            <path d="M14 21H18V4" />
                            <rect x="4.5" y="11" width="2.5" height="2.5" rx="0.4" />
                            <rect x="9" y="11" width="2.5" height="2.5" rx="0.4" />
                            <rect x="4.5" y="16" width="3" height="5" rx="0.4" />
                        </svg>
                    </div>
                    <h3 class="service-title"><?php esc_html_e('Nekretnine', 'flyrec'); ?></h3>
                    <p class="service-desc">
                        <?php esc_html_e('Profesionalna prezentacija stanova, kuća, apartmana, poslovnih prostora i drugih nekretnina.', 'flyrec'); ?>
                    </p>
                </div>

                <!-- 3. PEJZAŽI -->
                <div class="service-card fade-up delay-3">
                    <div class="service-icon" aria-hidden="true">
                        <!-- Pejzaž: planine + sunce -->
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="1.5"
                            stroke-linecap="round" stroke-linejoin="round"
                            xmlns="http://www.w3.org/2000/svg">
                            <circle cx="18" cy="6.5" r="2.2" />
                            <path d="M2 19l6.5-9 4.5 6 2.5-3.5L21 19z" />
                            <path d="M2 19h20" />
                        </svg>
                    </div>
                    <h3 class="service-title"><?php esc_html_e('Pejzaži', 'flyrec'); ?></h3>
                    <p class="service-desc">
                        <?php esc_html_e('Filmski video i fotografski prikazi prirode, turističkih lokacija i različitih destinacija.', 'flyrec'); ?>
                    </p>
                </div>

                <!-- 4. MONTAŽA I OBRADA -->
                <div class="service-card fade-up delay-4">
                    <div class="service-icon" aria-hidden="true">
                        <!-- Makaze: simbol montaže/sečenja materijala -->
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="1.5"
                            stroke-linecap="round" stroke-linejoin="round"
                            xmlns="http://www.w3.org/2000/svg">
                            <circle cx="6" cy="6" r="3" />
                            <circle cx="6" cy="18" r="3" />
                            <line x1="20" y1="4" x2="8.12" y2="15.88" />
                            <line x1="14.47" y1="14.48" x2="20" y2="20" />
                            <line x1="8.12" y1="8.12" x2="12" y2="12" />
                        </svg>
                    </div>
                    <h3 class="service-title"><?php esc_html_e('Montaža i obrada', 'flyrec'); ?></h3>
                    <p class="service-desc">
                        <?php esc_html_e('Montiranje promotivnih videa i obrada snimljenog materijala — od sirovog snimka do gotovog proizvoda spremnog za objavu.', 'flyrec'); ?>
                    </p>
                </div>

            </div><!-- /services-grid -->

        </div>
    </section>
    <!-- /USLUGE -->


    <!-- =========================================
         VIDEO RADOVI – YouTube embed grid (16:9)
         ========================================= -->
    <section class="section section--dark" id="video-radovi">
        <div class="container">

            <div class="section-header fade-up">
                <span class="section-label"><?php esc_html_e('YouTube', 'flyrec'); ?></span>
                <h2 class="section-title"><?php esc_html_e('Video radovi', 'flyrec'); ?></h2>
                <p class="section-subtitle">
                    <?php esc_html_e('Izabrane video produkcije sa našeg YouTube kanala.', 'flyrec'); ?>
                </p>
            </div>

            <?php
            $yt_videos = new WP_Query([
                'post_type'      => 'flyrec_video',
                'posts_per_page' => 3,
                'post_status'    => 'publish',
                'orderby'        => 'meta_value_num',
                'meta_key'       => '_flyrec_video_order',
                'order'          => 'ASC',
            ]);
            ?>

            <?php if ($yt_videos->have_posts()) : ?>
                <div class="videos-grid">
                    <?php while ($yt_videos->have_posts()) : $yt_videos->the_post(); ?>
                        <?php
                        $video_url  = get_post_meta(get_the_ID(), '_flyrec_video_url', true);
                        $video_type = get_post_meta(get_the_ID(), '_flyrec_video_type', true) ?: 'youtube';
                        $embed_url  = flyrec_get_embed_url($video_url, $video_type);
                        ?>
                        <article class="video-card fade-up">
                            <div class="video-embed-wrapper" data-src="<?php echo esc_attr($embed_url); ?>">
                                <?php
                                $vthumb = has_post_thumbnail()
                                    ? get_the_post_thumbnail_url(null, 'flyrec-thumb')
                                    : flyrec_get_auto_thumbnail($video_url, $video_type);
                                ?>
                                <?php if ($vthumb) : ?>
                                    <img
                                        class="video-thumb"
                                        src="<?php echo esc_url($vthumb); ?>"
                                        alt="<?php the_title_attribute(); ?>"
                                        loading="lazy">
                                <?php else : ?>
                                    <div class="video-thumb-placeholder"></div>
                                <?php endif; ?>
                                <button class="video-play-btn" aria-label="<?php esc_attr_e('Pokreni video', 'flyrec'); ?>">
                                    <span class="play-icon-wrap" aria-hidden="true">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                            <polygon points="6 3 20 12 6 21" />
                                        </svg>
                                    </span>
                                </button>
                            </div>
                        </article>
                    <?php endwhile;
                    wp_reset_postdata(); ?>
                </div><!-- /videos-grid -->
            <?php elseif (current_user_can('manage_options')) : ?>
                <p class="placeholder-notice">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="12" r="10" />
                        <path d="M12 16v-4" />
                        <path d="M12 8h.01" />
                    </svg>
                    <em><?php printf(__('Admin: Dodajte video radove kroz <strong>%s</strong> u WordPress adminu. (Ova poruka je vidljiva samo administratorima.)', 'flyrec'), 'Video Radovi → Dodaj snimak'); ?></em>
                </p>
            <?php endif; ?>

            <?php if ($youtube_channel) : ?>
                <div class="youtube-cta fade-up">
                    <a href="<?php echo esc_url($youtube_channel); ?>"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="btn btn--yt">
                        <?php esc_html_e('Pogledaj YouTube kanal', 'flyrec'); ?>
                    </a>
                </div>
            <?php endif; ?>

        </div>
    </section>
    <!-- /VIDEO RADOVI -->


    <!-- =========================================
         O NAMA
         ========================================= -->
    <section class="section section--dark section--about" id="o-nama">
        <div class="container">
            <div class="about-wrapper fade-up">
                <span class="section-label"><?php esc_html_e('Ko smo mi', 'flyrec'); ?></span>
                <h2 class="section-title"><?php esc_html_e('O Flyrec-u', 'flyrec'); ?></h2>
                <p class="about-text">
                    <?php esc_html_e('Flyrec kroz fotografiju i video pretvara trenutke, prostore i pejzaže u vizuelne priče. Fokusirani smo na kvalitet, kreativnost i sadržaj prilagođen savremenim digitalnim platformama.', 'flyrec'); ?>
                </p>
            </div>
        </div>
    </section>
    <!-- /O NAMA -->


    <!-- =========================================
         5. KONTAKT SEKCIJA
         ========================================= -->
    <section class="section section--dark section--contact" id="kontakt">
        <div class="container">

            <div class="contact-wrapper">

                <!-- Leva strana: CTA tekst + info -->
                <div class="contact-info fade-up">
                    <span class="section-label"><?php esc_html_e('Hajde da sarađujemo', 'flyrec'); ?></span>
                    <h2 class="section-title">
                        <?php esc_html_e('Spreman za snimanje', 'flyrec'); ?><br><?php esc_html_e('iz nove perspektive?', 'flyrec'); ?>
                    </h2>
                    <p class="contact-intro">
                        <?php esc_html_e('Svaki projekat je jedinstven. Pišite nam i zajednički ćemo kreirati nešto što će ostaviti utisak.', 'flyrec'); ?>
                    </p>

                    <ul class="contact-details">
                        <li>
                            <div class="contact-icon" aria-hidden="true">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                                    <polyline points="22 6 12 13 2 6" />
                                </svg>
                            </div>
                            <div>
                                <span class="contact-label"><?php esc_html_e('Email', 'flyrec'); ?></span>
                                <a href="mailto:<?php echo esc_attr($contact_email); ?>" class="contact-value">
                                    <?php echo esc_html($contact_email); ?>
                                </a>
                            </div>
                        </li>
                        <li>
                            <div class="contact-icon" aria-hidden="true">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="2" y="2" width="20" height="20" rx="5" />
                                    <circle cx="12" cy="12" r="4.5" />
                                    <circle cx="17.5" cy="6.5" r="0.5" fill="currentColor" stroke="none" />
                                </svg>
                            </div>
                            <div>
                                <span class="contact-label"><?php esc_html_e('Instagram', 'flyrec'); ?></span>
                                <a href="<?php echo esc_url($contact_ig); ?>" class="contact-value" target="_blank" rel="noopener noreferrer">
                                    @flyrec_
                                </a>
                            </div>
                        </li>
                        <li>
                            <div class="contact-icon" aria-hidden="true">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z" />
                                    <circle cx="12" cy="10" r="3" />
                                </svg>
                            </div>
                            <div>
                                <span class="contact-label"><?php esc_html_e('Lokacija', 'flyrec'); ?></span>
                                <span class="contact-value"><?php echo esc_html($contact_loc); ?></span>
                            </div>
                        </li>
                    </ul>
                </div><!-- /contact-info -->

                <!-- Desna strana: Kontakt / rezervacija forma -->
                <div class="contact-form-wrapper fade-up delay-1">
                    <form id="contactForm" novalidate>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="cf_name"><?php esc_html_e('Ime i prezime', 'flyrec'); ?> <span class="required">*</span></label>
                                <input type="text" id="cf_name" name="name" required placeholder="<?php esc_attr_e('Vaše ime', 'flyrec'); ?>">
                            </div>
                            <div class="form-group">
                                <label for="cf_email"><?php esc_html_e('Email adresa', 'flyrec'); ?> <span class="required">*</span></label>
                                <input type="email" id="cf_email" name="email" required placeholder="ime@email.com">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="cf_phone"><?php esc_html_e('Broj telefona', 'flyrec'); ?></label>
                                <input type="tel" id="cf_phone" name="phone" placeholder="+382 6X XXX XXX">
                            </div>
                            <div class="form-group">
                                <label for="cf_service"><?php esc_html_e('Vrsta usluge', 'flyrec'); ?></label>
                                <select id="cf_service" name="service">
                                    <option value="Snimanje događaja"><?php esc_html_e('Snimanje događaja', 'flyrec'); ?></option>
                                    <option value="Fotografisanje događaja"><?php esc_html_e('Fotografisanje događaja', 'flyrec'); ?></option>
                                    <option value="Snimanje nekretnina"><?php esc_html_e('Snimanje nekretnina', 'flyrec'); ?></option>
                                    <option value="Fotografisanje nekretnina"><?php esc_html_e('Fotografisanje nekretnina', 'flyrec'); ?></option>
                                    <option value="Snimanje pejzaža"><?php esc_html_e('Snimanje pejzaža', 'flyrec'); ?></option>
                                    <option value="Drugo"><?php esc_html_e('Drugo', 'flyrec'); ?></option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="cf_message"><?php esc_html_e('Poruka i dodatne informacije', 'flyrec'); ?> <span class="required">*</span></label>
                            <textarea id="cf_message" name="message" rows="4" required placeholder="<?php esc_attr_e('Recite nam nešto više o projektu...', 'flyrec'); ?>"></textarea>
                        </div>

                        <button type="submit" id="submitBtn" class="btn btn--primary btn--full">
                            <span class="btn-text"><?php esc_html_e('Pošaljite upit', 'flyrec'); ?></span>
                            <span class="btn-loading" style="display:none;"><?php esc_html_e('Slanje...', 'flyrec'); ?></span>
                        </button>

                        <div class="form-message" id="formMessage"></div>
                    </form>
                </div><!-- /contact-form-wrapper -->

            </div><!-- /contact-wrapper -->

        </div>
    </section>
    <!-- /KONTAKT -->

</main>

<!-- =========================================
     LIGHTBOX MODAL (za portfolio galeriju)
     ========================================= -->
<div class="lightbox-modal" id="lightboxModal" aria-modal="true" role="dialog" aria-label="<?php esc_attr_e('Galerija prikaz', 'flyrec'); ?>" aria-hidden="true">
    <button class="lightbox-close" id="lightboxClose" aria-label="<?php esc_attr_e('Zatvori', 'flyrec'); ?>">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
            <line x1="18" y1="6" x2="6" y2="18" />
            <line x1="6" y1="6" x2="18" y2="18" />
        </svg>
    </button>
    <div class="lightbox-content" id="lightboxContent">

        <!-- Prikaz slike -->
        <img class="lightbox-img" id="lightboxImg" src="" alt="" style="display:none;">

        <!-- MP4 video prikaz -->
        <div class="lightbox-video-wrapper" id="lightboxVideoWrapper" style="display:none;">
            <video class="lightbox-video" id="lightboxVideo" controls playsinline preload="metadata">
                <source id="lightboxVideoSrc" src="" type="video/mp4">
            </video>
        </div>

        <!-- YouTube / Vimeo iframe prikaz -->
        <div class="lightbox-iframe-wrapper" id="lightboxIframeWrapper" style="display:none;">
            <iframe id="lightboxIframe" src="" frameborder="0" allowfullscreen
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                loading="lazy"></iframe>
        </div>

        <div class="lightbox-caption" id="lightboxCaption"></div>
    </div>
</div>

<?php get_footer(); ?>