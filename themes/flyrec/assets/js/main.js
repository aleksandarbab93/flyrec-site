/**
 * FlyRec – Glavni JavaScript
 * Navigacija, animacije, video lazy load, kontakt forma, lightbox, theme toggle
 */

(function () {
    'use strict';

    // =============================================
    // DOM READY
    // =============================================
    document.addEventListener('DOMContentLoaded', function () {
        initNavigation();
        initThemeToggle();
        initScrollAnimations();
        initVideoLazyLoad();
        initContactForm();
        initPortfolioLightbox();
        initSmoothScroll();
    });

    // =============================================
    // 1. NAVIGACIJA – Hamburger + scroll efekti
    // =============================================
    function initNavigation() {
        var header    = document.getElementById('siteHeader');
        var hamburger = document.getElementById('hamburger');
        var mobileNav = document.getElementById('mobileNav');

        if (!header) return;

        // Scroll → dodaj klasu za pozadinu
        function onScroll() {
            if (window.scrollY > 40) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        }

        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll(); // Inicijalni poziv

        // Hamburger toggle
        if (hamburger && mobileNav) {
            hamburger.addEventListener('click', function () {
                var isOpen = hamburger.classList.contains('active');

                if (isOpen) {
                    hamburger.classList.remove('active');
                    mobileNav.classList.remove('open');
                    hamburger.setAttribute('aria-expanded', 'false');
                    mobileNav.setAttribute('aria-hidden', 'true');
                } else {
                    hamburger.classList.add('active');
                    mobileNav.classList.add('open');
                    hamburger.setAttribute('aria-expanded', 'true');
                    mobileNav.setAttribute('aria-hidden', 'false');
                }
            });

            // Zatvori mobilni meni klikom na link
            var mobileLinks = mobileNav.querySelectorAll('.mobile-nav-link');
            mobileLinks.forEach(function (link) {
                link.addEventListener('click', function () {
                    hamburger.classList.remove('active');
                    mobileNav.classList.remove('open');
                    hamburger.setAttribute('aria-expanded', 'false');
                    mobileNav.setAttribute('aria-hidden', 'true');
                });
            });

            // Zatvori klikom van menija
            document.addEventListener('click', function (e) {
                if (!header.contains(e.target)) {
                    hamburger.classList.remove('active');
                    mobileNav.classList.remove('open');
                }
            });
        }
    }

    // =============================================
    // 2. THEME TOGGLE – svetla / tamna tema
    // =============================================
    function initThemeToggle() {
        var html      = document.documentElement;
        var btnDesktop = document.getElementById('themeToggle');

        // Tema je već primenjena inline scriptom u <head> – samo sinhronizuj UI
        syncButtons();

        // Klik na dugme (jedno, vidljivo i na desktopu i na mobilnom, pored logoa)
        if (btnDesktop) {
            btnDesktop.addEventListener('click', function () { applyToggle(); });
        }

        function applyToggle() {
            var current  = html.getAttribute('data-theme') || 'dark';
            var next     = current === 'dark' ? 'light' : 'dark';

            // Kratkotrajni transition helper – gladak prelaz boja
            html.classList.add('theme-transitioning');
            html.setAttribute('data-theme', next);
            localStorage.setItem('flyrec_theme', next);

            syncButtons();

            setTimeout(function () {
                html.classList.remove('theme-transitioning');
            }, 400);
        }

        function syncButtons() {
            var theme = html.getAttribute('data-theme') || 'dark';
            var isDark = theme === 'dark';

            var ariaLabel = isDark ? 'Pređi na svetlu temu' : 'Pređi na tamnu temu';
            var titleText = isDark ? 'Svetla tema'          : 'Tamna tema';

            if (btnDesktop) {
                btnDesktop.setAttribute('aria-label', ariaLabel);
                btnDesktop.setAttribute('title', titleText);
            }
        }
    }

    // =============================================
    // 3. SMOOTH SCROLL za navigacione linkove
    // =============================================
    function initSmoothScroll() {
        var links = document.querySelectorAll('a[href^="#"]');
        links.forEach(function (link) {
            link.addEventListener('click', function (e) {
                var targetId = link.getAttribute('href');
                if (targetId === '#') return;

                var target = document.querySelector(targetId);
                if (!target) return;

                e.preventDefault();
                var headerH = document.getElementById('siteHeader').offsetHeight;
                var top = target.getBoundingClientRect().top + window.scrollY - headerH;

                window.scrollTo({ top: top, behavior: 'smooth' });
            });
        });
    }

    // =============================================
    // 3. SCROLL ANIMACIJE (Intersection Observer)
    // =============================================
    function initScrollAnimations() {
        var elements = document.querySelectorAll('.fade-up');
        if (!elements.length) return;

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target);
                }
            });
        }, {
            rootMargin: '0px 0px -60px 0px',
            threshold: 0.1
        });

        elements.forEach(function (el) {
            observer.observe(el);
        });
    }

    // =============================================
    // 4. VIDEO LAZY LOADING – učitaj iframe klikom
    // =============================================
    function initVideoLazyLoad() {
        var videoWrappers = document.querySelectorAll('.video-embed-wrapper');

        videoWrappers.forEach(function (wrapper) {
            var playBtn = wrapper.querySelector('.video-play-btn');
            if (!playBtn) return;

            playBtn.addEventListener('click', function () {
                var src = wrapper.getAttribute('data-src');
                if (!src) return;

                // Dodaj autoplay parametar URL-u
                var separator = src.indexOf('?') !== -1 ? '&' : '?';
                var embedSrc  = src + separator + 'autoplay=1';

                // Kreiraj iframe
                var iframe = document.createElement('iframe');
                iframe.setAttribute('src', embedSrc);
                iframe.setAttribute('frameborder', '0');
                iframe.setAttribute('allowfullscreen', '');
                iframe.setAttribute('allow', 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share');
                iframe.setAttribute('loading', 'lazy');

                wrapper.appendChild(iframe);
                wrapper.classList.add('loaded');
            });
        });
    }

    // =============================================
    // 5. KONTAKT FORMA – AJAX submit
    // =============================================
    function initContactForm() {
        var form = document.getElementById('contactForm');
        if (!form) return;

        form.addEventListener('submit', function (e) {
            e.preventDefault();

            var submitBtn  = document.getElementById('submitBtn');
            var btnText    = submitBtn.querySelector('.btn-text');
            var btnLoading = submitBtn.querySelector('.btn-loading');
            var msgEl      = document.getElementById('formMessage');

            // Prikaži loading stanje
            btnText.style.display    = 'none';
            btnLoading.style.display = 'flex';
            submitBtn.disabled       = true;
            msgEl.className          = 'form-message';
            msgEl.style.display      = 'none';

            var formData = new FormData(form);
            formData.append('action', 'flyrec_contact');
            formData.append('nonce',  flyrecData.nonce);

            fetch(flyrecData.ajaxUrl, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.success) {
                    msgEl.textContent  = data.data.message;
                    msgEl.className    = 'form-message success';
                    msgEl.style.display = 'block';
                    form.reset();
                } else {
                    msgEl.textContent  = data.data.message || 'Greška pri slanju. Pokušajte ponovo.';
                    msgEl.className    = 'form-message error';
                    msgEl.style.display = 'block';
                }
            })
            .catch(function () {
                msgEl.textContent  = 'Mrežna greška. Proverite konekciju i pokušajte ponovo.';
                msgEl.className    = 'form-message error';
                msgEl.style.display = 'block';
            })
            .finally(function () {
                btnText.style.display    = 'flex';
                btnLoading.style.display = 'none';
                submitBtn.disabled       = false;

                // Scroll do poruke
                msgEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            });
        });
    }

    // =============================================
    // 6. PORTFOLIO LIGHTBOX
    // =============================================
    function initPortfolioLightbox() {
        var modal           = document.getElementById('lightboxModal');
        var closeBtn        = document.getElementById('lightboxClose');
        var lightboxImg     = document.getElementById('lightboxImg');
        var lightboxVideoW  = document.getElementById('lightboxVideoWrapper');
        var lightboxVideo   = document.getElementById('lightboxVideo');
        var lightboxVideoSrc = document.getElementById('lightboxVideoSrc');
        var lightboxIframeW = document.getElementById('lightboxIframeWrapper');
        var lightboxIframe  = document.getElementById('lightboxIframe');
        var caption         = document.getElementById('lightboxCaption');

        if (!modal) return;

        function getEmbedUrl(url, type) {
            if (type === 'youtube') {
                var m = url.match(/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/|shorts\/))([A-Za-z0-9_-]{11})/);
                if (m) return 'https://www.youtube.com/embed/' + m[1] + '?autoplay=1&rel=0';
            }
            if (type === 'vimeo') {
                var m = url.match(/vimeo\.com\/(\d+)/);
                if (m) return 'https://player.vimeo.com/video/' + m[1] + '?autoplay=1';
            }
            return url;
        }

        function resetContent() {
            lightboxImg.style.display    = 'none';
            lightboxImg.src              = '';
            lightboxVideoW.style.display = 'none';
            lightboxVideo.pause();
            lightboxVideoSrc.src         = '';
            lightboxIframeW.style.display = 'none';
            lightboxIframe.src           = '';
        }

        function openModal(mode) {
            resetContent();
            if (mode === 'image')  lightboxImg.style.display    = '';
            if (mode === 'mp4')    lightboxVideoW.style.display  = '';
            if (mode === 'embed')  lightboxIframeW.style.display = '';

            modal.classList.add('open');
            modal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
            closeBtn.focus();
        }

        var items = document.querySelectorAll('.portfolio-item');
        items.forEach(function (item) {
            item.addEventListener('click', function () {
                var videoUrl  = item.getAttribute('data-video-url')  || '';
                var videoType = item.getAttribute('data-video-type') || '';
                var action    = item.getAttribute('data-action')     || 'modal';
                var title     = item.getAttribute('data-title')      || '';
                var imgSrc    = item.getAttribute('data-img')        || '';
                var imgEl     = item.querySelector('.portfolio-img');

                caption.textContent = title;

                // 1. Otvori u novom tabu (Instagram reels, external)
                if (action === 'newtab' && videoUrl) {
                    window.open(videoUrl, '_blank', 'noopener,noreferrer');
                    return;
                }

                // 2. MP4 u modalu
                if (videoType === 'mp4' && videoUrl) {
                    lightboxVideoSrc.src = videoUrl;
                    lightboxVideo.load();
                    openModal('mp4');
                    lightboxVideo.play().catch(function () {});
                    return;
                }

                // 3. YouTube / Vimeo iframe u modalu
                if ((videoType === 'youtube' || videoType === 'vimeo') && videoUrl) {
                    lightboxIframe.src = getEmbedUrl(videoUrl, videoType);
                    openModal('embed');
                    return;
                }

                // 4. Image lightbox (fallback)
                var src = imgSrc || (imgEl ? imgEl.src : '');
                if (!src) return;
                lightboxImg.src = src;
                lightboxImg.alt = title;
                openModal('image');
            });

            // Keyboard support
            item.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    item.click();
                }
            });
        });

        function closeLightbox() {
            modal.classList.remove('open');
            modal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
            resetContent();
        }

        if (closeBtn) closeBtn.addEventListener('click', closeLightbox);

        modal.addEventListener('click', function (e) {
            if (e.target === modal) closeLightbox();
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && modal.classList.contains('open')) {
                closeLightbox();
            }
        });
    }

})();
