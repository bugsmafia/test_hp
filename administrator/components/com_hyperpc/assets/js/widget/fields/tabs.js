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

    JBZoo.widget('HyperPC.FieldTabs', {
        'hiddenWrapper' : '.field-tabs'
    }, {

        /**
         * Initialize widget.
         *
         * @param $this
         */
        init : function ($this) {
            $this.$('div.hidden').each(function () {
                $(this).find('*').each(function () {
                    if ($(this).attr('name') !== undefined) {
                        $(this)
                            .attr('data-name', $(this).attr('name'))
                            .attr('name', '');
                    }
                });
            });
        },

        /**
         * Add new tab.
         *
         * @param e
         * @param $this
         */
        'click .jsAddNew' : function (e, $this) {
            var field = $this.$($this.getOption('hiddenWrapper'));
            $(field.find('div.hidden').get(0)).fadeIn(500, function () {
                $(this).find('*').each(function () {
                    if ($(this).data('name') !== undefined) {
                        $(this).attr('name', $(this).data('name'));
                    }

                });
            }).removeClass('hidden');
            e.preventDefault();
        },

        /**
         * Remove tab.
         *
         * @param e
         * @param $this
         */
        'click .jsRemove' : function (e, $this) {
            var element = $(this);
            element.closest('.field-content').fadeOut(500, function () {
                if ($(this).hasClass('jsIsSaved')) {
                    $(this).remove();
                } else {
                    $(this).addClass('hidden');
                    $(this).find('*').each(function () {
                        if ($(this).attr('name') !== undefined) {
                            $(this).attr('name', '').val('');
                        }
                    });
                }
            });

            e.preventDefault();
        }
    });
});
