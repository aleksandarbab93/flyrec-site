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
$hero_title    = get_theme_mod('flyrec_hero_title',      'Profesionalno snimanje dronom iz vazduha');
$hero_subtitle = get_theme_mod('flyrec_hero_subtitle',   'Cinematic aerial video, fotografije i produkcija za brendove, evente, nekretnine i turizam.');
$cta1_text     = get_theme_mod('flyrec_cta1_text',       'Pogledaj radove');
$cta2_text     = get_theme_mod('flyrec_cta2_text',       'Zakaži snimanje');

$contact_phone = get_theme_mod('flyrec_contact_phone',     '+381 60 000 0000');
$contact_email = get_theme_mod('flyrec_contact_email',     'info@flyrec.rs');
$contact_ig    = get_theme_mod('flyrec_contact_instagram', 'https://instagram.com/flyrec');
$contact_loc   = get_theme_mod('flyrec_contact_location',  'Beograd, Srbija');
?>

<main id="mainContent">

    <!-- =========================================
         1. HERO SEKCIJA – Full-screen video pozadina
         ========================================= -->
    <section class="hero" id="hero">

        <!-- Pozadina: video ili slika (podesi u Customizer → Hero Sekcija) -->
        <?php if ($hero_bg_type === 'image' && $hero_image) : ?>
            <div class="hero-image-wrapper" aria-hidden="true">
                <img
                    src="<?php echo esc_url($hero_image); ?>"
                    alt=""
                    class="hero-image"
                    loading="eager"
                    decoding="async">
            </div>
        <?php elseif ($hero_bg_type !== 'image' && $hero_video) : ?>
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
                <span class="section-label"><?php esc_html_e( 'Iz vazduha', 'flyrec' ); ?></span>
                <h2 class="section-title"><?php esc_html_e( 'Najnoviji snimci', 'flyrec' ); ?></h2>
                <p class="section-subtitle">
                    <?php esc_html_e( 'Izbor naših najnovijih snimaka i trenutaka zabeleženih iz vazduha.', 'flyrec' ); ?>
                </p>
            </div>

            <?php echo do_shortcode( '[flyrec_instagram_feed]' ); ?>

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
                                    <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 8.8a19.79 19.79 0 01-3.07-8.7A2 2 0 012.18 2h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.91 9.91a16 16 0 006.18 6.18l1.28-1.28a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z" />
                                </svg>
                            </div>
                            <div>
                                <span class="contact-label"><?php esc_html_e('Telefon', 'flyrec'); ?></span>
                                <a href="tel:<?php echo esc_attr(preg_replace('/\s/', '', $contact_phone)); ?>" class="contact-value">
                                    <?php echo esc_html($contact_phone); ?>
                                </a>
                            </div>
                        </li>
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
                                    @flyrec
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

                    <?php $whatsapp_digits = preg_replace('/[^0-9]/', '', $contact_phone); ?>
                    <?php if ($whatsapp_digits) : ?>
                        <a href="https://wa.me/<?php echo esc_attr($whatsapp_digits); ?>"
                            class="btn btn--whatsapp"
                            target="_blank"
                            rel="noopener noreferrer">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M17.47 14.38c-.29-.15-1.73-.85-2-.95-.27-.1-.46-.15-.66.15-.2.29-.76.95-.93 1.14-.17.2-.34.22-.63.07-.29-.14-1.22-.45-2.32-1.43-.86-.76-1.44-1.71-1.6-2-.17-.29-.02-.44.13-.59.13-.13.29-.34.44-.51.15-.17.2-.29.29-.49.1-.2.05-.37-.02-.51-.08-.15-.66-1.58-.9-2.17-.24-.57-.48-.5-.66-.5-.17-.01-.37-.01-.56-.01-.2 0-.51.07-.78.37-.27.29-1.02 1-1.02 2.43 0 1.43 1.05 2.82 1.19 3.01.15.2 2.06 3.15 5 4.42.7.3 1.25.48 1.67.61.7.22 1.34.19 1.85.12.56-.08 1.73-.71 1.98-1.39.24-.68.24-1.27.17-1.39-.07-.12-.26-.2-.55-.34z" />
                                <path d="M12 2C6.48 2 2 6.48 2 12c0 1.85.5 3.58 1.36 5.07L2 22l5.06-1.33A9.94 9.94 0 0012 22c5.52 0 10-4.48 10-10S17.52 2 12 2zm0 18.15A8.14 8.14 0 013.85 12 8.15 8.15 0 1112 20.15z" />
                            </svg>
                            <?php esc_html_e('Piši nam na WhatsApp', 'flyrec'); ?>
                        </a>
                    <?php endif; ?>
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