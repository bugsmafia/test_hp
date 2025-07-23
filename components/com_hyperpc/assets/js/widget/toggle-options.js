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

    JBZoo.widget('HyperPC.SiteToggleOptions', {
        'labelInStock'      : 'In stock',
        'labelOutOfStock'   : 'Out of stock',
        'labelPreOrder'     : 'Preorder',
        'labelDiscontinued' : 'Discontinued',

        'addToCartDefaultContent' : 'Add to Cart',
        'addToCartInstockContent' : 'Add to Cart',

        'pickupFromTheStore' : 'Pick up from the store',

        'vat' : 20
    }, {

        btnDisabledClass: 'uk-disabled tm-background-gray-25 uk-text-muted',

        /**
         * Initialize widget.
         *
         * @param $this
         */
        init: function ($this) {},

        /**
         * Update option availability.
         *
         * @param $this
         *
         * @private
         */
        _updateAvailability: function ($this) {
            const $currentOption = $this.$('.jsPartOptions :selected'),
                  availability = $currentOption.data('availability');
            switch (availability) {
                case 'InStock':
                case 'PreOrder':
                    $this._unblockPurchaseButtons($this);
                    break;
                case 'OutOfStock':
                case 'Discontinued':
                    $this._blockPurchaseButtons($this);
                    break;
            }

            $this._setAvailabilityState($this, availability);

            const storesAvailability    = $currentOption.data('stores'),
                  pickupFromTheStoreStr = $currentOption.data('pickupStr');
            $this._setStoreAvailabilityState($this, storesAvailability, pickupFromTheStoreStr);
        },

        /**
         * Set availability state
         * 
         * @param $this
         * @param {string} state
         */
        _setAvailabilityState: function ($this, state) {
            const $buyButtons = $this.$('.hp-part-purchase, .hp-part-head__purchase').find('.jsAddToCart'),
                  $availabilityLabel = $this.$('.jsAvailabilityLabel'),
                  $availabilityConditionItem = $this.$('.jsAvailabilityConditionItem'),
                  $availabilityConditionItemText = $availabilityConditionItem.find('.hp-conditions-item__text');

            [$availabilityLabel, $availabilityConditionItemText].forEach(function($collection) {
                $collection
                    .text($this.getOption('label' + state))
                    .removeClass('uk-text-success uk-text-warning uk-text-danger');
            });

            switch (state) {
                case 'InStock':
                    $buyButtons.html($this.getOption('addToCartInstockContent'));
                    $availabilityLabel.addClass('uk-text-success');
                    $availabilityConditionItem.find('[uk-icon]').attr('uk-icon', 'check');
                    $availabilityConditionItem
                        .find('.jsConditionsDeliveryLink').removeAttr('hidden').siblings().attr('hidden', 'hidden');
                    $availabilityConditionItemText.addClass('uk-text-success');
                    break;
                case 'PreOrder':
                    $buyButtons.html($this.getOption('addToCartDefaultContent'));
                    $availabilityLabel.addClass('uk-text-warning');
                    $availabilityConditionItem.find('[uk-icon]').attr('uk-icon', 'clock');
                    $availabilityConditionItem
                        .find('.jsConditionsPreOrderSub').removeAttr('hidden').siblings().attr('hidden', 'hidden');
                    $availabilityConditionItemText.addClass('uk-text-warning');
                    break;
                case 'OutOfStock':
                    $buyButtons.html($this.getOption('addToCartDefaultContent'));
                    $availabilityConditionItem.find('[uk-icon]').attr('uk-icon', 'clock');
                    $availabilityConditionItem
                        .find('.jsConditionsOutOfStockSub').removeAttr('hidden').siblings().attr('hidden', 'hidden');
                    break;
                case 'Discontinued':
                    $buyButtons.html($this.getOption('addToCartDefaultContent'));
                    $availabilityLabel.addClass('uk-text-danger');
                    $availabilityConditionItem.find('[uk-icon]').attr('uk-icon', 'ban');
                    $availabilityConditionItem
                        .find('.jsConditionsDiscontinuedSub').removeAttr('hidden').siblings().attr('hidden', 'hidden');
                    $availabilityConditionItemText.addClass('uk-text-danger');
                    break;
            }
        },

        /**
         * Set store availability state.
         *
         * @param $this
         * @param {object} storesAvailability
         * @param {string} pickupFromTheStoreStr
         */
        _setStoreAvailabilityState: function ($this, storesAvailability, pickupFromTheStoreStr) {
            storesAvailability    = storesAvailability || {};
            pickupFromTheStoreStr = pickupFromTheStoreStr || $this.getOption('pickupFromTheStore');

            $this.$('.jsPickupFromTheStoreStr').text(pickupFromTheStoreStr);

            const $stores = $('.jsConditionsDeliveryStores').children();

            $stores.each(function () {
                const $store = $(this),
                      storeId = $store.data('storeid');

                let date = '';
                if (typeof storesAvailability[storeId] !== 'undefined' &&
                    typeof storesAvailability[storeId]['pickup'] !== 'undefined' &&
                    typeof storesAvailability[storeId]['pickup']['value'] !== 'undefined') {
                    date = storesAvailability[storeId]['pickup']['value'];
                }

                $store.find('.jsConditionsDeliveryStoreAvailability').html(date);
            });
        },

        /**
         * Block purchase buttons
         *
         * @param $this
         */
        _blockPurchaseButtons: function ($this) {
            $this.$('.hp-part-purchase, .hp-part-head__purchase').find('.jsAddToCart').addClass($this.btnDisabledClass);
        },

        /**
         * Unblock purchase buttons
         *
         * @param $this
         */
        _unblockPurchaseButtons: function ($this) {
            $this.$('.hp-part-purchase, .hp-part-head__purchase').find('.jsAddToCart').removeClass($this.btnDisabledClass);
        },

        /**
         * Update buy buttons.
         *
         * @param $this
         * @param optionId
         * @param isIncart
         */
        _updateBuyButton: function ($this, optionId, isIncart) {
            const $buyButtons = $this.$('.hp-part-purchase, .hp-part-head__purchase').find('.jsAddToCart'),
                  partId = $buyButtons.eq(0).data('id'),
                  itemKey = 'part-' + partId + '-' + optionId;

            $buyButtons.data('default-option', optionId)
                       .attr('data-default-option', optionId)
                       .attr('onclick', 'gtmProductAddToCart(\'' + itemKey + '\');')
                       .parent().attr('data-itemkey', itemKey).data('itemkey', itemKey);

            if (isIncart) {
                $buyButtons.parent().addClass('hp-element-in-cart');
            } else {
                $buyButtons.parent().removeClass('hp-element-in-cart');
            }
        },

        /**
         * Update credit calculation.
         *
         * @param   $this
         * @param   $optionSelected
         */
        _updateCreditCalculation: function($this, $optionSelected) {
            const $calculateCreditLink = $this.$('.jsItemMonthlyPayment').children().filter('a');
            if ($calculateCreditLink.length) {
                const optionId = $optionSelected.attr('value'),
                      price = $optionSelected.data('price');
                let paramsString = $calculateCreditLink.get(0).search;

                paramsString = paramsString
                                   .replace(/option_id=\d+/, 'option_id=' + optionId)
                                   .replace(/price=\d+/, 'price=' + price)
                $calculateCreditLink
                    .attr(
                        'href',
                        $calculateCreditLink.get(0).pathname + paramsString
                    );
            } else {
                $this.$('.jsItemMonthlyPayment')
                    .find('.simpleType-value')
                    .text($this._priceFormat($optionSelected.data('monthly-payment')));
            }
        },

        /**
         * Get modal error message.
         */
        _getModalError: function () {
            const $modalHtml =
                $('<div class="uk-modal">' +
                    '<div class="uk-modal-dialog hp-dialog-compare">' +
                        '<div class="uk-modal-body uk-grid-small uk-flex-middle tm-background-gray-15" uk-grid>' +
                            '<a class="uk-modal-close uk-link-muted uk-flex-last@s" uk-icon="icon: info; ratio: 1.5"></a>' +
                            '<span class="uk-text-danger uk-visible@s" uk-icon="icon: check; ratio: 1.5"></span>' +
                            '<div class="uk-width-1-1 uk-width-expand@s">' +
                                'Произошла ошибка, попробуйте еще раз.' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                '</div>');

            UIkit.modal($modalHtml).show();
        },

        /**
         * Update prices
         * 
         * @param $this
         * @param $optionSelected
         * @param $questionForm
         */
        _updatePrices: function ($this, $optionSelected, $questionForm) {
            const price = $optionSelected.data('price');

            $this._updatePriceElement($this, $this.$('.jsItemPrice'), price);
            $this._updatePriceElement($this, $this.$('.jsItemVat'), $this._calculateVat($this, price));
            $this._updateCreditCalculation($this, $optionSelected);

            $questionForm.find('[name="hp-item_price"]').val($this._priceFormat(price));
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
         * Update simpletype price element
         * 
         * @param $this
         * @param $el
         * @param {number} value
         */
         _updatePriceElement: function ($this, $el, value) {
            $el.find('.simpleType-value').text($this._priceFormat(value));
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
         * Toggle part option.
         *
         * @param e
         * @param $this
         */
        'change .jsPartOptions': function (e, $this) {
            $this._updateAvailability($this);

            const $optionSelected = $('option:selected', this),
                  optionId        = parseInt($(this).val()),
                  vendorCode      = $optionSelected.data('vendorCode'),
                  isInCart        = $optionSelected.data('inCart'),
                  imageSrc        = $optionSelected.data('image'),
                  pickingDates    = $optionSelected.data('stores') || {};

            $this.$('.jsOptionFields[data-option-id="' + optionId +'"]')
                .removeAttr('hidden').attr('aria-hidden', 'false')
                .siblings('.jsOptionFields').attr('hidden', '').attr('aria-hidden', 'true');

            $this.$('.jsProductVendorCode').find('span').text(vendorCode);

            $this.$('.hp-part-image__image').attr('src', imageSrc);

            $('.hp-part-option').removeClass('hp-part-option--selected');
            $('.hp-part-option[data-option-id="' + optionId + '"]').addClass('hp-part-option--selected');

            const $questionForm = $('.jsProductQuestionModal').find('form');

            $this._updatePrices($this, $optionSelected, $questionForm);

            $this._updateBuyButton($this, optionId, isInCart);

            $this.$('.hp-compare-btn-wrapper')
                .find('.jsCompareAdd')
                .addClass('uk-hidden')
                .filter('[data-option-id="' + optionId + '"]')
                .removeClass('uk-hidden');

            $('.jsGeoDelivery').trigger('datesUpdated', pickingDates);

            $questionForm.find('[name="hp-item_name"]').val($this.$('h1').text().trim() + ' ' + $optionSelected.text().trim());
        }

    });
});
