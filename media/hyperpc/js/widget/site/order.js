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
 * @author     Artem Vyshnevskiy
 */

jQuery(function ($) {

    JBZoo.widget('HyperPC.SiteOrder', {
        sberbankWarningBeforeSend : 'Check if you have authorized on <a href="https://online.sberbank.ru" class="uk-link-muted tm-link-underlined" target="_blank" rel=noopener>online.sberbank.ru</a> before continue'
    }, {

        /**
         * Initialize widget.
         *
         * @param $this
         */
        init : function ($this) {},

        /**
         * Click credit sberbank send button.
         *
         * @param $this
         */
        'click .jsCreditMethodSendButton[data-type="sberbank"]' : function (e, $this) {
            e.preventDefault();
            const href = $(this).attr('href'),
                  btnCommonClass = 'uk-button uk-button-small uk-button-normal@s',
                  $dialogHtml =
                $('<div>' +
                    '<div class="uk-modal-dialog">' +
                        '<button class="uk-modal-close-default" type="button" uk-close></button>' +
                        '<div class="uk-modal-body tm-background-gray-5">' +
                            '<p>' + $this.getOption('sberbankWarningBeforeSend') + '</p>' +
                        '</div>' +
                        '<div class="uk-modal-footer uk-text-right tm-background-gray-5">' +
                            '<button class="' + btnCommonClass + ' uk-button-default uk-modal-close" type="button">Отмена</button> ' +
                            '<a href="' + href + '" class="' + btnCommonClass + ' uk-button-primary" target="_blank">Продолжить</a>' +
                        '</div>' +
                    '</div>' +
                '</div>');

            UIkit.modal($dialogHtml).show();
        },

    });
});
