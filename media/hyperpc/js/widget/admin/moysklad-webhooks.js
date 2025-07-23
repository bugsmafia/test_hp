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

    JBZoo.widget('HyperPC.MoyskladWebhooks', {}, {

        /**
         * Handle Ajax error
         *
         * @param {string} error
         */
         _handleAjaxError : function (error) {
            const msg = error || 'Connection error';
            alert(msg);
        },

        /**
         * Оn click create webhook button.
         *
         * @param e
         * @param $this
         */
        'click .jsCreateWebhook' : function (e, $this) {
            const $button = $(this),
                  entityType = $button.data('entity'),
                  action = $button.data('action'),
                  url = $button.data('url');

            $this._openLoader();
            $.ajax({
                'type'      : 'POST',
                'dataType'  : 'json',
                'url'       : '/index.php',
                'data'      : {
                    'tmpl'       : 'component',
                    'option'     : 'com_hyperpc',
                    'task'       : 'moysklad.create_webhook',
                    'entityType' : entityType,
                    'action'     : action,
                    'url'        : url
                }
            })
            .done(function(data) {
                if (data.result) {
                    const $input = $button.prev();

                    $input.val(data.key);

                    $button.remove();
                } else {
                    $this._handleAjaxError(data.message);
                }
            })
            .fail(function(jqXHR, textStatus) {
                $this._handleAjaxError(textStatus);
            })
            .always(function () {
                $this._hideLoader();
            });
        },

        /**
         * Оn click remove webhook button.
         *
         * @param e
         * @param $this
         */
        'click .jsRemoveWebhook' : function (e, $this) {
            const $button = $(this),
                  key = $button.data('key');

            $this._openLoader();
            $.ajax({
                'type'      : 'POST',
                'dataType'  : 'json',
                'url'       : '/index.php',
                'data'      : {
                    'tmpl'   : 'component',
                    'option' : 'com_hyperpc',
                    'task'   : 'moysklad.remove_webhook',
                    'key'    : key
                }
            })
            .done(function(data) {
                if (data.result) {
                    const $input = $button.prev();

                    $input.val('');

                    $button.remove();
                } else {
                    $this._handleAjaxError(data.message);
                }
            })
            .fail(function(jqXHR, textStatus) {
                $this._handleAjaxError(textStatus);
            })
            .always(function () {
                $this._hideLoader();
            });
        },

    });
});
