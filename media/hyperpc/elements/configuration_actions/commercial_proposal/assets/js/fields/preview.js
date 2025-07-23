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
 * @author     Roman Evsyukov <roman_e@hyperpc.ru>
 */

jQuery(function ($) {

    JBZoo.widget('HyperPC.FieldPreview', {}, {

        /**
         * Show iframe with pdf previe
         *
         * @param e
         * @param $this
         */
        'click .jsPdfPreview' : function (e, $this) {
            e.preventDefault();

            $.fancybox.open({
                src     : $(this).attr('href'),
                type    : 'iframe'
            });
        }
    });

});
