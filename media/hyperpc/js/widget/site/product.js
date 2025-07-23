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

    JBZoo.widget('HyperPC.SiteProduct', {
        'id'       : null,
        'vat'      : 20
    }, {

        /**
         * @typedef {Object} PartData
         * @property {string[]} advantages
         * @property {string} desc part short description
         * @property {string|number} folder_id group or folder id
         * @property {string} image image src path
         * @property {string} name part name
         * @property {string|number} option_id part option id
         * @property {string|number} part_id part id
         * @property {string} url_view view url
         * @property {string} url_change change url
         */

        /**
         * Initialize widget.
         *
         * @param $this
         */
        init: function ($this) {
            if (!window.HpProductDefaultParts) {
                $this.$('.jsItemChangeButton').parent().attr('hidden', 'hidden');
            }

            $(document).on('partupdated', function (e, data) {
                /** @type {PartData} data  */
                $this._updatePart($this, data);
            });
        },

        /**
         * Recount product price by changable parts.
         *
         * @param $this
         */
        _recountPrice: function ($this) {
            const args = {};

            $this.$('.jsCanBeChanged').each(function (i) {
                const $partRow = $(this);

                args[i] = {
                    'part_id'   : $partRow.find('.jsGroupPartValue').val(),
                    'option_id' : $partRow.find('.jsGroupOptionValue').val()
                };

                const defaultPartItemkey = $partRow.data('defaultItemkey');

                if (window.parent.HpProductDefaultParts && window.parent.HpProductDefaultParts[defaultPartItemkey]) {
                    args[i].default = {
                        'part'   : window.parent.HpProductDefaultParts[defaultPartItemkey].part_id,
                        'option' : window.parent.HpProductDefaultParts[defaultPartItemkey].option_id,
                    };
                }
            });

            if (Object.keys(args).length) {
                $.ajax({
                    'url'      : '/index.php',
                    'dataType' : 'json',
                    'method'   : 'POST',
                    'data'     : {
                        'option'     : 'com_hyperpc',
                        'format'     : 'raw',
                        'args'       : args,
                        'method'     : 'toggle-part',
                        'tmpl'       : 'component',
                        'task'       : 'moysklad_product.recount-price',
                        'product-id' : $this.getOption('id')
                    }
                })
                .done(function (data) {
                    if (data.result === true) {
                        $this.$('.jsItemPrice')
                            .find('.simpleType')
                            .data('simpletype-value', data.price)
                            .find('.simpleType-value')
                            .attr('content', data.price)
                            .text($this._priceFormat(data.price));

                        const vat = $this._calculateVat($this, data.price);
                        $this.$('.jsItemVat')
                            .find('.simpleType')
                            .data('simpletype-value', vat)
                            .find('.simpleType-value')
                            .attr('content', vat)
                            .text($this._priceFormat(vat));

                        $this.$('.jsItemMonthlyPayment')
                            .find('.simpleType')
                            .data('simpletype-value', data.monthly)
                            .find('.simpleType-value')
                            .attr('content', data.monthly)
                            .text($this._priceFormat(data.monthly));
                    } else {
                        UIkit.notification('Ajax call returned an error', 'danger');
                    }
                })
                .fail(function (xjr, error) {
                    const msg = error.msg || 'Connection error';
                    UIkit.notification(msg, 'danger');
                });
            }
        },

         /**
          * 
          * @param $this 
          * @param {PartData} partData 
          */
        _updatePart: function ($this, partData) {
            let itemKey = 'position-';

            itemKey += partData.part_id;
            if (partData.option_id) {
                itemKey += '-' + partData.option_id;
            }

            const $partRow = $this.$('.hp-group-row-' + partData.folder_id);

            if ($partRow.data('defaultItemkey') === itemKey) {
                $partRow.removeAttr('data-changed-item')
                        .find('.jsItemResetButton').parent().attr('hidden', 'hidden');
            } else {
                $partRow.attr('data-changed-item', itemKey)
                        .find('.jsItemResetButton').parent().removeAttr('hidden');
            }

            // Change content
            $partRow.find('.hp-equipment-part__name').text(partData.name);
            $partRow.find('.hp-equipment-part__desc').text(partData.desc);
            $partRow.find('.hp-equipment-part__img').attr('src', partData.image);
            $partRow.find('.jsItemMoreButton').attr('href', partData.url_view);

            // Change link
            $partRow.find('.jsItemChangeButton').data('href', partData.url_change);

            // Set hidden inputs
            $partRow.find('.jsGroupPartValue').val(partData.part_id);
            $partRow.find('.jsGroupOptionValue').val(partData.option_id);

            // Change specification table
            const $partInSpec = $this.$('.jsProductSpecification').find('.hp-group-' + partData.group_id).find('.hp-spec-item');
            $partInSpec.html(partData.name);
            if (partData.advantages.length) {
                const advantages = [];
                partData.advantages.forEach(function (value) {
                    advantages.push('<li>' + value + '</li>');
                });
                $partInSpec.append(
                    '<ul class="uk-list uk-list-collapse uk-text-muted uk-text-small uk-margin-remove-top">' +
                        advantages.join('\n') +
                    '</ul>'
                );
            }

            $this._recountPrice($this);

            if ($this._isDefaultConfig($this)) {
                $this._checkCartButtons($this);
            } else {
                $this.$('.jsCartButtons').removeClass('hp-element-in-cart');
            }
        },

        /**
         * Checks cart buttons actual state
         * 
         * @param $this
         */
        _checkCartButtons: function ($this) {
            const $cartButtonsWrapper = $this.$('.jsCartButtons'),
                  itemKey = $cartButtonsWrapper.eq(0).data('itemkey'),
                  matches = itemKey.match(/^(position-\d+)(-\d+)?$/);

            $cartButtonsWrapper.removeClass('hp-element-in-cart');

            if ($this._isDefaultConfig($this)) {
                // Remove config id from the itemkey
                if (matches[2]) {
                    const defaultItemkey = matches[1];
                    $cartButtonsWrapper.data('itemkey', defaultItemkey);
                }

                $(document).trigger('updatecartbuttons');
            }
        },

        /**
         * Does the product have changed parts
         * 
         * @param $this
         * 
         * @returns {boolean}
         */
        _isDefaultConfig: function ($this) {
            return $this.$('.jsCanBeChanged').filter('[data-changed-item]').length <= 0;
        },

        /**
         * Price format
         *
         * @param   price
         *
         * @returns {*|string}
         */
        _priceFormat: function (price) {
            const moneyConfig = window.Joomla.getOptions('moneyConfig') || {
                'decimal_sep': '.',
                'thousands_sep': ' ',
                'num_decimals': 0
            };

            return window.JBZoo.numFormat(price, moneyConfig.num_decimals, moneyConfig.decimal_sep, moneyConfig.thousands_sep);
        },

        /**
         * Calculate VAT
         * 
         * @param $this
         * @param {number} price
         * 
         * @returns {number}
         */
        _calculateVat: function ($this, price) {
            const vat = $this.getOption('vat');
            return Math.round((price / (100 + vat)) * vat);
        },

        /**
         * Reset group default part item.
         *
         * @param e
         * @param $this
         */
        'click .jsItemResetButton': function (e, $this) {
            const $button  = $(this),
                  $partRow = $button.closest('.hp-equipment-part'),
                  itemKey  = $partRow.data('defaultItemkey');

            if (window.HpProductDefaultParts && window.HpProductDefaultParts[itemKey]) {
                /** @type {PartData} */
                const data = window.HpProductDefaultParts[itemKey];

                $this._updatePart($this, data);
            }

            $this._checkCartButtons($this);

            e.preventDefault();
        }

    });
});
