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
 * @author     Roman Evsyukov <roman_e@hyperpc.ru>
 */

jQuery(function ($) {

    JBZoo.widget('HyperPC.FieldProductIndex', {
        'fieldType'     : null,
        'typePartGroup' : null,
        'typeFieldCat'  : null,
    }, {

        /**
         * Initialize widget.
         *
         * @param $this
         */
        init : function ($this) {
            let defaultGroup = $this.$('.jsSelectGroup').val();
            $this.$('#hp-field-group-' + defaultGroup).removeClass('hidden');

            $this.$('.hp-saved-index-fields').sortable({
                handle      : '.jsFieldSort',
                placeholder : 'ui-state-highlight',
                connectWith : '.hp-part-connected-sortable'
            }).disableSelection();
        },

        /**
         * Change select group.
         *
         * @param e
         * @param $this
         */
        'change #fiter_product_cat_ids, .jsSelectFieldsGroup' : function (e, $this) {
            let groupId = $(this).val();

            let data = {
                group_id : groupId,
                option   : 'com_hyperpc',
                task     : 'field.callback',
                method   : 'getCategoryFields',
                field    : $this.getOption('fieldType')
            };

            if ($(this).hasClass('jsSelectFieldsGroup')) {
                data.method = 'getGroupFields';
            }

            if ($(this).parents('.jsProductIndex').data('context') === 'com_hyperpc.position') {
                data.moysklad = true;
            }

            $.ajax({
                'url'       : '/administrator/index.php',
                'method'    : 'POST',
                'dataType'  : 'json',
                'timeout'   : 15000,
                'data'      : data,
            })
            .done(function(response) {
                if (response.result === true) {
                    $this.$('.jsSelectFields').html(response.output).trigger('liszt:updated');
                    $this.$('.jsAllowedFieldWrapper').removeClass('hidden');
                } else {
                    $this._setAlert($this, response.output);
                    $this.$('.jsAllowedFieldWrapper').addClass('hidden');
                }
            })
            .fail(function(xjr, error) {
                $this._setAlert($this, error);
            })
            .always(function() {

            });
        },

        /**
         * Setup info alert message.
         *
         * @param $this
         * @param message
         * @param type
         * @private
         */
        _setAlert : function ($this, message, type) {
            if (type === undefined) {
                type = 'warning';
            }

            $this.el.find('.jsFiltersWrapper').prepend('<div class="alert alert-' + type + '">' + message + '</div>');

            setTimeout(function () {
                $this.el.find('.alert').slideUp();
            }, 2000);
        },

        /**
         * Change group select list.
         *
         * @param e
         * @param $this
         */
        'change .jsSelectFields' : function (e, $this) {
            let value     = $(this).val(),
                groupId   = $this.$('#fiter_product_cat_ids').val(),
                fieldName = $('option:selected', this).text(),
                hasField  = $this.$('.hp-index-field-' + value + '[data-group="' + groupId + '"]').length,
                length    = $this.$('.hp-saved-index-fields li').length;

            let fromValue = $this.getOption('typePartGroup');
            const $fieldGroup = $this.$('.jsSelectFieldsGroup');

            if ($fieldGroup.val() !== 'none') {
                fromValue = $this.getOption('typeFieldCat');
            }

            let rootFieldName = $(this).parents('.jsProductIndex').data('name');

            let groupName = $('option:selected', $this.$('#fiter_product_cat_ids')).text();
                groupName = groupName.replace(/[^a-zA-ZА-Яа-яЁё]/gi,'');

            if (hasField <= 0) {
                $this.$('.hp-saved-index-fields').append(
                    '<li class="hp-index-field-' + value + '">' +
                        '<a href="#" class="hp-icon hp-icon-sort jsFieldSort"></a>' + "\n" +
                        '<input type="text" name="' + rootFieldName +
                                '[' + length + '][title]" value="' + fieldName + '" />' +
                        '<span class="hp-index-field-group-name">(' + groupName + ')</span>' +
                        '<a href="#" class="jsRemoveField pull-right">' +
                            '<i class="hp-icon hp-icon-remove"></i>' +
                        '</a>' +
                        '<input type="hidden" name="' + rootFieldName +
                                '[' + length + '][id]" value="' + value + '" />' +
                        '<input type="hidden" name="' + rootFieldName +
                                '[' + length + '][group_id]" value="' + groupId + '" />' +
                        '<input type="hidden" name="' + rootFieldName +
                                '[' + length + '][from]" value="' + fromValue + '" />' +
                    '</li>'
                );
            } else {
                $this._setAlert($this, $this.getOption('fieldAddedMsg'));
            }
        },

        'change .jsSelectFilterField' : function (e, $this) {
            const $filterField = $(this).parents('.jsProductIndex');

            let value  = $(this).val(),
                length = $filterField.find('.hp-saved-index-fields li').length;

            [fieldName, fieldId, groupId, fromValue, groupName] = value.split(':');

            let hasField   = $filterField.find('.hp-index-field-' + fieldId).length,
                fieldset   = $filterField.data('fieldset'),
                filterName = 'jform';

            if (fieldset) {
                filterName += '[' + fieldset + ']';
            }

            filterName += '[' + $filterField.data('fieldname') + ']';

            if (hasField <= 0) {
                $this.$('.hp-saved-index-fields').append(
                    '<li class="hp-index-field-' + fieldId + '">' +
                        '<a href="#" class="hp-icon hp-icon-sort jsFieldSort"></a>' + "\n" +
                        '<input type="text" name="' + filterName +
                            '[' + length + '][title]" value="' + fieldName + '" />' +
                        '<span class="hp-index-field-group-name">(' + groupName + ')</span>' +
                        '<a href="#" class="jsRemoveField pull-right">' +
                            '<i class="hp-icon hp-icon-remove"></i>' +
                        '</a>' +
                        '<input type="hidden" name="' + filterName +
                            '[' + length + '][id]" value="' + fieldId + '" />' +
                        '<input type="hidden" name="' + filterName +
                            '[' + length + '][group_id]" value="' + groupId + '" />' +
                        '<input type="hidden" name="' + filterName +
                            '[' + length + '][from]" value="' + fromValue + '" />' +
                    '</li>'
                );
            } else {
                $this._setAlert($this, $this.getOption('fieldAddedMsg'));
            }
        },

        /**
         * Remove basket field.
         *
         * @param e
         * @param $this
         */
        'click .jsRemoveField' : function (e, $this) {
            const $element = $(this);

            if (confirm($this.getOption('confirmMsg'))) {
                $element.closest('li').slideUp(500, function () {
                    $(this).remove();
                });
            }

            e.preventDefault();
        }
    });
});
