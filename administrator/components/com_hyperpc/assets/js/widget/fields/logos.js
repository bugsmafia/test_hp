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

    JBZoo.widget('HyperPC.FieldLogos', {}, {

        /**
         * Initialize widget.
         *
         * @param $this
         */
        init : function ($this) {
            
        },
        
        'click .hp-logos-field-input' : function (e, $this) {
            var $field = $(this);
            if ($field.hasClass('checked')) {
                $field
                    .removeAttr('checked')
                    .removeClass('checked')
                    .next()
                    .removeClass('checked')
                    .parent('label').removeClass('checked');

                $field.trigger('change');
            } else {
                $field
                    .attr('checked', true)
                    .addClass('checked')
                    .next()
                    .addClass('checked')
                    .parent('label').addClass('checked');
            }
        }
    });
});
