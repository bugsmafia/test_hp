/**
 * HYPERPC - The shop of powerful computers.
 *
 * This file is part of the HYPERPC package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package    HYPERPC
 * @license    Proprietary
 * @copyright  Proprietary https://hyperpc.ru/license
 * @link       https://github.com/HYPER-PC/HYPERPC".
 *
 * @author     Artem Vyshnevskiy
 */

jQuery(function ($) {

    JBZoo.widget('HyperPC.SiteConfigurationActionsCPPdf', {}, {

        /**
         * Initialize widget.
         *
         * @param $this
         */
        init : function ($this) {
            $('body').on('submit', '.jsCommercialProposalPreprocessForm', function (e) {
                e.preventDefault();

                const $form = $(this);
                $this._lockFormSubmitButton($form);
                $.ajax({
                    'url'      : $form.attr('action'),
                    'dataType' : 'json',
                    'type'     : 'POST',
                    'data'     : $form.serialize()
                })
                .done(function (response, textStatus, $xhr) {
                    if (response.result) {
                        const item = [{source: response.url, type: 'iframe'}],
                              panel = UIkit.lightboxPanel({'items': item});

                        $(panel.$el).data('source', response.url);
                        panel.show();

                        UIkit.modal($form.closest('[uk-modal]')).$destroy(true);
                    } else {
                        $this._handleAjaxError($xhr);
                        $this._unlockFormSubmitButton($form);
                    }
                })
                .fail(function ($xhr) {
                    $this._handleAjaxError($xhr);
                    $this._unlockFormSubmitButton($form);
                });
            });
        },

        /**
         * Handle Ajax error
         *
         * @param {Object} $xhr
         */
        _handleAjaxError : function ($xhr) {
            const msg = $xhr.status ? $xhr.statusText : 'Connection error';
            UIkit.notification(msg, 'danger');
        },

        /**
         * Lock form submit button.
         *
         * @param $form - jQuery object
         */
        _lockFormSubmitButton : function ($form) {
            $form.find('[type="submit"]')
                .prepend('<span uk-spinner="ratio: 0.7"></span>')
                .attr('disabled', 'disabled');
        },

        /**
         * Unlock form submit button.
         *
         * @param $form - jQuery object
         */
        _unlockFormSubmitButton : function ($form) {
            $form.find('[type="submit"]')
                .removeAttr('disabled')
                .find('[uk-spinner]').remove();
        },

        /**
         * Build pdf.
         *
         * @param e
         *
         * @param $this
         */
        'click .jsShowCpForm' : function (e, $this) {
            e.preventDefault();

            $.ajax({
                'type'     : 'GET',
                'dataType' : 'html',
                'url'      : $(this).attr('href')
            })
            .done(function (html) {
                UIkit.modal(
                    $('<div class="uk-modal-container" uk-modal>' +
                        '<div class="uk-modal-dialog uk-modal-body">' +
                            '<button class="uk-modal-close-default" type="button" uk-close></button>' +
                            '<div class="uk-container uk-container-small">' +
                                html +
                            '</div>' +
                        '</div>' +
                    '</div>')
                ).show();
            })
            .fail(function ($xhr) {
                $this._handleAjaxError($xhr);
            });
        }
    });
});
