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
 * @author     Sergey Kalistratov <kalistratov.s.m@gmail.com>
 * @author     Artem Vyshnevskiy
 */

jQuery(function ($) {

    JBZoo.widget('HyperPC.ConfigurationModule', {}, {

        /**
         * Initialize widget.
         *
         * @param $this
         */
        init : function ($this) {
        },

        /**
         * Check configuration by id.
         *
         * @param e
         * @param $this
         * @returns {boolean}
         */
        'submit form' : function (e, $this) {
            var form            = $(this),
                configurationId = $this.$('#configuration-id').val();

            var modalHtml = '<div class="uk-modal-body uk-width-2xlarge uk-flex uk-flex-middle" uk-lightbox>' +
                    '<button class="uk-modal-close-default" type="button" uk-close></button>' +
                    '<div class="uk-width-expand">' +
                        '<div class="uk-margin">' +
                            '<span class="uk-text-danger uk-margin-small-right" uk-icon="icon: bolt"></span>' +
                            'Ошибка!' +
                        '</div>' +
                        '<div>' +
                            'Пожалуйста введите номер конфигурации' +
                        '</div>' +
                    '</div>' +
                '</div>';

            if (!configurationId) {
                UIkit.modal.dialog(modalHtml);
                return false;
            } else {
                $this._openLoader();
                $this.ajax({
                    'url'       : '/index.php',
                    'dataType'  : 'json',
                    'data'      : {
                        'configuration_id' : configurationId,
                        'task' : 'configurator.check_configuration'
                    },
                    'success' : function (data) {
                        $this._hideLoader();
                        if (data.result === 'error') {
                            var modalHtml = '<div class="uk-modal-body uk-width-2xlarge uk-flex uk-flex-middle" uk-lightbox>' +
                                    '<button class="uk-modal-close-default" type="button" uk-close></button>' +
                                    '<div class="uk-width-expand">' +
                                        '<h2 class="uk-modal-title">' +
                                            '<span class="uk-text-danger uk-margin-small-right" uk-icon="icon: bolt"></span>' +
                                            'Ошибка!' +
                                        '</h2>' +
                                        '<div>' +
                                            'Персональная конфигурация №<strong>' + configurationId + '</strong> не найдена.' +
                                        '</div>' +
                                    '</div>' +
                                '</div>';
                            UIkit.modal.dialog(modalHtml);

                            return false;
                        }

                        if (data.result === 'success') {
                            window.location.href = form.attr('action') + '&configuration_id=' + configurationId;
                        }
                    },
                    'error' : function () {
                        $this._hideLoader();
                    }
                });
            }

            return false;
        },

        'click .jsModuleConfigurationIcon' : function (e, $this) {
            UIkit.tooltip($(this)).hide();
        }
    });
});
