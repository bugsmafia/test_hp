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

    JBZoo.widget('HyperPC.FieldCartParams', {

        'formFieldName'           : '',
        'errorMessage'            : '',
        'aliasMessage'            : '',
        'confirmMessage'          : 'Are you sure?',
        'placeholderTextMultiple' : '',
        'aliasMessageError'       : ''

    }, {

        /**
         * Alert error.
         *
         * @param $this
         * @param errorMgs
         */
        _error : function ($this, errorMgs) {
            $this.alert(null, null, {
                'title' : $this.getOption('errorMessage'),
                'text'  : errorMgs,
                'type'  : 'error'
            });
        },

        /**
         * Initialize widget.
         *
         * @param $this
         */
        init : function ($this) {
            //  Initialize sortable position.
            $('.jsSortableOrder, .jsSortablePayments, .jsSortableCredit, .jsSortableAfterSave').sortable({
                connectWith : '.sortable-wrapper',
                handle      : '.jsFieldSort',
                placeholder : 'ui-state-highlight',
                stop        : function(e, ui) {
                    var el          = $(ui.item[0]);
                    var elClass     = el.attr('class');
                    var newPosition = $this.$('.' + elClass).closest('.hp-fieldset').data('position');

                    $this.$('.' + elClass).find('.jsElementPosition').val(newPosition)
                }
            });

            $this.$('.accordion-group').each(function () {
                var identifier = $(this).find('.accordion-body').data('identifier');
                $this.$('.jsSortable-' + identifier).sortable({
                    connectWith : '.sortable-wrapper-payment',
                    handle      : '.jsFieldSort',
                    placeholder : 'ui-state-highlight'
                });
            });
        },

        /**
         * Change field name in to accordion head.
         *
         * @param e
         * @param $this
         */
        'keyup .hp-element-name' : function (e, $this) {
            var value = $(this).val();

            if (value.length > 100) {
                value = value.substring(0, 100) + '...';
            }

            $(this)
                .closest('.accordion-group')
                .find('.accordion-heading > .accordion-toggle')
                .text(value.trim());
        },

        /**
         * Remove basket field.
         *
         * @param e
         * @param $this
         */
        'click .jsRemoveField' : function (e, $this) {
            var element = $(this);
            if (confirm($this.getOption('confirmMessage'))) {
                element.closest('.accordion-group').slideUp(500, function () {
                    $(this).remove();
                });
            }
            
            e.preventDefault();
        },

        /**
         * Mask of bootstrap 2 accordion.
         *
         * @param identifier
         * @param html
         * @param fieldName
         * @returns {string}
         * @private
         */
        _bootstrapAccordionWrapper : function (identifier, html, fieldName) {
            return  '<li class="' + identifier + '"><div class="accordion-group">' +
                        '<div class="accordion-heading">' +
                            '<a href="#" class="hp-icon hp-icon-sort jsFieldSort">' +
                            '</a>' +
                            '<a class="accordion-toggle" data-toggle="collapse" data-parent="#hp-accordion-order" href="#el-' + identifier + '">' +
                                fieldName +
                            '</a>' +
                            //'<em class="text-error">(' + fieldType + ')</em>' +
                            '<div class="accordion-nav">' +
                                '<a class="accordion-toggle" data-toggle="collapse" data-parent="#hp-accordion-order" href="#el-' + identifier + '">' +
                                    '<i class="hp-icon hp-icon-edit"></i>' +
                                '</a>' +
                                '<a href="#" class="jsRemoveField">' +
                                    '<i class="hp-icon hp-icon-remove"></i>' +
                                '</a>' +
                            '</div>' +
                        '</div>' +
                        '<div id="el-' + identifier + '" class="accordion-body collapse">' +
                            '<div class="accordion-inner form-horizontal">' +
                                html +
                            '</div>' +
                        '</div>' +
                    '</div></li>';
        },

        /**
         * Mask of bootstrap 2 accordion for related.
         *
         * @param identifier
         * @param html
         * @param fieldName
         * @param parentIdentifier
         * @param fieldType
         * @returns {string}
         * @private
         */
        _bootstrapAccordionWrapperRelated : function (identifier, html, fieldName, parentIdentifier, fieldType) {
            return  '<li class="' + identifier + '"><div class="accordion-group">' +
                        '<div class="accordion-heading">' +
                            '<a href="#" class="hp-icon hp-icon-sort jsFieldSort">' +
                            '</a>' +
                            '<a class="accordion-toggle" data-toggle="collapse" data-parent="#hp-accordion-' + parentIdentifier + '" href="#el-' + parentIdentifier + '-' + identifier + '">' +
                                fieldName +
                            '</a>' +
                            '<em class="text-error">(' + fieldType + ')</em>' +
                            '<div class="accordion-nav">' +
                                '<a class="accordion-toggle" data-toggle="collapse" data-parent="#hp-accordion-' + parentIdentifier + '" href="#el-' + parentIdentifier + '-' + identifier + '">' +
                                    '<i class="hp-icon hp-icon-edit"></i>' +
                                '</a>' +
                                '<a href="#" class="jsRemoveField">' +
                                    '<i class="hp-icon hp-icon-remove"></i>' +
                                '</a>' +
                            '</div>' +
                        '</div>' +
                        '<div id="el-' + parentIdentifier + '-' + identifier + '" class="accordion-body collapse">' +
                            '<div class="accordion-inner form-horizontal">' +
                                html +
                            '</div>' +
                        '</div>' +
                    '</div></li>';
        },

        /**
         * Reload script for form btn group.
         *
         * @private
         */
        _reloadBtnGroup : function () {
            // Turn radios into btn-group
            $('.radio.btn-group label').addClass('btn');

            $('fieldset.btn-group').each(function() {
                // Handle disabled, prevent clicks on the container, and add disabled style to each button
                if ($(this).prop('disabled')) {
                    $(this).css('pointer-events', 'none').off('click');
                    $(this).find('.btn').addClass('disabled');
                }
            });

            $('.btn-group label:not(.active)').click(function() {
                var label = $(this);
                var input = $('#' + label.attr('for'));

                if (!input.prop('checked')) {
                    label.closest('.btn-group').find('label').removeClass('active btn-success btn-danger btn-primary');

                    if (label.closest('.btn-group').hasClass('btn-group-reversed')) {
                        if (input.val() == '') {
                            label.addClass('active btn-primary');
                        } else if (input.val() == 0) {
                            label.addClass('active btn-success');
                        } else {
                            label.addClass('active btn-danger');
                        }
                    } else {
                        if (input.val() == '') {
                            label.addClass('active btn-primary');
                        } else if (input.val() == 0) {
                            label.addClass('active btn-danger');
                        } else {
                            label.addClass('active btn-success');
                        }
                    }
                    input.prop('checked', true);
                    input.trigger('change');
                }
            });

            $('.btn-group input[checked=checked]').each(function() {
                var $self  = $(this);
                var attrId = $self.attr('id');

                if ($self.parent().hasClass('btn-group-reversed')) {
                    if ($self.val() == '') {
                        $('label[for=' + attrId + ']').addClass('active btn-primary');
                    } else if ($self.val() == 0) {
                        $('label[for=' + attrId + ']').addClass('active btn-success');
                    } else {
                        $('label[for=' + attrId + ']').addClass('active btn-danger');
                    }
                } else {
                    if ($self.val() == '') {
                        $('label[for=' + attrId + ']').addClass('active btn-primary');
                    } else if ($self.val() == 0) {
                        $('label[for=' + attrId + ']').addClass('active btn-danger');
                    } else {
                        $('label[for=' + attrId + ']').addClass('active btn-success');
                    }
                }
            });
        }
    });
});
