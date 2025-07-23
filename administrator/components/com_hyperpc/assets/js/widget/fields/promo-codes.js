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

    JBZoo.widget('HyperPC.FieldPromoCodes', {
    }, {

        /**
         * Initialize widget.
         *
         * @param   $this
         */
        init : function ($this) {
            const typeVal = parseInt(($this.$('#jform_type').val()));
            const relatedProducts = $this.$('.field-related-product');

            if (typeVal === 0) {
                $this.$('#jform_rate')
                    .val(0)
                    .attr('readonly', 'readonly');

                relatedProducts.find('ul li').remove();
            }
        },

        /**
         * Change code type.
         *
         * @param   e
         * @param   $this
         */
        'change #jform_type' : function (e, $this) {
            const val = parseInt(($(this).val()));
            const relatedProducts = $this.$('.field-related-product');

            if (val === 0) {
                $this.$('#jform_rate')
                    .val(0)
                    .attr('readonly', 'readonly');

                relatedProducts.find('ul li').remove();
            }

            if (val === 1 || val === 2) {
                $this.$('#jform_rate')
                    .removeAttr('readonly');
            }
        }
    });
});
