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
 * @author     Artem Vyshnevskiy
 */

jQuery(function ($) {

    JBZoo.widget('HyperPC.FieldConfigurator', {

        'parts'   : [],
        'options' : []

    }, {

        /**
         * Initialize widget.
         *
         * @param $this
         */
        init: function ($this) {
            const parts = $this.getOption('parts'),
                  options = $this.getOption('options');

            parts.forEach(partId => {
                const $partRow = $this.$('.jsPartRow[data-id="' + partId + '"]'),
                      partName = $partRow.find('.row-name').text().trim(),
                      quantity = $partRow.find('.jsConfiguratorDefaultQuantity').val() || 1,
                      groupId  = $partRow.data('group-id');

                $this._updateSummaryPart($this, groupId, partId, partName, quantity);
            });

            options.forEach(optionId => {
                const $optionRow = $this.$('.jsOptionRow[data-id="' + optionId + '"]'),
                      optionName = $optionRow.find('.row-name').text().trim(),
                      partId = $optionRow.data('part-id'),
                      $partRow = $this.$('.jsPartRow[data-id="' + partId + '"]'),
                      quantity = $partRow.find('.jsConfiguratorDefaultQuantity').val() || 1,
                      groupId = $partRow.data('group-id'),
                      partName = $partRow.find('.row-name').text().trim() + ' (' + optionName + ')';

                $this._updateSummaryPart($this, groupId, partId + '-' + optionId, partName, quantity);
            });

            $this.$('.jsSummaryValue').each(function () {
                if ($(this).text().trim() === '') {
                    $(this).parent().hide();
                }
            });
        },

        /**
         * Clear summary group.
         *
         * @param $this
         * @param groupId
         */
        _clearSummaryGroup: function ($this, groupId) {
            const $summaryGroup = $this.$('.jsConfigurationSummary').find('[data-group-id="' + groupId + '"]');
            $summaryGroup.hide().find('.jsSummaryValue').html('');
        },

        /**
         * Remove part from summary.
         *
         * @param $this
         * @param partId
         */
        _removeSummaryPart: function ($this, partId) {
            const $summaryContent = $this.$('.jsConfigurationSummary').find('[data-part-id^="' + partId + '"]');

            if ($summaryContent.siblings().length === 0) {
                $summaryContent.closest('li').hide();
            }

            $summaryContent.remove();
        },

        /**
         * Add or update summary block.
         *
         * @param $this
         * @param groupId
         * @param partId
         * @param partName
         * @param quantity
         */
        _updateSummaryPart: function ($this, groupId, partId, partName, quantity) {
            const $group = $this.$('.jsConfigurationSummary').find('[data-group-id="' + groupId + '"]'),
                  $summaryValue = $group.find('.jsSummaryValue'),
                  $part = $summaryValue.children('[data-part-id=' + partId + ']'),
                  value = quantity > 1 ? `${quantity} x ${partName}` : partName,
                  content = `<div data-part-id="${partId}">${value}</span>`;

            if ($part.length) {
                $part.html(content);
            } else {
                $summaryValue.append(content);
                $group.show();
            }
        },

        /**
         * Update total product price.
         *
         * @param $this
         */
        _updateTotalPrice: function ($this) {
            let total = 0;

            $this.$('.jsCheckDefault').filter(':checked').each(function () {
                const $input = $(this),
                      type = $input.data('type'),
                      $row = $input.closest(type === 'option' ? '.jsOptionRow' : '.jsPartRow'),
                      rowPrice = $row.find('.simpleType').data('simpletype-value');

                let quantity = $row.find('.jsConfiguratorDefaultQuantity').val() || 1;
                if (type === 'option') {
                    const $partRow = $this.$('.jsPartRow[data-id="' + $row.data('part-id') + '"]');
                    quantity = $partRow.find('.jsConfiguratorDefaultQuantity').val() || 1;
                }

                total += rowPrice * quantity;
            });

            $this.$('.jsConfigurePrice').val(Math.ceil(total));
        },

        /**
         * On change part quantity.
         *
         * @param e
         * @param $this
         */
        'change .jsConfiguratorDefaultQuantity': function (e, $this) {
            const $select  = $(this),
                  $partRow = $select.closest('.jsPartRow'),
                  $checked = $partRow.find('.jsCheckDefault').filter(':checked');

            if ($checked.length === 0) {
                return;
            }

            let partId = $partRow.data('id')
                partName = $partRow.find('.row-name').text().trim();
            if ($checked.data('type') === 'option') {
                const $optionRow = $checked.closest('.jsOptionRow');
                partId += '-' + $optionRow.data('id');
                partName += ' (' + $optionRow.find('.row-name').text().trim() + ')';
            }

            $this._updateSummaryPart($this, $partRow.data('group-id'), partId, partName, $select.val());
            $this._updateTotalPrice($this);
        },

        /**
         * Setup default product part.
         *
         * @param e
         * @param $this
         */
        'change .jsCheckDefault': function (e, $this) {
            const $input = $(this),
                  type = $input.data('type'),
                  $row = $input.closest(type === 'option' ? '.jsOptionRow' : '.jsPartRow');

            let partId = $row.data(type === 'option' ? 'part-id' : 'id');

            const $partRow = type === 'option' ? $this.$('.jsPartRow[data-id="' + partId + '"]') : $row,
                  groupId = $partRow.data('group-id');

            let partName = $partRow.find('.row-name').text().trim();

            const isCheckbox = $input.is('[type="checkbox"]');
            if (isCheckbox) {
                if (!$input.is(':checked')) {
                    $this._removeSummaryPart($this, partId);
                    $row.find('.jsPartCheck, .jsPartMini').removeAttr('readonly');
                } else {
                    $this._updateSummaryPart($this, groupId, partId, partName, $partRow.find('.jsConfiguratorDefaultQuantity').val() || 1);
                    $row.find('.jsPartCheck, .jsPartMini').prop('checked', true).attr('readonly', 'readonly');
                }

                $this._updateTotalPrice($this);

                return;
            }

            const $table = $input.closest('table'),
                  quantity = $partRow.find('.jsConfiguratorDefaultQuantity').val() || 1;

            if ($input.data('type') === 'option') {
                const isMultipleEnabled = $table.closest('.jsGroupContent').find('.jsEnableMultiple').is(':checked');
                if (!isMultipleEnabled) {
                    $table.find('.jsCheckDefault').not($input).prop('checked', false);
                    $table.find('.jsPartCheck, .jsOptionCheck, .jsPartMini, .jsOptionMini').removeAttr('readonly');
                    $this._clearSummaryGroup($this, groupId);
                } else {
                    $table.find('.jsOptionCheck, .jsOptionMini').filter('[value="' + $input.data('part-id') + '"]').removeAttr('readonly');
                    $this._removeSummaryPart($this, partId);
                }

                $row.find('.jsOptionCheck, .jsOptionMini').prop('checked', true).attr('readonly', 'readonly');

                partId += '-' + $row.data('id');
                partName += ' (' + $row.find('.row-name').text().trim() + ')';
            } else {
                $table.find('.jsCheckDefault').not($input).prop('checked', false);
                $table.find('.jsPartCheck, .jsOptionCheck, .jsPartMini, .jsOptionMini').removeAttr('readonly');
                $this._clearSummaryGroup($this, groupId);
            }

            $partRow.find('.jsPartCheck, .jsPartMini').prop('checked', true).attr('readonly', 'readonly');

            $this._updateSummaryPart($this, groupId, partId, partName, quantity);
            $this._updateTotalPrice($this);
        },

        /**
         * Readonly input prevent change state.
         *
         * @param e
         * @param $this
         */
        'click input[readonly="readonly"]': function (e, $this) {
            e.preventDefault();
        },

        /**
         * Reset default parts.
         *
         * @param e
         * @param $this
         */
        'click .jsResetDefault': function (e, $this) {
            e.preventDefault();

            const $target = $(this),
                  $table = $target.closest('table');

            $table.find('.jsCheckDefault').prop('checked', false);
            $table.find('input[readonly]').removeAttr('readonly');

            $this._clearSummaryGroup($this, $target.data('group-id'));
            $this._updateTotalPrice($this);
        },

        /**
         * On change part checkbox.
         *
         * @param e
         * @param $this
         */
        'change .jsPartCheck': function (e, $this) {
            const $checkbox = $(this),
                  $table = $checkbox.closest('table');

            if (!$checkbox.is(':checked')) {
                $checkbox.closest('tr').find('.jsPartMini').prop('checked', false);
                $table.find('.jsOptionMini[value="' + $checkbox.val() + '"]').prop('checked', false);
            }

            $table.find('.jsOptionCheck[value="' + $checkbox.val() + '"]').prop('checked', $checkbox.is(':checked'));
        },

        /**
         * On change option checkbox.
         *
         * @param e
         * @param $this
         */
        'change .jsOptionCheck': function (e, $this) {
            const $checkbox = $(this),
                  $table = $checkbox.closest('table');

            if (!$checkbox.is(':checked')) {
                $checkbox.closest('tr').find('.jsOptionMini').prop('checked', false);
                if ($table.find('.jsOptionCheck[value="' + $checkbox.val() + '"]:checked').length === 0) {
                    $table.find('.jsPartCheck[value="' + $checkbox.val() + '"], .jsPartMini[value="' + $checkbox.val() + '"]').prop('checked', false);
                }
            } else {
                $table.find('.jsPartCheck[value="' + $checkbox.val() + '"]').prop('checked', true);
            }
        },

        /**
         * On change part mini checkbox.
         *
         * @param e
         * @param $this
         */
        'change .jsPartMini': function (e, $this) {
            const $checkbox = $(this);

            if (!$checkbox.closest('tr').find('.jsPartCheck').is(':checked')) {
                $checkbox.prop('checked', false);
                return;
            }

            if ($checkbox.is(':checked')) {
                $checkbox.closest('table').find('.jsOptionCheck[value="' + $checkbox.val() + '"]').filter(':checked').each(function () {
                    $(this).closest('tr').find('.jsOptionMini[value="' + $checkbox.val() + '"]').prop('checked', true)
                });
            } else {
                $checkbox.closest('table').find('.jsOptionMini[value="' + $checkbox.val() + '"]').prop('checked', false);
            }
        },

        /**
         * On change option checkbox mini.
         *
         * @param e
         * @param $this
         */
        'change .jsOptionMini': function (e, $this) {
            const $checkbox = $(this);

            if (!$checkbox.closest('tr').find('.jsOptionCheck').is(':checked')) {
                $checkbox.prop('checked', false);
                return;
            }

            const $table = $checkbox.closest('table');

            if (!$checkbox.is(':checked')) {
                if ($table.find('.jsOptionMini[value="' + $checkbox.val() + '"]:checked').length === 0) {
                    $table.find('.jsPartMini[value="' + $checkbox.val() + '"]').prop('checked', false);
                }
            } else {
                $table.find('.jsPartMini[value="' + $checkbox.val() + '"]').prop('checked', true);
            }
        },

        /**
         * On change parts check all checkbox.
         *
         * @param e
         * @param $this
         */
        'change .jsPartCheckAll': function (e, $this) {
            const $input = $(this),
                  $table = $input.closest('table');

            $table.find('.jsPartCheck, .jsOptionCheck').not('[readonly]').prop('checked', $input.prop('checked'));

            if (!$input.is(':checked')) {
                $table.find('.jsPartMini, .jsOptionMini').not('[readonly]').prop('checked', false);
            }
        },

        /**
         * On change enable multiple selection checkbox.
         *
         * @param e
         * @param $this
         */
        'change .jsEnableMultiple': function (e, $this) {
            const $checkbox = $(this),
                  $group = $checkbox.closest('.jsGroupContent'),
                  $togglers = $group.find('.jsPartRow').find('.jsCheckDefault');

            if ($checkbox.is(':checked')) {
                $togglers.attr('type', 'checkbox');
                $group.find('.jsCanDeselected').addClass('invisible').find('select').val('-1');
            } else {
                $group.find('.jsCheckDefault').filter(':checked').not(':eq(0)').prop('checked', false);
                $togglers.attr('type', 'radio');
                $group.find('.jsCheckDefault').filter(':checked').trigger('change');
                $group.find('.jsCanDeselected').removeClass('invisible');
            }
        },

        /**
         * On click set by reference button.
         *
         * @param e
         * @param $this
         */
        'click .jsSetByExample': function (e, $this) {
            const link = $(this).data('src'),
                  id = $(this).data('id');
            $.fancybox.open({
                src: link,
                type: 'iframe',
                opts: {
                    afterLoad: (instance, current) => {
                        const $items = current.$iframe.contents().find('.jsChooseItem');

                        $items.on('click', function (e) {
                            e.preventDefault();

                            const $target = $(this),
                                  itemKey = $target.data('id'),
                                  sourceId = itemKey.match(/\d+/) && itemKey.match(/\d+/)[0];

                            instance.close();
                            $this._openLoader();

                            $.ajax({
                                'url'      : '/administrator/index.php',
                                'dataType' : 'json',
                                'type'     : 'POST',
                                'data'     : {
                                    'tmpl'    : 'component',
                                    'option'  : 'com_hyperpc',
                                    'task'    : 'moysklad_product.copy-configuration',
                                    'source'  : sourceId,
                                    'target'  : id
                                },
                                'headers'  : {
                                    'X-CSRF-Token': Joomla.getOptions('csrf.token') || ''
                                },
                            })
                            .done(function (response) {
                                if (response.success) {
                                    if (response.message) {
                                        $('#system-message-container').html(`
                                            <div class="alert alert-success">
                                                ${response.message}
                                            </div>
                                        `);
                                    }
                                    window.location.reload();
                                } else {
                                    if (response.message) {
                                        $('#system-message-container').html(`
                                            <div class="alert alert-danger">
                                                ${response.message}
                                            </div>
                                        `);
                                    }
                                    $this._hideLoader();
                                }
                            })
                            .fail(function (xjr, status, error) {
                                $('#system-message-container').html(`
                                    <div class="alert alert-danger">
                                        ${status}: ${error}
                                    </div>
                                `);
                                $this._hideLoader();
                            })
                            .always(function () {});
                        });
                    }
                }
            });
        },

        /**
         * Toggle part property.
         *
         * @param e
         * @param $this
         *
         * @deprecated
         */
        'click .jsPartProperties' : function (e, $this) {
            var element          = $(this),
                miniConfigInputs = element.closest('.tab-pane').find('.hp-item-config-mini input');

            if (element.prop('checked')) {
                miniConfigInputs.each(function () {
                    $(this)
                        .prop('checked', false)
                        .removeAttr('checked')
                        .attr('readonly', 'readonly');
                });
            } else {
                miniConfigInputs.each(function () {
                    $(this).removeAttr('readonly');
                });
            }
        }
    });
});
