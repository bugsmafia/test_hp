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

    JBZoo.widget('HyperPC.AdminOrder', {}, {

        /**
         * Choose primary group.
         *
         * @param e
         * @param $this
         */
        'click .jsToggleProductConfig' : function (e, $this) {
            var productId = $(this).data('id');

            var partRow = $this.$('.hp-product-' + productId + '-config');
            if (!partRow.hasClass('isShow')) {
                partRow.show().addClass('isShow');
            } else {
                partRow.hide().removeClass('isShow');
            }

            e.preventDefault();
        }
    });
});
