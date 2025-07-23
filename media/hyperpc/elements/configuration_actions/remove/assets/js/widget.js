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
 * @author     Sergey Kalistratov <kalistratov.s.m@gmail.com>
 */

jQuery(function ($) {

    JBZoo.widget('HyperPC.SiteConfigurationActionsRemove', {
        'msgAjaxError'   : 'Error',
        'msgSendConfirm' : 'Are you sure?'
    }, {

        /**
         * Remove configuration.
         *
         * @param e
         *
         * @param $this
         */
        'click .jsRemoveConfig' : function (e, $this) {
            let $el      = $(this),
                configId = $(this).data('configuration-id');

            UIkit.modal
                .confirm($this.getOption('msgSendConfirm').replace('%s', configId))
                .then(function() {
                    $this.ajax({
                        'data'     : {
                            'format'    : 'raw',
                            'config_id' : configId,
                            'task'      : 'configurator.remove_user_config'
                        },
                        'dataType' : 'json',
                        'url'      : '/index.php',
                        'success'  : function (data) {
                            $el.closest('tr').fadeOut('slow', function () {
                                $(this).remove();
                            });
                        },
                        'error' : function () {
                            UIkit.modal(
                                $('<div uk-modal>' +
                                    '<div class="uk-modal-dialog uk-modal-body">' +
                                        '<button class="uk-modal-close-default" type="button" uk-close></button>' +
                                        '<p>' + $this.getOption('msgAjaxError') + '</p>' +
                                    '</div>' +
                                '</div>')
                            ).show();
                        }
                    });
                });

            e.preventDefault();
        }

    });
});
