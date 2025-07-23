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
 * @author     Artem Vyshnevskiy
 */

jQuery(function ($) {

    JBZoo.widget('HyperPC.SiteCart', {
        'creditMinSum' : 10000,
        'creditMaxSum' : 0,
        'orderMinSum'  : 3000,
        'creditMaxLimitMsg' : null,
        'creditMinLimitMsg' : null,
        'orderMinLimitMsg'  : null,
        'vat' : 20,
        'gtmAddCallback'    : '',
        'gtmRemoveCallback' : '',

        'removeFromCartConfirm' : 'Remove an item from the cart?',
        'clearCartConfirm' : 'Are you sure you want to remove all items from your cart?'
    }, {

        percentRate : false,
        dataItems   : [],
        promoType   : 0,
        rate        : 0,

        /**
         * Initialize widget.
         *
         * @param $this
         */
        init: function ($this) {
            const hash = location.hash.trim();
            if (hash !== '#') {
                const $fieldsBtn = $this.$(hash + '-fields');
                if ($fieldsBtn.length > 0) {
                    $fieldsBtn.trigger('click');
                }
            }

            window.cartItems = window.cartItems || {};
            $this.dataItems = $.extend({}, window.cartItems);

            const pageAccessedByReload = (
                (window.performance.navigation && window.performance.navigation.type === 1) ||
                window.performance
                    .getEntriesByType('navigation')
                    .map((nav) => nav.type)
                    .includes('reload')
            );

            window.dataLayer = window.dataLayer || [];

            if (!pageAccessedByReload) {
                const moneyConfig = window.Joomla.getOptions('moneyConfig') || {'iso_code': 'RUB'};

                const dataGA4 = {
                    'event': 'view_cart',
                    'ecommerce': {
                        'currency': moneyConfig.iso_code,
                        'items': $this._getEcommerceItemsGA4($this)
                    }
                };

                dataLayer.push({ecommerce: null});
                dataLayer.push(dataGA4);
            }

            $(window).on('storage', function (e) {
                switch (e.key) {
                    case 'hp_update_cart_now':
                        if (localStorage.getItem('hp_update_cart_now')) {
                            $this._updateCartItems($this);

                            localStorage.removeItem('hp_update_cart_now');
                        }
                        break;
                    case 'hp_cart_items':
                        $this._updateCartItems($this);
                        break;
                }
            });

            document.addEventListener('hpuserloggedin', (e) => {
                const data = e.detail || {};
                $this.$('.jsFormToken').children('input').attr('name', data.token);
            });

            $this._checkCreditAvailability($this);
            $this._checkCreditLimits($this);

            $(document).on('deliveryChange', function (e) {
                $this._checkCreditLimits($this);
            });

            $this.$('.jsToSecondStep').one('click', function (e) {
                $this._onInitiateCheckout($this);
            });
        },

        /**
         * On initiate checkout
         *
         * @param $this
         */
        _onInitiateCheckout: function ($this) {
            const moneyConfig = window.Joomla.getOptions('moneyConfig') || {'iso_code': 'RUB'};

            const dataGA4 = {
                'event': 'begin_checkout',
                'ecommerce': {
                    'currency': moneyConfig.iso_code,
                    'items': $this._getEcommerceItemsGA4($this)
                }
            };

            dataLayer.push({ecommerce: null});
            dataLayer.push(dataGA4);

            $.ajax({
                'url'  : '/index.php',
                'type' : 'POST',
                'data' : {
                    'option' : 'com_hyperpc',
                    'format' : 'raw',
                    'tmpl'   : 'component',
                    'task'   : 'cart.initiate-checkout',
                },
            });
        },

        /**
         * Get ecommerce data in Google Analytics 4 (GA4) format
         *
         * @param $this
         *
         * @returns {Array}
         */
        _getEcommerceItemsGA4: function ($this) {
            const itemsData = Object.values($this.dataItems);
            const result = [];

            itemsData.forEach(function (itemData) {
                const ga4Item = {
                    'item_name'      : itemData.name,
                    'item_id'        : itemData.id,
                    'item_brand'     : itemData.brand || '',
                    'quantity'       : itemData.quantity,
                    'price'          : itemData.price,
                    'item_list_id'   : itemData.list_id || '',
                    'item_list_name' : itemData.list_name || ''
                };

                const categories = itemData.categories.slice().reverse();
                for (let i = 0; i < categories.length; i++) {
                    const propKey = 'item_category' + (i > 0 ? (i + 1) : '');
                    ga4Item[propKey] = categories[i];
                }

                result.push(ga4Item);
            });

            return result;
        },

        /**
         * Reload cart items
         * 
         * @param $this
         * 
         * @todo ajax reload and update
         */
        _updateCartItems: function ($this) {
            document.location.reload();
        },

        /**
         * Calculate monthly payment.
         *
         * @param {number} price
         * @param {number} [rate=18.00]
         * @param {number} [loanTerm=12]
         * @param {number} [downPayment=0]
         *
         * @returns {number}
         */
        _calculateMonthlyPayment: function (price, rate, loanTerm, downPayment) {
            rate        = typeof rate === 'undefined' ? 20.00 : rate;
            loanTerm    = typeof loanTerm === 'undefined' ? 36 : loanTerm;
            downPayment = downPayment || 0;

            let monthlyPayment = 0;
            if (rate > 0) {
                monthlyPayment = (((price - downPayment) * rate) / 1200) / (1 - Math.pow((1 / (1 + (rate / 1200))), loanTerm));
            } else {
                monthlyPayment = (price - downPayment) / loanTerm;
            }

            return Math.ceil(monthlyPayment);
        },

        /**
         * Change item quantity.
         *
         * @param $btnElement
         * @param type
         * @param $this
         */
        _changeItemQuantity: function ($btnElement, type, $this) {
            type = type || 'plus';

            const $quantityWrapper = $btnElement.closest('.jsQuantityWrapper'),
                  $quantity        = $quantityWrapper.find('.jsItemQuantity'),
                  quantityVal      = JBZoo.toInt($quantity.val()),
                  oldQuantityVal   = $quantity.attr('value');

            let newQuantityVal = Math.max(quantityVal, 1);

            switch (type) {
                case 'plus':
                    newQuantityVal = quantityVal + 1;
                    break;
                case 'minus':
                    newQuantityVal = quantityVal - 1;
                    break;
            }

            if (newQuantityVal !== quantityVal) {
                $quantity.val(newQuantityVal);
            }

            if (newQuantityVal === 1) {
                $quantityWrapper.find('.jsItemMinus').addClass('uk-disabled');
            } else {
                $quantityWrapper.find('.jsItemMinus').removeClass('uk-disabled');
            }

            const $itemRow = $btnElement.closest('.jsCartItemRow');

            const sendArgs = {
                'quantity' : newQuantityVal,
                'id'       : $itemRow.data('id'),
                'type'     : $itemRow.data('type'),
                'option'   : $itemRow.data('option')
            };

            if (typeof $itemRow.data('saved-configuration') !== 'undefined') {
                sendArgs.type = 'configuration';
                sendArgs.savedConfiguration = $itemRow.data('saved-configuration');
            }

            const priceForOne = $itemRow.find('.jsPriceForOne').val();

            $this._openLoader();
            $.ajax({
                'url'      : '/index.php',
                'type'     : 'POST',
                'dataType' : 'json',
                'data'     : {
                    'option'  : 'com_hyperpc',
                    'format'  : 'raw',
                    'tmpl'    : 'component',
                    'task'    : 'cart.addToCart',
                    'args'    : sendArgs,
                    'addType' : 'item-replace'
                },
            })
            .done(function (data) {
                if (data.result === true) {
                    const itemKey = $this._getItemKey($itemRow);

                    window.cartItems = window.cartItems || {};
                    if (typeof window.cartItems[itemKey] !== 'undefined') {
                        window.cartItems[itemKey].quantity = newQuantityVal;
                        $this.dataItems = $.extend({}, window.cartItems);
                    }

                    $quantity.attr('value', newQuantityVal);

                    $this._updatePrices($this, $btnElement, newQuantityVal, data.promoType);
                    $this._updateTotalValue($this);

                    document.dispatchEvent(new CustomEvent('hpcartupdated', {
                        detail: {
                            items: data.items,
                            count: data.count
                        }
                    }));

                    localStorage.setItem('hp_cart_items_count', data.count);
                    localStorage.setItem('hp_cart_items', JSON.stringify(data.items));

                    $this._updateCheckoutBlock(
                        $this,
                        itemKey,
                        priceForOne,
                        newQuantityVal
                    );
                    $this.$('.jsToSecondStep').data('update', true);

                    const quantityDiff = newQuantityVal - oldQuantityVal;
                    const gtmFunction  =  $this.getOption(quantityDiff > 0 ? 'gtmAddCallback' : 'gtmRemoveCallback');
                    if (typeof window[gtmFunction] === 'function') {
                        window[gtmFunction](itemKey, Math.abs(quantityDiff));
                    }
                }
            })
            .fail(function (jqXHR, textStatus) {
                $this._handleAjaxError(textStatus);
            })
            .always(function () {
                $this._hideLoader();
            });
        },

        /**
         * Get char from keyboard.
         *
         * @param event
         * @returns {*}
         */
        _getChar: function (event) {
            if (event.which == null) {
                if (event.keyCode < 32) {
                    return null;
                }

                return String.fromCharCode(event.keyCode);
            }

            if (event.which != 0 && event.charCode != 0) {
                if (event.which < 32) {
                    return null;
                }

                return String.fromCharCode(event.which);
            }

            return null;
        },

        /**
         * Get item key.
         *
         * @param $itemRow
         * @returns {string}
         */
        _getItemKey: function ($itemRow) {
            return $itemRow.data('itemKey');
        },

        /**
        * Set new monthly payment.
        *
        * @param $this
        * @param {number} monthlyPayment
        */
       _setMonthlyPayment: function ($this, monthlyPayment) {
            const newMonthlyPayment = $this._priceFormat(monthlyPayment);

            $('.jsCartMonthlyPayment')
                .find('.simpleType-value')
                .text(newMonthlyPayment);
        },

        /**
         * Update checkout right box item data.
         *
         * @param $this
         * @param id
         * @param type
         * @param unitPrice
         * @param quantity
         */
        _updateCheckoutBlock: function ($this, itemKey, unitPrice, quantity) {
            const $itemRow = $this.$('[class*="hp-cart-check-' + itemKey + '"]');

            if ($itemRow.length > 0) {
                const itemTotalPrice = unitPrice * quantity;

                $itemRow.find('.jsQuantityValue').html(quantity/* + ' шт.' */); /** @todo use language constant */

                $this._updatePriceElement(
                    $this,
                    $itemRow.find('.jsQuantityTotal'),
                    itemTotalPrice
                );

                $this._updatePriceElement(
                    $this,
                    $itemRow.find('.jsCheckoutItemUnitPrice'),
                    unitPrice
                );
            }
        },

        /**
         * Update monthly payment.
         *
         * @param $this
         * @param price
         */
        _updateCreditCalculation: function ($this, price) {
            const $calculateCreditLink = $this.$('.jsCartMonthlyPayment').children().filter('a');
            if ($calculateCreditLink.length) {
                let paramsString = $calculateCreditLink.get(0).search;
                paramsString = paramsString.replace(/price=\d+/, 'price=' + price);

                $calculateCreditLink
                    .attr(
                        'href',
                        $calculateCreditLink.get(0).pathname + paramsString
                    );
            } else {
                const monthlyPayment = $this._calculateMonthlyPayment(price);
                $this._setMonthlyPayment($this, monthlyPayment);
            }
        },

        /**
         * Update cart items prices.
         *
         * @param $itemElement jQuery object
         * @param quantity
         * @param promoType
         */
        _updatePrices: function ($this, $itemElement, quantity, promoType = 0) {
            const $itemRow = $itemElement.closest('.jsCartItemRow'),
                  itemPrice = $itemRow.find('.jsPriceForOne').val(),
                  totalPrice = $this._priceFormat(itemPrice * quantity),
                  $itemTotalPrice = $itemRow.find('.jsItmeTotalPrice .simpleType');

            $itemTotalPrice.attr('data-simpletype-value', itemPrice * quantity);
            $itemTotalPrice.find('.simpleType-value').attr('content', totalPrice).html(totalPrice);

            const $unitPrice = $itemRow.find('.jsItemUnitPrice'),
                  unitPriceFormat = $this._priceFormat(itemPrice);

            $unitPrice.attr('data-simpletype-value', itemPrice);
            $unitPrice.find('.simpleType-value').attr('content', unitPriceFormat).html(unitPriceFormat);

            if (quantity > 1) {
                $unitPrice.removeAttr('hidden');
            } else {
                $unitPrice.attr('hidden', 'hidden');
            }

            let rate        = $this.rate,
                $promoPrice = $itemRow.find('.jsItemPromoPrice'),
                promoPrice  = itemPrice;

            if (rate === 0 || rate === undefined) {
                rate = $itemRow.find('.jsItemRateValue').val()
            }

            if ($this.percentRate === false && promoType === 2) {
                promoPrice = Math.ceil(parseInt(itemPrice) + parseInt(rate));
            } else if ($this.percentRate === true || promoType === 1) {
                promoPrice = Math.ceil(itemPrice / (1 - (rate / 100)));
            }

            let promoPriceFormat = rate === 0 ? unitPriceFormat : $this._priceFormat(promoPrice);

            $promoPrice.attr('data-simpletype-value', promoPrice);
            $promoPrice.find('.simpleType-value').attr('content', promoPriceFormat).html(promoPriceFormat);

            $itemRow.find('[type="hidden"]').filter('[name$="[price]"]').val(promoPrice);

            $itemRow.find('.jsServicePrice').each(function () {
                $servicePrice = $(this);
                originalPrice = $servicePrice.attr('data-original-price');
                if (rate > 0 && promoType === 1) {
                    $servicePrice.find('.simpleType-value').html($this._priceFormat(originalPrice - (originalPrice * (rate / 100))));
                } else {
                    $servicePrice.find('.simpleType-value').html($this._priceFormat(originalPrice));
                }
            });

            if (rate > 0) {
                $promoPrice.removeAttr('hidden');
            } else {
                $promoPrice.attr('hidden', 'hidden');
            }
        },

        /**
         * Update cart total price value.
         *
         * @param $this
         * @private
         */
        _updateTotalValue: function ($this) {
            let totalPrice = 0;
            $this.$('.jsCartItemRow').each(function () {
                const $item = $(this);
                if (!$item.hasClass('hp-item-row-separator')) {
                    const unitPrice = $item.find('.jsPriceForOne').val(),
                          quantity  = $item.find('.jsItemQuantity').val();

                    totalPrice += unitPrice * quantity;
                }
            });

            $this._updatePriceElement(
                $this,
                $this.$('.jsCartFirstStep').find('.jsCartTotalPrice'),
                totalPrice
            );

            $this._updateCreditCalculation($this, totalPrice);

            // Update vat
            const excludedVat = $this._calculateVat($this, totalPrice);
            $this._updatePriceElement(
                $this,
                $this.$('.jsCartTotalVat'),
                excludedVat
            );

            // add delivery cost
            const deliveryPrice = $this.$('.jsCartSecondStep').find('.hp-part-delivery').data('value');
            if (deliveryPrice) {
                totalPrice = totalPrice + deliveryPrice;
            }

            $this._updatePriceElement(
                $this,
                $this.$('.jsCartSecondStep').find('.jsCartTotalPrice'),
                totalPrice
            );

            $this.$('.jsTotalPriceValue').val(totalPrice);
        },

        /**
         * Show or hide credit payment.
         * 
         * @param $this
         */
        _checkCreditAvailability: function ($this) {
            const $paymentCredit = $this.$('#hp-payment-credit');

            if ($this.$('.jsCartItemRow').filter('[data-only-upgrade="1"]').length) {
                $paymentCredit.attr('hidden', 'hidden');
            } else {
                $paymentCredit.removeAttr('hidden');
            }
        },

        /**
         * Check credit limits.
         * 
         * @param $this
         */
        _checkCreditLimits: function ($this) {
            const cartTotal = $this._getCartTotal($this, true),
                  $paymentCredit = $this.$('#hp-payment-credit'),
                  $input = $paymentCredit.find('input[type="radio"]'),
                  moreThanLimits = $this.getOption('creditMaxSum') > 0 && cartTotal > $this.getOption('creditMaxSum'),
                  lessThanLimits = cartTotal < $this.getOption('creditMinSum');

            if (moreThanLimits || lessThanLimits) {
                $input.attr('disabled', 'disabled');
                if ($input.is(':checked')) {
                    const $paymentCard = $this.$('#hp-payment-card');
                    $paymentCard.find('input[type="radio"]').prop('checked', true);
                    let errorMsg = '';
                    if (lessThanLimits) {
                        errorMsg = $this.getOption('creditMinLimitMsg');
                    } else if (moreThanLimits) {
                        errorMsg = $this.getOption('creditMaxLimitMsg');
                    }
                    if (errorMsg !== '') {
                        UIkit.notification(errorMsg, 'danger');
                    }
                }
            } else {
                $input.removeAttr('disabled');
            }
        },

        /**
         * Get cart total.
         *
         * @param $this
         * @param {boolean} includeDelivery
         * 
         * @returns {number}
         */
        _getCartTotal: function ($this, includeDelivery) {
            const itemsSum = parseInt($this.$('.jsTotalPriceValue').val());

            if (includeDelivery) {
                let shippingCost = parseInt($this.$('[name$="[shipping_cost]"]').val());
                shippingCost = shippingCost < 0 ? 0 : shippingCost;
                return itemsSum + shippingCost;
            }

            return itemsSum;
        },

        /**
         * Calculate vat value from price
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
            const valueFormated = $this._priceFormat(value);
            $el.find('.simpleType')
                .attr('data-simpletype-value', value)
                .data('simpletypeValue', value)
                .find('.simpleType-value').attr('content', valueFormated).html(valueFormated);
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
         * Set plus item quantity.
         *
         * @param e
         * @param $this
         */
        'click .jsItemPlus': function (e, $this) {
            $this._changeItemQuantity($(this), 'plus', $this);
            e.preventDefault();
        },

        /**
         * Set minus item quantity.
         *
         * @param e
         * @param $this
         */
        'click .jsItemMinus': function (e, $this) {
            $this._changeItemQuantity($(this), 'minus', $this);
            e.preventDefault();
        },

        /**
         * Submit promocode on press Enter
         *
         * @param e
         * @param $this
         */
        'keypress .jsPromoCodeInput': function (e, $this) {
            if (e.which == 13 && !$('.jsPromoCodeSubmit').hasClass('uk-hidden')) {
                e.preventDefault();
                $this.$('.jsPromoCodeSubmit').trigger('click');
            }
        },

        /**
         * Reset promo code value.
         *
         * @param e
         * @param $this
         */
        'click .jsPromoCodeReset': function (e, $this) {
            const $promoCode = $this.$('#jform_promo_code');

            $this.ajax({
                'dataType' : 'json',
                'data'     : {
                    'task' : 'cart.reset-promo-code',
                    'code' : $promoCode.val()
                },
                'success' : function (data) {
                    if (data.result === true) {
                        document.location.reload();
                    }
                }
            });
        },

        /**
         * Check promo code.
         *
         * @param e
         * @param $this
         */
        'click .jsPromoCodeSubmit': function (e, $this) {
            const $promoCode = $this.$('#jform_promo_code');

            if (!$promoCode.val()) {
                $promoCode.addClass('uk-form-danger');
                return;
            }

            $promoCode.removeClass('uk-form-danger').addClass('tm-form-success');

            $this._openLoader();
            $this.ajax({
                'dataType' : 'json',
                'data'     : {
                    'task' : 'cart.check-promo-code',
                    'code' : $promoCode.val()
                },
                'success' : function (data) {
                    $this._hideLoader();

                    if (data.result === true && data.items.length > 0) {

                        if (data.type === 1 || data.type === 2) {
                            $this.promoType = data.type;

                            data.items.forEach(function (item, i, arr) {
                                let $itemRows;

                                if(item.option_id){
                                    $itemRows = $this.$('.jsCartItemRow[data-type="' + item.type + '"][data-option="' + item.option_id + '"]');
                                }else{
                                    $itemRows = $this.$('.jsCartItemRow[data-type="' + item.type + '"][data-id="' + item.id + '"]');
                                }

                                $itemRows.each(function () {
                                    const $itemRow      = $(this),
                                          $unitPrice    = $itemRow.find('.jsPriceForOne'),
                                          unitPriceVal  = parseInt($unitPrice.val()),
                                          $promoPrice   = $itemRow.find('.jsItemPromoPrice').find('.simpleType-value'),
                                          promoPriceVal = parseInt($promoPrice.attr('content').replace(/\s+/g, ''));

                                    let promoPrice = 0,
                                        rate       = data.rate;

                                    if (unitPriceVal > 0) {
                                        if (data.type === 1) {
                                            promoPrice = Math.ceil(unitPriceVal * (1 - (parseInt(data.rate) / 100)));
                                        } else {
                                            promoPrice = Math.ceil(promoPriceVal - parseInt(data.rate));
                                        }

                                        if (promoPrice < 0) {
                                            promoPrice = 0;
                                        }

                                        $unitPrice.val(promoPrice);
                                        $itemRow.find('.jsItemRateValue').val(rate);

                                        $itemRow.trigger('hpitemupdated', {
                                            'percentRate' : false,
                                        });
                                    }
                                });

                            });

                            $this.$('.jsPromoCodeSubmit').addClass('uk-hidden');
                            $this.$('.jsPromoCodeReset').removeClass('uk-hidden');
                            $this.$('#jform_promo_code').attr('readonly', 'readonly');

                            $this._updateTotalValue($this);
                        }

                        if (data.type === 0) {
                            document.location.reload();
                        }
                    }
                },
                'error' : function (data) {
                    $this._hideLoader();
                    if (data.result === false) {
                        UIkit.modal(
                            $('<div uk-modal>' +
                                '<div class="uk-modal-dialog uk-modal-body">' +
                                    '<button class="uk-modal-close-default" type="button" uk-close></button>' +
                                    '<p>' + data.message + '</p>' +
                                '</div>' +
                            '</div>')
                        ).show();
                    }
                }
            });
        },

        /**
         * Number filter for quantity input.
         *
         * @param e
         * @param $this
         * @returns {boolean}
         */
        'keypress .jsItemQuantity': function (e, $this) {
            if (e.ctrlKey || e.altKey || e.metaKey) {
                return;
            }

            if (e.key === 'Enter') {
                e.preventDefault();
                $(e.target).trigger('blur');
            }

            const chr = $this._getChar(e);

            if (chr == null) {
                return;
            }

            if (chr < '0' || chr > '9') {
                return false;
            }
        },

        /**
         * Go to checkout.
         *
         * @param e
         * @param $this
         */
        'click .jsToSecondStep': function (e, $this) {
            const totalPrice = $this._getCartTotal($this);
            if (totalPrice < $this.getOption('orderMinSum')) {
                UIkit.notification($this.getOption('orderMinLimitMsg'), {status : 'danger'});
                return false;
            }

            $this.$('.jsCartFirstStep').attr('hidden', '');
            $this.$('.jsCartSecondStep').removeAttr('hidden');
            UIkit.margin('.jsSecondStepButtons').$emit(event = 'update');

            location.hash = 'form';
            window.scrollTo(0,0);

            if ($(this).data('update')) {
                $this._checkCreditLimits($this);
            }

            e.preventDefault();
        },

        /**
         * Back to the first step.
         *
         * @param e
         * @param $this
         */
        'click .jsToFirstStep': function (e, $this) {
            $this.$('.jsToSecondStep').data('update', false);
            $this.$('.jsCartSecondStep').attr('hidden', '');
            $this.$('.jsCartUserHeading').attr('hidden', '');
            $this.$('.jsCartFirstStep').removeAttr('hidden');
            UIkit.margin('.jsFirstStepButtons').$emit(event = 'update');

            location.hash = '';

            e.preventDefault();
        },

        /**
         * Leave the cart.
         *
         * @param e
         * @param $this
         */
        'click .jsLeaveCart': function (e, $this) {
            e.preventDefault();
        },

        /**
         * Change item quantity.
         *
         * @param e
         * @param $this
         */
        'change .jsItemQuantity': function (e, $this) {
            $this._changeItemQuantity($(this), 'none', $this);
        },

        /**
         * On remove item from cart.
         *
         * @param e
         * @param $this
         */
        'click .jsRemoveItem': function (e, $this) {
            const $removeButton = $(this),
                  $itemRow      = $removeButton.closest('.jsCartItemRow');

            const sendArgs = {
                'itemKey'      : $itemRow.data('itemKey'),
                'relatedParts' : $itemRow.data('singleParts') || []
            };

            UIkit.tooltip($removeButton).hide();

            UIkit.modal.confirm($this.getOption('removeFromCartConfirm')).then(function () {
                $this._openLoader();
                $.ajax({
                    'url'      : '/index.php',
                    'type'     : 'POST',
                    'dataType' : 'json',
                    'data'     : {
                        'option' : 'com_hyperpc',
                        'format' : 'raw',
                        'tmpl'   : 'component',
                        'task'   : 'cart.removeItem',
                        'args'   : sendArgs
                    }
                })
                .done(function (data) {
                    if (data.result === true) {
                        document.dispatchEvent(new CustomEvent('hpcartupdated', {
                            detail: {
                                items: data.items,
                                count: data.count
                            }
                        }));

                        localStorage.setItem('hp_cart_items_count', data.count);
                        localStorage.setItem('hp_cart_items', JSON.stringify(data.items));

                        if (data.count === 1) {
                            $this.$('.jsCartClearAll').attr('hidden', 'hidden');
                        }

                        const itemKey = $this._getItemKey($itemRow);

                        $itemRow.fadeOut('slow', function () {
                            $(this).remove();

                            $this._updateTotalValue($this);

                            // removes item from checkout block
                            $this.$('.hp-cart-check-' + itemKey).remove();

                            const removeCallback = $removeButton.data('removeCallback');
                            if (typeof window[removeCallback] === 'function') {
                                window[removeCallback](itemKey);
                            }

                            delete window.cartItems[itemKey];
                            $this.dataItems = $.extend({}, window.cartItems);

                            if ($this.$('.jsCartItemRow').length < 1) {
                                $this.$('.jsCartFirstStep:not(.page-title), .jsCartSecondStep').attr('hidden', '');
                                $this.$('.jsCartIsEmpty').removeAttr('hidden');
                                return;
                            }

                            $this._checkCreditAvailability($this);
                            $this.$('.jsToSecondStep').data('update', true);
                        });
                    } else {
                        $this._handleAjaxError('Failed to remove item');
                    }
                })
                .fail(function (jqXHR, textStatus) {
                    $this._handleAjaxError(textStatus);
                })
                .always(function () {
                    $this._hideLoader();
                });
            }, function () {});

            e.preventDefault();
        },

        /**
         * Handle Ajax error
         *
         * @param {string} error
         */
        _handleAjaxError: function (error) {
            const msg = error || 'Connection error';
            UIkit.notification(msg, {status: 'danger', timeout: 0});
        },

        /**
         * Оn submit order.
         *
         * @param e
         * @param $this
         */
        'submit form[action$="cart"]': function (e, $this) {
            $this.$('.jsSubmitOrder')
                .attr('uk-spinner', 'ratio:.5')
                .removeClass('uk-button-primary')
                .addClass('uk-button-secondary uk-disabled');
            $this.$('.jsToFirstStep').addClass('uk-disabled');
        },

        /**
         * On item updated from another widgets.
         *
         * @param e
         * @param $this
         * @param data
         */
        'hpitemupdated .jsCartItemRow': function (e, $this, data = {}) {
            if ('promoType' in data) {
                $this.promoType = data.promoType;
            }

            if ('rate' in data) {
                $this.rate = data.rate;
            }

            if ('percentRate' in data) {
                $this.percentRate = data.percentRate;
            }

            const $itemRow = $(e.target)
                  itemKey = $this._getItemKey($itemRow),
                  quantity = $itemRow.find('.jsItemQuantity').val(),
                  unitPrice = $itemRow.find('.jsPriceForOne').val();

            $this._updatePrices($this, $itemRow, quantity, $this.promoType);
            $this._updateCheckoutBlock($this, itemKey, unitPrice, quantity);
            $this._updateTotalValue($this);
            $this.$('.jsToSecondStep').data('update', true);
        },

        /**
         * On reset item service.
         *
         * @param e
         * @param $this
         */
        'click .jsServiceReset': function (e, $this) {
            $target = $(this);
            const $serviceWrapper = $target.closest('.hp-cart-item-service'),
                  $itemRow = $target.closest('.jsCartItemRow'),
                  requestData = $target.data('reset'),
                  controller = requestData.itemKey.indexOf('position-') === 0 ? 'moysklad_product' : 'product';

            $target.removeAttr('uk-icon').attr('uk-spinner', 'ratio:0.66');

            $.ajax({
                'url'      : '/index.php',
                'method'   : 'POST',
                'dataType' : 'json',
                'data'     : {
                    'option'     : 'com_hyperpc',
                    'format'     : null,
                    'tmpl'       : 'component',
                    'task'       : controller + '.service-save-session',
                    'product-id' : requestData.productId,
                    'price'      : requestData.price,
                    'service-id' : requestData.serviceId,
                    'group-id'   : requestData.groupId,
                    'config-id'  : requestData.configId,
                    'item-key'   : requestData.itemKey,
                }
            })
            .done(function (data) {
                if (data.result) {
                    $serviceWrapper.attr('data-override-params', '{}');
                    $serviceWrapper.find('.hp-cart-item-service__link').removeAttr('hidden');
                    $serviceWrapper.find('.hp-cart-item-service__label').attr('hidden', 'hidden');
                    $serviceWrapper.find('.hp-cart-item-service__edit').attr('hidden', 'hidden');

                    const discount = parseInt($itemRow.find('.jsItemRateValue').val()),
                          unitPrice = discount === 0 ? data.price_quantity : data.price_quantity * (1 - (data.discount / 100));
                    $itemRow.find('.jsPriceForOne').val(unitPrice);
                    $itemRow.trigger('hpitemupdated', {
                        'percentRate' : true,
                        'promoType'   : data.promoType,
                        'rate'        : data.discount
                    });
                } else {
                    UIkit.notification(data.message, 'danger');
                }
            })
            .fail(function (jqXHR, textStatus) {
                $this._handleAjaxError(textStatus);
            })
            .always(function () {
                $target.removeAttr('uk-spinner').removeClass('uk-spinner').attr('uk-icon', 'close');
            });
        },

        /**
         * On click payment type credit wrapper.
         *
         * @param e
         * @param $this
         */
        'click #hp-payment-credit': function (e, $this) {
            const $el = $(this),
                  $radio = $el.find('input');

            if ($radio.is('[disabled]')) {
                const totalPrice = $this._getCartTotal($this, true);
                let errorMsg = '';
                if (totalPrice < $this.getOption('creditMinSum')) {
                    errorMsg = $this.getOption('creditMinLimitMsg');
                } else if ($this.getOption('creditMaxSum') > 0 && totalPrice > $this.getOption('creditMaxSum')) {
                    errorMsg = $this.getOption('creditMaxLimitMsg');
                }

                if (errorMsg !== '') {
                    UIkit.notification(errorMsg, 'danger');
                }
            }
        },

        /**
         * On click clear all button.
         *
         * @param e
         * @param $this
         */
        'click .jsCartClearAll': function (e, $this) {
            UIkit.modal.confirm($this.getOption('clearCartConfirm')).then(function () {
                $this._openLoader();
                $.ajax({
                    'url'      : '/index.php',
                    'type'     : 'POST',
                    'dataType' : 'json',
                    'data'     : {
                        'option' : 'com_hyperpc',
                        'format' : 'raw',
                        'tmpl'   : 'component',
                        'task'   : 'cart.clear-all'
                    }
                })
                .done(function (data) {
                    if (data.result === true) {
                        document.dispatchEvent(new CustomEvent('hpcartupdated', {
                            detail: {
                                items: [],
                                count: 0
                            }
                        }));

                        const moneyConfig = window.Joomla.getOptions('moneyConfig') || {'iso_code': 'RUB'};
                        const dataGA4 = {
                            'event': 'remove_from_cart',
                            'ecommerce': {
                                'currency': moneyConfig.iso_code,
                                'items': $this._getEcommerceItemsGA4($this)
                            }
                        };

                        window.dataLayer = window.dataLayer || [];

                        dataLayer.push({ecommerce: null});
                        dataLayer.push(dataGA4);

                        window.cartItems = {};
                        $this.dataItems = {};

                        localStorage.setItem('hp_cart_items_count', 0);
                        localStorage.setItem('hp_cart_items', '[]');

                        $this.$('.jsCartFirstStep:not(.page-title), .jsCartSecondStep').attr('hidden', '');
                        $this.$('.jsCartIsEmpty').removeAttr('hidden');

                        $this.$('.jsCartItemRow').remove();

                        $this._updateTotalValue($this);
                    } else {
                        $this._handleAjaxError('Failed to remove items');
                    }
                })
                .fail(function (jqXHR, textStatus) {
                    $this._handleAjaxError(textStatus);
                })
                .always(function () {
                    $this._hideLoader();
                });
            }, function () {});
        }

    });
});
