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

    JBZoo.widget('HyperPC.FieldPartFields', {
        confirmMsg : 'Are you sure?',
        formName   : 'jform[params][part_fields][]'
    }, {

        /**
         * Initialize widget.
         *
         * @param $this
         */
        init : function ($this) {
            $this.$('#hp-saved-list, #hp-allowed-list').sortable({
                handle      : '.jsFieldSort',
                placeholder : 'ui-state-highlight',
                connectWith : '.hp-part-connected-sortable'
            }).disableSelection();
        },

        /**
         * Add new part field.
         *
         * @param e
         * @param $this
         */
        'change .jsPartFieldList' : function (e, $this) {
            var value = $(this).val();
            if (value != 0) {
                var hasField     = $this.$('.hp-part-field-' + value).length;
                var selectedText = $('option:selected', this).text();

                if ($('option:selected', this).data('name')) {
                    selectedText = $('option:selected', this).data('name');
                }

                if (hasField <= 0) {
                    $this.$('#hp-saved-list').append('<li class="hp-part-field-' + value + '">' +
                            '<a href="#" class="hp-icon hp-icon-sort jsFieldSort"></a>' +
                            selectedText +
                            '<a href="#" class="jsRemoveField pull-right">' +
                                '<i class="hp-icon hp-icon-remove"></i>' +
                            '</a>' +
                            '<input type="hidden" name="' + $this.getOption('formName') + '" value="' + value + '" />' +
                        '</li>');
                } else {
                    alert($this.getOption('fieldAddedMsg'));
                }
            }
        },

        /**
         * Remove basket field.
         *
         * @param e
         * @param $this
         */
        'click .jsRemoveField' : function (e, $this) {
            var element = $(this);

            if (confirm($this.getOption('confirmMsg'))) {
                element.closest('li').slideUp(500, function () {
                    $(this).remove();
                });
            }

            e.preventDefault();
        }

    });

});
