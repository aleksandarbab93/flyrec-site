/**
 * Flyrec Instagram Feed – Admin akcije (konekcija, sync, refresh, disconnect, clear).
 * Čist jQuery (već dostupan u WP adminu), bez dodatnih zavisnosti.
 */
(function ($) {
    'use strict';

    function showNotice(type, message) {
        var $notice = $('#fig-notice');
        $notice
            .removeClass('notice-success notice-error notice-warning')
            .addClass('notice-' + type)
            .find('p').text(message);
        $notice.show();
        $('html, body').animate({ scrollTop: $notice.offset().top - 40 }, 300);
    }

    function withBusyButton($btn, workFn) {
        var originalText = $btn.text();
        $btn.prop('disabled', true).text(figAdmin.i18n.working);

        workFn().always(function () {
            $btn.prop('disabled', false).text(originalText);
        });
    }

    function ajaxPost(action, extraData) {
        return $.post(figAdmin.ajaxUrl, $.extend({
            action: action,
            nonce: figAdmin.nonce,
        }, extraData || {}));
    }

    $(function () {
        $('#fig-btn-connect').on('click', function () {
            var $btn = $(this);
            var token = $('#fig_token_input').val().trim();
            var appSecret = $('#fig_app_secret_input').val().trim();

            if (!token) {
                showNotice('error', 'Unesite access token pre povezivanja.');
                return;
            }

            withBusyButton($btn, function () {
                return ajaxPost('fig_connect_token', { token: token, app_secret: appSecret })
                    .done(function (res) {
                        if (res.success) {
                            showNotice('success', res.data.message);
                            setTimeout(function () { window.location.reload(); }, 1200);
                        } else {
                            showNotice('error', res.data.message || 'Greška pri povezivanju.');
                        }
                    })
                    .fail(function () {
                        showNotice('error', 'Mrežna greška pri povezivanju.');
                    });
            });
        });

        $('#fig-btn-sync').on('click', function () {
            var $btn = $(this);
            withBusyButton($btn, function () {
                return ajaxPost('fig_manual_sync')
                    .done(function (res) {
                        var data = res.data || {};
                        showNotice(res.success ? 'success' : 'error', data.message || 'Gotovo.');
                        if (res.success) {
                            setTimeout(function () { window.location.reload(); }, 1200);
                        }
                    })
                    .fail(function () {
                        showNotice('error', 'Mrežna greška pri sinhronizaciji.');
                    });
            });
        });

        $('#fig-btn-refresh').on('click', function () {
            var $btn = $(this);
            withBusyButton($btn, function () {
                return ajaxPost('fig_refresh_token')
                    .done(function (res) {
                        showNotice(res.success ? 'success' : 'error', (res.data && res.data.message) || 'Gotovo.');
                        if (res.success) {
                            setTimeout(function () { window.location.reload(); }, 1200);
                        }
                    })
                    .fail(function () {
                        showNotice('error', 'Mrežna greška pri osvežavanju tokena.');
                    });
            });
        });

        $('#fig-btn-disconnect').on('click', function () {
            if (!window.confirm(figAdmin.i18n.confirmDisconnect)) return;
            var $btn = $(this);
            withBusyButton($btn, function () {
                return ajaxPost('fig_disconnect')
                    .done(function (res) {
                        showNotice('success', (res.data && res.data.message) || 'Gotovo.');
                        setTimeout(function () { window.location.reload(); }, 1000);
                    })
                    .fail(function () {
                        showNotice('error', 'Mrežna greška.');
                    });
            });
        });

        $('#fig-btn-clear').on('click', function () {
            if (!window.confirm(figAdmin.i18n.confirmClear)) return;
            var $btn = $(this);
            withBusyButton($btn, function () {
                return ajaxPost('fig_clear_data')
                    .done(function (res) {
                        showNotice('success', (res.data && res.data.message) || 'Gotovo.');
                        setTimeout(function () { window.location.reload(); }, 1200);
                    })
                    .fail(function () {
                        showNotice('error', 'Mrežna greška.');
                    });
            });
        });
    });
})(jQuery);
