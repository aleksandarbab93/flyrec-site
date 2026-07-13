/**
 * Flyrec Instagram Feed – frontend ponašanje.
 * Čist vanilla JS, bez zavisnosti. Instagram embed.js se učitava lenjo
 * (samo kada korisnik stvarno otvori embed popup), ne na inicijalno
 * učitavanje stranice.
 */
(function () {
    'use strict';

    var embedScriptLoading = false;
    var embedScriptLoaded  = false;

    function loadEmbedScript(callback) {
        if (embedScriptLoaded && window.instgrm) {
            callback();
            return;
        }
        if (embedScriptLoading) {
            var check = setInterval(function () {
                if (window.instgrm) {
                    clearInterval(check);
                    callback();
                }
            }, 150);
            return;
        }
        embedScriptLoading = true;
        var script = document.createElement('script');
        script.src = 'https://www.instagram.com/embed.js';
        script.async = true;
        script.onload = function () {
            embedScriptLoaded = true;
            callback();
        };
        document.body.appendChild(script);
    }

    function ajaxPost(action, data) {
        var form = new FormData();
        form.append('action', action);
        form.append('nonce', figFrontend.nonce);
        Object.keys(data || {}).forEach(function (key) {
            form.append(key, data[key]);
        });
        return fetch(figFrontend.ajaxUrl, { method: 'POST', body: form, credentials: 'same-origin' })
            .then(function (res) { return res.json(); });
    }

    // =============================================
    // LIGHTBOX
    // =============================================
    function initLightbox() {
        var modal    = document.getElementById('figLightboxModal');
        if (!modal) return;

        var closeBtn  = document.getElementById('figLightboxClose');
        var spinner   = document.getElementById('figLightboxSpinner');
        var embedBox  = document.getElementById('figLightboxEmbed');
        var imgBox    = document.getElementById('figLightboxImg');
        var fallback  = document.getElementById('figLightboxFallback');

        function resetModal() {
            spinner.classList.remove('fig-modal-spinner--active');
            embedBox.innerHTML = '';
            imgBox.style.display = 'none';
            imgBox.src = '';
            fallback.style.display = 'none';
            fallback.innerHTML = '';
        }

        function openModal() {
            modal.classList.add('fig-modal--open');
            modal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
            closeBtn.focus();
        }

        function closeModal() {
            modal.classList.remove('fig-modal--open');
            modal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
            resetModal();
        }

        function showFallback(message, permalink) {
            spinner.classList.remove('fig-modal-spinner--active');
            fallback.style.display = 'block';
            var text = document.createElement('span');
            text.textContent = message || figFrontend.i18n.embedFailed;
            fallback.appendChild(text);
            if (permalink) {
                fallback.appendChild(document.createElement('br'));
                var link = document.createElement('a');
                link.href = permalink;
                link.target = '_blank';
                link.rel = 'noopener noreferrer';
                link.textContent = figFrontend.i18n.openInstagram;
                fallback.appendChild(link);
            }
        }

        function loadEmbed(postId, permalinkFallback) {
            spinner.classList.add('fig-modal-spinner--active');
            ajaxPost('fig_get_embed', { post_id: postId })
                .then(function (res) {
                    if (res.success && res.data && res.data.html) {
                        embedBox.innerHTML = res.data.html;
                        spinner.classList.remove('fig-modal-spinner--active');
                        loadEmbedScript(function () {
                            if (window.instgrm && window.instgrm.Embeds) {
                                window.instgrm.Embeds.process();
                            }
                        });
                    } else {
                        var permalink = (res.data && res.data.permalink) || permalinkFallback;
                        showFallback(res.data && res.data.message, permalink);
                    }
                })
                .catch(function () {
                    showFallback(figFrontend.i18n.embedFailed, permalinkFallback);
                });
        }

        function handleItemActivate(item) {
            var clickAction = item.getAttribute('data-click-action') || 'lightbox';
            var permalink   = item.getAttribute('data-permalink') || '';
            var type        = item.getAttribute('data-type') || '';
            var postId      = item.getAttribute('data-post-id') || '';
            var thumbEl     = item.querySelector('.fig-item-thumb');

            if ('instagram' === clickAction) {
                if (permalink) window.open(permalink, '_blank', 'noopener,noreferrer');
                return;
            }

            resetModal();
            openModal();

            var showImageDirectly = 'lightbox' === clickAction && 'IMAGE' === type;

            if (showImageDirectly && thumbEl) {
                imgBox.src = thumbEl.src;
                imgBox.style.display = '';
                return;
            }

            loadEmbed(postId, permalink);
        }

        document.addEventListener('click', function (e) {
            var item = e.target.closest ? e.target.closest('.fig-item') : null;
            if (!item) return;
            handleItemActivate(item);
        });

        document.addEventListener('keydown', function (e) {
            if (('Enter' === e.key || ' ' === e.key) && e.target.classList && e.target.classList.contains('fig-item')) {
                e.preventDefault();
                handleItemActivate(e.target);
            }
            if ('Escape' === e.key && modal.classList.contains('fig-modal--open')) {
                closeModal();
            }
        });

        closeBtn.addEventListener('click', closeModal);
        modal.addEventListener('click', function (e) {
            if (e.target === modal) closeModal();
        });
    }

    // =============================================
    // UČITAJ JOŠ
    // =============================================
    function initLoadMore() {
        var buttons = document.querySelectorAll('.fig-btn--load-more');
        buttons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                var wrapper = btn.closest('.fig-grid-wrapper');
                var grid    = wrapper.querySelector('.fig-grid');
                var offset  = parseInt(grid.getAttribute('data-offset'), 10) || 0;
                var limit   = parseInt(grid.getAttribute('data-limit'), 10) || 12;
                var type    = grid.getAttribute('data-type') || 'all';
                var clickAction = grid.getAttribute('data-click-action') || 'lightbox';

                var originalText = btn.textContent;
                btn.disabled = true;
                btn.textContent = figFrontend.i18n.loading;

                ajaxPost('fig_load_more', {
                    offset: offset,
                    limit: limit,
                    type: type,
                    click_action: clickAction,
                    columns: 0,
                })
                    .then(function (res) {
                        if (res.success) {
                            grid.insertAdjacentHTML('beforeend', res.data.html);
                            grid.setAttribute('data-offset', offset + limit);
                            if (!res.data.has_more) {
                                btn.remove();
                            } else {
                                btn.disabled = false;
                                btn.textContent = originalText;
                            }
                        } else {
                            btn.disabled = false;
                            btn.textContent = originalText;
                        }
                    })
                    .catch(function () {
                        btn.disabled = false;
                        btn.textContent = originalText;
                    });
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        initLightbox();
        initLoadMore();
    });
})();
