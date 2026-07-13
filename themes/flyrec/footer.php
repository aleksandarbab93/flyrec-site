<!-- ===== FOOTER ===== -->
<footer class="site-footer">
    <div class="container">

        <div class="footer-grid">

            <!-- Brend kolona -->
            <div class="footer-brand">
                <?php $footer_logo = get_theme_mod('flyrec_logo_light', ''); ?>
                <a href="<?php echo esc_url(home_url('/')); ?>" class="footer-logo" aria-label="FlyRec">
                    <?php if ($footer_logo) : ?>
                        <img src="<?php echo esc_url($footer_logo); ?>" alt="<?php esc_attr_e( 'FlyRec', 'flyrec' ); ?>" class="footer-logo-img" height="32" loading="lazy">
                    <?php else : ?>
                        <span class="logo-text">
                            <span class="logo-fly">FLY</span><span class="logo-dot">•</span><span class="logo-rec">REC</span>
                        </span>
                    <?php endif; ?>
                </a>
                <p class="footer-tagline"><?php esc_html_e( 'Cinematic snimanje dronom.', 'flyrec' ); ?><br><?php esc_html_e( 'Vaš svet iz nove perspektive.', 'flyrec' ); ?></p>
                <div class="footer-social">
                    <?php $ig = get_theme_mod('flyrec_contact_instagram', 'https://instagram.com/flyrec'); ?>
                    <a href="<?php echo esc_url($ig); ?>" class="social-link" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e( 'Instagram', 'flyrec' ); ?>">
                        <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="2" y="2" width="20" height="20" rx="5" />
                            <circle cx="12" cy="12" r="4.5" />
                            <circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none" />
                        </svg>
                    </a>
                    <a href="https://www.youtube.com/@flyrec001" class="social-link" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e( 'YouTube', 'flyrec' ); ?>">
                        <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M22.54 6.42a2.78 2.78 0 00-1.95-1.95C18.88 4 12 4 12 4s-6.88 0-8.59.47a2.78 2.78 0 00-1.95 1.95C1 8.12 1 12 1 12s0 3.88.46 5.58a2.78 2.78 0 001.95 1.95C5.12 20 12 20 12 20s6.88 0 8.59-.47a2.78 2.78 0 001.95-1.95C23 15.88 23 12 23 12s0-3.88-.46-5.58z" />
                            <polygon points="9.75 15.02 15.5 12 9.75 8.98 9.75 15.02" fill="currentColor" stroke="none" />
                        </svg>
                    </a>
                    <a href="https://www.facebook.com/flyrec001" class="social-link" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e( 'Facebook', 'flyrec' ); ?>">
                        <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z" />
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Navigacija -->
            <div class="footer-nav">
                <h4 class="footer-heading"><?php esc_html_e( 'Navigacija', 'flyrec' ); ?></h4>
                <ul class="footer-links">
                    <li><a href="#hero"><?php esc_html_e( 'Početna',    'flyrec' ); ?></a></li>
                    <li><a href="#portfolio"><?php esc_html_e( 'Video radovi', 'flyrec' ); ?></a></li>
                    <li><a href="#usluge"><?php esc_html_e( 'Usluge',      'flyrec' ); ?></a></li>
                    <li><a href="#o-nama"><?php esc_html_e( 'O nama',     'flyrec' ); ?></a></li>
                    <li><a href="#portfolio"><?php esc_html_e( 'Portfolio', 'flyrec' ); ?></a></li>
                    <li><a href="#kontakt"><?php esc_html_e( 'Kontakt',     'flyrec' ); ?></a></li>
                </ul>
            </div>

            <!-- Usluge -->
            <div class="footer-services">
                <h4 class="footer-heading"><?php esc_html_e( 'Usluge', 'flyrec' ); ?></h4>
                <ul class="footer-links">
                    <li><?php esc_html_e( 'Snimanje nekretnina',         'flyrec' ); ?></li>
                    <li><?php esc_html_e( 'Snimanje venčanja',           'flyrec' ); ?></li>
                    <li><?php esc_html_e( 'Turistički video',            'flyrec' ); ?></li>
                    <li><?php esc_html_e( 'Sadržaj za socijalne mreže', 'flyrec' ); ?></li>
                    <li><?php esc_html_e( 'Inspekcije objekata',         'flyrec' ); ?></li>
                </ul>
            </div>

            <!-- Kontakt -->
            <div class="footer-contact">
                <h4 class="footer-heading"><?php esc_html_e( 'Kontakt', 'flyrec' ); ?></h4>
                <ul class="footer-contact-list">
                    <?php $phone = get_theme_mod('flyrec_contact_phone', '+381 60 000 0000'); ?>
                    <?php $email = get_theme_mod('flyrec_contact_email', 'info@flyrec.rs'); ?>
                    <?php $location = get_theme_mod('flyrec_contact_location', 'Beograd, Srbija'); ?>

                    <li>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 8.8a19.79 19.79 0 01-3.07-8.7A2 2 0 012.18 2h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.91 9.91a16 16 0 006.18 6.18l1.28-1.28a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z" />
                        </svg>
                        <a href="tel:<?php echo esc_attr(preg_replace('/\s/', '', $phone)); ?>">
                            <?php echo esc_html($phone); ?>
                        </a>
                    </li>
                    <li>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                            <polyline points="22 6 12 13 2 6" />
                        </svg>
                        <a href="mailto:<?php echo esc_attr($email); ?>">
                            <?php echo esc_html($email); ?>
                        </a>
                    </li>
                    <li>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z" />
                            <circle cx="12" cy="10" r="3" />
                        </svg>
                        <span><?php echo esc_html($location); ?></span>
                    </li>
                </ul>
            </div>

        </div><!-- /footer-grid -->

        <div class="footer-bottom">
            <p class="footer-copy">
                &copy; <?php echo date('Y'); ?>
                <a href="<?php echo esc_url(home_url('/')); ?>">FlyRec Studio</a>.
                <?php esc_html_e( 'Sva prava zadržana.', 'flyrec' ); ?>
            </p>
            <p class="footer-credit">
                <?php esc_html_e( 'Profesionalno snimanje dronom', 'flyrec' ); ?> &mdash; <?php echo esc_html(get_theme_mod('flyrec_contact_location', __( 'Beograd, Srbija', 'flyrec' ))); ?>
            </p>
        </div>

    </div>
</footer>
<!-- /FOOTER -->

<?php wp_footer(); ?>
</body>

</html>