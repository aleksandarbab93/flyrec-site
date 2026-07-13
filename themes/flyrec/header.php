<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#080808">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <?php wp_head(); ?>
    <!-- Anti-flash script: tema se primenjuje PRE renderovanja, nema treptanja -->
    <script>
    (function(){
        var t = localStorage.getItem('flyrec_theme') || 'light';
        document.documentElement.setAttribute('data-theme', t);
    })();
    </script>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<!-- ===== NAVIGACIJA ===== -->
<header class="site-header" id="siteHeader">
    <div class="container header-inner">

        <!-- Logo – dual sistem: light logo (bijeli) i dark logo (tamni) -->
        <div class="site-logo">
            <?php
            // ═══════════════════════════════════════════════════════════════
            // LOGO URL-OVI – postavi u: Customizer → 🖼 Logo Slike
            // Ili direktno zamijeni '' sa URL-om iz Media Library, npr:
            // 'https://mojsajt.com/wp-content/uploads/logo-white.png'
            $logo_light = get_theme_mod( 'flyrec_logo_light', '' ); // Bijeli PNG
            $logo_dark  = get_theme_mod( 'flyrec_logo_dark',  '' ); // Tamni PNG
            // ═══════════════════════════════════════════════════════════════
            ?>

            <!-- BIJELI LOGO – prikazuje se: transparent header + uvijek u dark temi -->
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>"
               class="logo-home-link logo-version logo-version--light"
               aria-label="<?php esc_attr_e( 'FlyRec – Početna', 'flyrec' ); ?>">
                <?php if ( $logo_light ) : ?>
                    <img src="<?php echo esc_url( $logo_light ); ?>"
                         alt="FlyRec"
                         class="logo-img"
                         height="36"
                         width="auto"
                         loading="eager">
                <?php else : ?>
                    <span class="logo-text">
                        <span class="logo-fly">FLY</span><span class="logo-dot">•</span><span class="logo-rec">REC</span>
                    </span>
                <?php endif; ?>
            </a>

            <!-- TAMNI LOGO – prikazuje se: scrolled white header u light temi -->
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>"
               class="logo-home-link logo-version logo-version--dark"
               aria-label="<?php esc_attr_e( 'FlyRec – Početna', 'flyrec' ); ?>">
                <?php if ( $logo_dark ) : ?>
                    <img src="<?php echo esc_url( $logo_dark ); ?>"
                         alt="FlyRec"
                         class="logo-img"
                         height="36"
                         width="auto"
                         loading="eager">
                <?php else : ?>
                    <span class="logo-text logo-text--inverted">
                        <span class="logo-fly">FLY</span><span class="logo-dot">•</span><span class="logo-rec">REC</span>
                    </span>
                <?php endif; ?>
            </a>
        </div>

        <!-- Desktop navigacija -->
        <nav class="main-nav" aria-label="<?php esc_attr_e( 'Primarna navigacija', 'flyrec' ); ?>">
            <ul class="nav-list">
                <li><a href="#hero"    class="nav-link"><?php esc_html_e( 'Početna', 'flyrec' ); ?></a></li>
                <li><a href="#portfolio"  class="nav-link"><?php esc_html_e( 'Radovi',  'flyrec' ); ?></a></li>
                <li><a href="#usluge"  class="nav-link"><?php esc_html_e( 'Usluge',  'flyrec' ); ?></a></li>
                <li><a href="#o-nama"  class="nav-link"><?php esc_html_e( 'O nama',  'flyrec' ); ?></a></li>
                <li><a href="#kontakt" class="nav-link nav-link--cta"><?php esc_html_e( 'Kontakt', 'flyrec' ); ?></a></li>
            </ul>
        </nav>

        <!-- Theme switcher – sun/moon ikona -->
        <button
            class="theme-toggle"
            id="themeToggle"
            aria-label="<?php esc_attr_e( 'Pređi na svetlu temu', 'flyrec' ); ?>"
            title="<?php esc_attr_e( 'Promeni temu', 'flyrec' ); ?>"
        >
            <span class="icon-sun" aria-hidden="true">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="4"/>
                    <line x1="12" y1="2" x2="12" y2="4"/><line x1="12" y1="20" x2="12" y2="22"/>
                    <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
                    <line x1="2" y1="12" x2="4" y2="12"/><line x1="20" y1="12" x2="22" y2="12"/>
                    <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
                </svg>
            </span>
            <span class="icon-moon" aria-hidden="true">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/>
                </svg>
            </span>
        </button>

        <!-- Hamburger dugme (mobilni) -->
        <button class="hamburger" id="hamburger" aria-label="<?php esc_attr_e( 'Otvori meni', 'flyrec' ); ?>" aria-expanded="false">
            <span></span>
            <span></span>
            <span></span>
        </button>

    </div>

    <!-- Mobilni meni -->
    <nav class="mobile-nav" id="mobileNav" aria-hidden="true">
        <ul class="mobile-nav-list">
            <li><a href="#hero"    class="mobile-nav-link"><?php esc_html_e( 'Početna', 'flyrec' ); ?></a></li>
            <li><a href="#portfolio"  class="mobile-nav-link"><?php esc_html_e( 'Radovi',  'flyrec' ); ?></a></li>
            <li><a href="#usluge"  class="mobile-nav-link"><?php esc_html_e( 'Usluge',  'flyrec' ); ?></a></li>
            <li><a href="#o-nama"  class="mobile-nav-link"><?php esc_html_e( 'O nama',  'flyrec' ); ?></a></li>
            <li><a href="#kontakt" class="mobile-nav-link mobile-nav-link--cta"><?php esc_html_e( 'Kontakt', 'flyrec' ); ?></a></li>
        </ul>
    </nav>
</header>
<!-- /NAVIGACIJA -->
