/**
 * Flyrec Instagram Feed – drag & drop reorder u admin listi flyrec_media.
 * jQuery UI Sortable (bundled sa WP core) + AJAX upis novog redosleda.
 */
(function ($) {
    'use strict';

    $(function () {
        var $list = $('#the-list');
        if (!$list.length) return;

        var $status = $('<span class="fig-reorder-status"></span>').insertAfter('.wp-heading-inline .page-title-action');

        $list.sortable({
            items: 'tr',
            handle: '.fig-drag-handle',
            axis: 'y',
            cursor: 'move',
            opacity: 0.7,
            helper: function (e, tr) {
                // Zadrži širinu kolona dok se red vuče, inače se helper skupi.
                var $originals = tr.children();
                var $helper = tr.clone();
                $helper.children().each(function (index) {
                    $(this).width($originals.eq(index).width());
                });
                return $helper;
            },
            update: function () {
                var ids = [];
                $list.find('tr').each(function () {
                    var id = $(this).attr('id'); // "post-123"
                    if (id) ids.push(parseInt(id.replace('post-', ''), 10));
                });

                $status.text(figReorder.i18n.saving);

                $.post(figReorder.ajaxUrl, {
                    action: 'fig_reorder_media',
                    nonce: figReorder.nonce,
                    order: ids,
                })
                    .done(function (res) {
                        $status.text(res.success ? figReorder.i18n.saved : figReorder.i18n.error);
                        setTimeout(function () { $status.text(''); }, 2000);
                    })
                    .fail(function () {
                        $status.text(figReorder.i18n.error);
                    });
            },
        });
    });
})(jQuery);
