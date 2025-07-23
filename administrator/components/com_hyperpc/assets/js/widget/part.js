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

    JBZoo.widget('HyperPC.AdminPart', {}, {

        /**
         * Choose primary group.
         *
         * @param e
         * @param $this
         */
        'change .jsGroupId' : function (e, $this) {
            var value = $(this).val();
            if (value > 1) {
                $this.$('.jsGroups').val(value).trigger('liszt:updated');
            }
        },

        /**
         * Setup default configurator name.
         *
         * @param e
         * @param $this
         */
        'change #jform_name' : function (e, $this) {
            var partName  = $(this).val();
            var largeName = $this.$('#jform_params_configurator_title').val().trim();

            if (largeName.length <= 0) {
                $this.$('#jform_params_configurator_title').val(partName);
            }
        },

        /**
         * Setup part price from default option.
         *
         * @param e
         * @param $this
         */
        'change .jsToggleDefaultOption' : function (e, $this) {
            var price = $(this).data('price');
            $this.$('#jform_price').val(price);
        },

        /**
         * Choose groups.
         *
         * @param e
         * @param $this
         */
        'change .jsGroups' : function (e, $this) {
            var value = $(this).val();
            if (value > 1) {
                $this.$('.jsGroupId').val(value).trigger('liszt:updated');
            }
        }
    });
});
