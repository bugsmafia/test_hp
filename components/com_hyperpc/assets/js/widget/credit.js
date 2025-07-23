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
 */

jQuery(function ($) {

    JBZoo.widget('HyperPC.SiteCredit', {}, {

        /**
         * Reload open loader.
         *
         * @private
         */
        _openLoader : function () {
            var loaderHtml =
                '<div class="hp-loader">' +
                    '<div class="hp-loader-wrapper">' +
                        '<img src="/media/hyperpc/img/loaders/loader-black64x64.gif" class="hp-loader-image" />' +
                    '</div>' +
                '</div>';
            $('body').addClass('hp-ajax').prepend(loaderHtml);
        },

        /**
         * Submit form.
         *
         * @param e
         * @param $this
         */
        'submit form' : function (e, $this) {
            $this._openLoader();
        }
    });
});
