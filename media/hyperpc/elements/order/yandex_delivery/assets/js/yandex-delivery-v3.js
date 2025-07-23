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

    JBZoo.widget('HyperPC.Geo.YandexDelivery.Order', {
        'elementIdentifier' : '',
        'connectionInfo'    : '',
        'courierInfo'       : '',
        'cashOnDelivery'    : false,
        'lang'              : {}
    }, {

        /**
         * @typedef {Object} UserLocation
         * @property {string} country country name
         * @property {string} city city name
         * @property {string} location full name of the user location
         * @property {string} fiasId city/settelment fias identifier
         * @property {string} fiasType type of the locality
         * @property {string|number} geoId yandex geoId
         * @property {string|number} zipCode post index
         *
         * @typedef {Object} ParcelInfo
         * @property {(string|number)} length item length in cm
         * @property {(string|number)} width item width in cm
         * @property {(string|number)} height item height in cm
         * @property {(string|number)} weight item weight in kg
         *
         * @typedef {Object} PickupPoint
         * @property {string} address address of the point
         * @property {?string} coordinates location coordinates of the point
         *
         * @typedef {Object} DaysMinMax
         * @property {number} min min days
         * @property {number} max max days
         *
         * @typedef {Object} DatesRange
         * @property {string} min date in "Jan 01 2000" format
         * @property {string} max date in "Jan 01 2000" format
         *
         * @typedef {Object} DateInfo
         * @property {string} raw date in "Jan 01 2000" or "Jan 01 2000 - Jan 02 2000" format
         * @property {string} value easy to read value
         */

        doday: null,
        currentHour: null,

        lang: {
            free: '',
            days: '',
            shipping: '', 
            methodName: {
                todoor: '',
                pickup: '',
                post: ''
            }
        },

        /**
         * Initialize widget.
         *
         * @param $this
         */
        init: function ($this) {
            $this.constructor.parent.init($this);
            $this.constructor.parent.constructor.parent.init($this);

            if (typeof $this.getOption('lang') === 'object') {
                $.extend(true, $this.lang, $this.getOption('lang'));
            }

            $this._checkPayments($this);
            $this._updateParcelInfo($this);

            const $pickupPointsList = $('.jsPickupPointsList');

            $pickupPointsList
                .on('change', 'input[type="radio"]', function () {
                    $this._setPickupPoint($this, $(this).closest('tr').data('point'));
                })
                .on('click', 'tr', function(e) {
                    if (!$(e.target).is('a')) {
                        $(this).find('input[type="radio"]').prop('checked', true).trigger('change');
                    }
                });

            $('.jsPickupPointsFilter').find('input').on('input', function (e) {
                const $points = $pickupPointsList.children();

                if ($(this).val() === '') {
                    $points.removeAttr('hidden');
                    $('.jsPickupPointsListFooter').attr('hidden', 'hidden');
                } else {
                    const value = $(this).val().toLowerCase();
                    $points.filter(':not([data-address*="' + value + '"]):not(.uk-active)').attr('hidden', 'hidden');
                    $points.filter('[data-address*="' + value + '"]').removeAttr('hidden', 'hidden');

                    if ($points.filter('[data-address*="' + value + '"]').length === $points.length) {
                        $('.jsPickupPointsListFooter').attr('hidden', 'hidden');
                    } else {
                        $('.jsPickupPointsListFooter').removeAttr('hidden');
                    }
                }
            });

            $('.jsPickupPointsFilterClear').on('click', function () {
                $('.jsPickupPointsFilter').find('input').val('').trigger('input');
            });

            $(document).on('availabilityChange', function () {
                $this._checkExpressDelivery($this);
            });
        },

        /**
         * On location defined.
         */
        _onLocationDefined: function () {
            $this = this;

            /** @type {UserLocation} */
            const userLocation = $this._getUserLocation($this);

            $this._updateSendingFromStoreId($this, userLocation);
            $this._updateEstimatedSendingDates($this);

            if ($this._hasMicrosoftRestrictions($this)) {
                $this._setDeliveryUnavailableMsg($this);
            } else if (userLocation[$this.getOption('cityIdentifier')]) {
                $this._updateDelivery($this);
            } else {
                $this._setDeliveryUnavailableMsg($this);
            }

            $this._initAddressField($this, userLocation);
        },

        /**
         * Init address field
         *
         * @param $this
         * @param {UserLocation} userLocation
         */
        _initAddressField: function ($this, userLocation) {
            const $addressField = $this.$('.jsAddressInput'),
                  countryCode = $this._getCountryCode(userLocation),
                  initParams = {
                    token : Joomla.getOptions('dadataToken', ''),
                    type : 'ADDRESS',
                    minChars : 4,
                    geoLocation : false,
                    restrict_value: true
                  };

            $addressField.val('').removeClass('tm-form-success').blur();
            $this.$('[name^="' + $this.getOption('elementIdentifier') + '[granular_address_"]').val('');
            $this.$('[name="' + $this.getOption('elementIdentifier') + '[original_address"]').val('');
            $this.$('[name="' + $this.getOption('elementIdentifier') + '[fias_id"]').val('');

            if (['RU', 'KZ'].includes(countryCode)) {
                const constraintsLocations = {};

                if (countryCode === 'RU') {
                    switch (userLocation.fiasType) {
                        case 'city':
                            constraintsLocations.city_fias_id = userLocation.fiasId;
                            break;
                        case 'settlement':
                            constraintsLocations.settlement_fias_id = userLocation.fiasId;
                            break;
                    }
                } else if (countryCode === 'KZ') {
                    constraintsLocations.country_iso_code = 'KZ';
                    constraintsLocations.region_iso_code = userLocation.geoId;

                    initParams.onSuggestionsFetch = function (suggestions) {
                        return suggestions.filter(function (suggestion) {
                            suggestion.value = suggestion.value.substring(suggestion.value.indexOf(suggestion.data.street_with_type));
                            return [suggestion.data.city, suggestion.data.settlement].includes(userLocation.city);
                        });
                    }
                }

                initParams.constraints = {
                    locations: constraintsLocations
                }

                $addressField.suggestions(initParams);
            } else if ($addressField.suggestions()) {
                $addressField.suggestions().dispose();
            }
        },

        /**
         * Check if all the items in the order are Instock.
         *
         * @param $this
         * @returns {boolean}
         * 
         * @todo improve
         */
        _isOrderInStock: function ($this) {
            return $this.$('.jsCartItemRow').not('[data-availability="InStock"]').length === 0;
        },

        /**
         * Sets parcel dimensions and weight in their inputs.
         *
         * @param $this
         * @param {ParcelInfo} parcelInfo
         */
        _setParcelInfo: function ($this, parcelInfo) {
            if (typeof parcelInfo === 'object') {
                $this.$('[name="' + $this.getOption('elementIdentifier') + '[parcel_dimentions_length]"]').val(parcelInfo.length);
                $this.$('[name="' + $this.getOption('elementIdentifier') + '[parcel_dimentions_width]"]').val(parcelInfo.width);
                $this.$('[name="' + $this.getOption('elementIdentifier') + '[parcel_dimentions_height]"]').val(parcelInfo.height);
                $this.$('[name="' + $this.getOption('elementIdentifier') + '[parcel_weight]"]').val(parcelInfo.weight);
            }
        },

        /**
         * Update parcel info inputs
         *
         * @param $this
         */
        _updateParcelInfo: function ($this) {
            /** @type {ParcelInfo} */
            const parcelInfo = $this._getParcelInfo($this);
            $this._setParcelInfo($this, parcelInfo);
        },

        /**
         * Handle parcel info error
         *
         * @param $this
         */
        _handleParcelInfoError: function ($this) {
            $this._setDeliveryUnavailableMsg($this);
            $this._checkPayments($this);
        },

        /**
         * Handle success update delivery ajax
         *
         * @param $this
         * @param {object} responseData
         * @param {UserLocation} userLocation
         * @param {ParcelInfo} parcelInfo
         * @param {enum} hyperpcCourierState
         */
        _handleSuccessUpdateDelivery: function ($this, response, userLocation, parcelInfo, hyperpcCourierState) {
            const responseData = response.body || false,
                  time = new Date(response.time);

            $this.today = response.time.substr(0, 11);
            $this.currentHour = time.getHours();

            let available = false;

            if (typeof responseData === 'object') {
                const $deliveryOptionsWrapper = $this.$('.jsCartDeliveryOptions');
                $deliveryOptionsWrapper.html('');

                for (let method in responseData) {
                    const methodData = responseData[method],
                          methodDataLength = Object.keys(methodData).length;
                    if (methodDataLength > 0) {
                        available = true;
                        for (let index in methodData) {
                            const methodName = $this._getMethodNameFromAlias(method),
                                  companyName = methodData[index]['companyName'],
                                  cost = methodData[index]['cost'] + $this._getInsuranceCost($this, companyName),
                                  isCourierHyperpc = companyName === 'Курьер HYPERPC';

                            if (isCourierHyperpc) {
                                /** @type {DatesRange} */
                                const estimatedSendingDates = $this._getEstimatedSendingDates($this);
                                if (estimatedSendingDates.min === estimatedSendingDates.max) {
                                    const days = $this._getHypeprcCourierDays(time, new Date(estimatedSendingDates.min));

                                    methodData[index]['minDays'] = days.min;
                                    methodData[index]['maxDays'] = days.max;
                                }
                            }

                            let recomended = false;
                            if (methodDataLength > 1) {
                                if (
                                    companyName.search(/(Деловые линии|DelLin)/i) !== -1 ||
                                    (isCourierHyperpc && hyperpcCourierState === $this.hyperpcCourier.DEFAULT) // Рекомендуем достаку курьером если она выводится вместе с другими доставками
                                ) {
                                    recomended = true;
                                }
                            }

                            let html = $this._getDeliveryMethodHtml($this, companyName, method, methodName, cost, methodData[index]['minDays'], methodData[index]['maxDays'], recomended);
                            // Доставка с подключением если в корзине есть ноутбук или компьютер
                            if (isCourierHyperpc && $this._hasProductOrNotebook($this)) {
                                const connectionCost = $this.getOption('connectionCost');
                                html += ' ' + $this._getDeliveryMethodHtml($this, companyName, method, 'Доставка с подключением', cost + connectionCost, methodData[index]['minDays'], methodData[index]['maxDays'], recomended);
                            }

                            $deliveryOptionsWrapper.append(html);

                            if (method === 'pickup' || method === 'post') {
                                $deliveryOptionsWrapper.children().last()
                                    .data('pickupPointIds', methodData[index]['pickupPointIds'])
                                    .data('defaultPickupPoint', methodData[index]['defaultPickupPoint']);
                            }
                        }
                    }
                }
            }

            if (!available) {
                $this._setDeliveryUnavailableMsg($this);
            } else {
                if ($this.$('[name="' + $this.getOption('elementIdentifier') + '[need_shipping]"]:checked').val() === '1') {
                    $this._setDefaultDeliveryOption($this);
                }
                $this._hideSpinner($this);
            }
        },

        /**
         * Handle fail update delivery
         *
         * @param $this
         */
        _handleFailUpdateDelivery: function ($this) {
            $this._setDeliveryUnavailableMsg($this);
        },

        /**
         * After update delivery
         *
         * @param $this
         */
        _afterUpdateDelivery: function ($this) {
            $this._checkPayments($this);
        },

        /**
         * Get delivery method option html
         *
         * @param $this
         * @param {string} companyName
         * @param {string} method
         * @param {string} methodName
         * @param {number} cost
         * @param {number} minDays
         * @param {number} maxDays
         * @param {boolean} recomended
         */
        _getDeliveryMethodHtml: function ($this, companyName, method, methodName, cost, minDays, maxDays, recomended) {
            let costStr = '<span>' + (cost > 0 ? $this._priceFormat(cost) : $this.lang.free) + '</span>',
                daysStr = $this.lang.days.replace('%s', minDays),
                hiddenAttr = '';

            if (companyName === 'Курьер HYPERPC') {
                daysStr = '';
                if (methodName === 'Доставка с подключением' && $this.getOption('connectionInfo') !== '') {
                    costStr += ' <a href="' + $this.getOption('connectionInfo') + '" class="jsLoadIframe uk-link-muted uk-margin-small-left uk-flex-none" uk-icon="question"></a>';
                } else if (methodName === 'Доставка до двери' && $this.getOption('courierInfo') !== '') {
                    costStr += ' <a href="' + $this.getOption('courierInfo') + '" class="jsLoadIframe uk-link-muted uk-margin-small-left uk-flex-none" uk-icon="question"></a>';
                }
            } else if ($this._tariffNameIsYandexGo(companyName)) {
                daysStr = '';
                if (cost > 0) {
                    costStr = '<span>от ' + $this._priceFormat(cost) + '</span>';
                    cost = -1;
                }
                hiddenAttr = $this._canSendExpress($this) ? '' : ' hidden';
            } else if (minDays !== maxDays) {
                daysStr = $this.lang.days.replace('%s', (minDays + ' - ' + maxDays));
            }

            return '<tr class="jsCartDeliveryOption' + (recomended ? ' jsRecomendedMethod' : '') + '" data-method="' + method + '" data-cost="' + cost + '" data-days=\'{"min":"' + minDays + '", "max":"' + maxDays + '"}\'' + hiddenAttr + '>' +
                        '<td class="uk-table-shrink uk-padding-remove-right">' +
                            '<input type="radio" name="' + $this.getOption('elementIdentifier') + '[delivery_service]" data-cost="' + cost + '" value="' + companyName + ' ' + methodName + '" class="uk-radio" />' +
                        '</td>' +
                        '<td>' +
                            companyName + (recomended ? ' <span class="uk-text-small uk-text-success">(рекомендуем)</span> ' : '') +
                            '<div class="uk-text-small uk-text-muted">' + methodName + ' ' + daysStr + '</div>' +
                        '</td>' +
                        '<td class="uk-text-nowrap">' +
                            '<span class="uk-flex uk-flex-between uk-flex-nowrap">' +
                                costStr +
                            '</span>' +
                        '</td>' +
                    '</tr>';
        },

        /**
         * Can send item by express delivery (in 2 hours)
         *
         * @param $this
         *
         * @returns {boolean}
         */
         _canSendExpress: function ($this) {
            return true; // always true now

            /** @type {UserLocation} */
            const userLocation = $this._getUserLocation($this);
            let storeId = 0,
                workingHours = false;

            // TODO rewrite it
            if ($this._locationIsSpb(userLocation)) {
                storeId = 2; 
                workingHours = $this.currentHour >= 11 && $this.currentHour < 18; // TODO get hours from store params
            } else { // Moscow and Oblast
                storeId = 1;
                workingHours = $this.currentHour >= 9 && $this.currentHour < 20; // TODO get hours from store params
            }

            return workingHours && $this.$('.jsStoresList').find('[data-storeid="' + storeId + '"]').data('available'); // TODO get from $this.orderPickingDates
        },

        /**
         * Set defaul delivery option
         *
         * @param $this
         */
        _setDefaultDeliveryOption: function ($this) {
            const $options = $this.$('.jsCartDeliveryOption'),
                  $recomended = $options.filter('.jsRecomendedMethod');

            if ($recomended.length > 0) {
                $recomended.eq(0).find('input[type="radio"]').prop('checked', true).trigger('change');
                return;
            }

            const $optionsTodoor = $options.filter('[data-method="todoor"]'),
                  $optionsPickup = $options.filter('[data-method="pickup"]');

            let minCost = -1;

            if ($optionsTodoor.length > 0) {
                $optionsTodoor.each(function () {
                    const $option = $(this);
                    if (!$option.is('[hidden]')) {
                        minCost = minCost === -1 ? $option.data('cost') : Math.min($option.data('cost'), minCost);
                    }
                });

                $optionsTodoor.filter('[data-cost="' + minCost + '"]').eq(0)
                    .find('input[type="radio"]').prop('checked', true).trigger('change');
            } else if ($optionsPickup.length > 0) {
                $optionsPickup.each(function () {
                    minCost = minCost === -1 ? $(this).data('cost') : Math.min($(this).data('cost'), minCost);
                });

                $optionsPickup.filter('[data-cost="' + minCost + '"]').eq(0)
                    .find('input[type="radio"]').prop('checked', true).trigger('change');
            } else {
                $options.eq(0).find('input[type="radio"]').prop('checked', true).trigger('change');
            }
        },

        /**
         * Get delivery method title from its alias
         *
         * @param {string} methodAlias
         * @returns {string}
         */
        _getMethodNameFromAlias: function (methodAlias) {
            return $this.lang.methodName[methodAlias] || '';
        },

        /**
         * Show/hide express delivery option by order availability
         *
         * @param $this
         */
         _checkExpressDelivery: function ($this) {
            $this.$('.jsCartDeliveryOption').each(function () {
                const $option = $(this);
                if ($this._tariffNameIsYandexGo($option.text())) {
                    if ($this._canSendExpress($this)) {
                        $option.removeAttr('hidden');
                    } else {
                        $option.attr('hidden', 'hidden');
                        $this._setDefaultDeliveryOption($this);
                    }

                    return false;
                }
            });
        },

        /**
         * Get overrides for services
         *
         * @param $cartItem
         * @returns {object}
         */
        _getServiceOverrides: function ($cartItem) {
            const $services = $cartItem.find('.hp-cart-item-service'),
                  overridedParams = {};

            $services.each(function () {
                const serviceParams = JSON.parse($(this).attr('data-override-params'));
                if (typeof serviceParams === 'object') {
                    for (let param in serviceParams) {
                        overridedParams[param] = serviceParams[param];
                    }
                }
            });

            return overridedParams;
        },

        /**
         * Get parcel dimensions and weight
         *
         * @param $this
         * @returns {ParcelInfo}
         */
        _getParcelInfo: function ($this) {
            const $cartItems = $this.$('.jsCartItemRow'),
                  /** @type {ParcelInfo[]} */
                  itemsMeasurments = [];

            $cartItems.each(function () {
                const $cartItem = $(this);
                if (typeof $cartItem.data('weight') !== 'undefined' && typeof $cartItem.data('dimensions') !== 'undefined') {
                    const overrides = $this._getServiceOverrides($cartItem),
                          itemDimensions = overrides.dimensions || $cartItem.data('dimensions'),
                          itemWeight     = overrides.weight || $cartItem.data('weight'),
                          itemQuantity   = $cartItem.find('.jsItemQuantity').val();

                    /** @type {ParcelInfo} */
                    const itemMeasurments = {
                        'length' : itemDimensions.length,
                        'width'  : itemDimensions.width,
                        'height' : itemDimensions.height,
                        'weight' : (itemWeight * itemQuantity)
                    };

                    const minDimension = $this._getMinDimension(itemMeasurments);
                    itemMeasurments[minDimension] *= itemQuantity;

                    itemsMeasurments.push(itemMeasurments);

                    if (typeof $cartItem.data('additionalWeight') !== 'undefined' && typeof $cartItem.data('additionalDimensions') !== 'undefined') {
                        const additionalDimensions = $cartItem.data('additionalDimensions'),
                              additionalWeight     = $cartItem.data('additionalWeight');

                        /** @type {ParcelInfo} */
                        const additionalMeasurments = {
                            'length' : additionalDimensions.length,
                            'width'  : additionalDimensions.width,
                            'height' : additionalDimensions.height,
                            'weight' : (additionalWeight * itemQuantity)
                        };

                        const minAdditionalDimension = $this._getMinDimension(additionalMeasurments);
                        additionalMeasurments[minAdditionalDimension] *= itemQuantity;

                        itemsMeasurments.push(additionalMeasurments);
                    }
                }
            });

            if (itemsMeasurments.length === 1) {
                return itemsMeasurments.shift();
            } else if (itemsMeasurments.length > 1) {
                let orderVolume = 0,
                    orderWeight = 0.0;

                itemsMeasurments.forEach(function (itemMeasurments) {
                    orderVolume += (itemMeasurments.length * itemMeasurments.width * itemMeasurments.height) / 1000000;
                    orderWeight += itemMeasurments.weight;
                });

                Math.cbrt = Math.cbrt || $this._cbrt;

                const orderOneDimension = Math.ceil(Math.cbrt(orderVolume * 1.15) * 100);

                /** @type {ParcelInfo} */
                const parcelInfo = {
                    'length' : orderOneDimension,
                    'width'  : orderOneDimension,
                    'height' : orderOneDimension,
                    'weight' : orderWeight
                };

                return parcelInfo;
            }

            return false;
        },

        /**
         * Get min dimension name
         *
         * @param {ParcelInfo} dimensions
         * @returns {string}
         */
        _getMinDimension: function (dimensions) {
            const minWidthLength = dimensions.width < dimensions.length ? 'width' : 'length';
            return dimensions[minWidthLength] < dimensions.height ? minWidthLength : 'height';
        },

        /**
         * Math.cbrt polyfill
         *
         * @param {number} x
         * @returns {number}
         */
        _cbrt: function (x) {
            if (x === 0 || x === +1 / 0 || x === -1 / 0 || x !== x) {
                return x;
              }

              const a = Math.abs(x),
                    y = Math.exp(Math.log(a) / 3);

              return (x / a) * (y + (a / (y * y) - y) / 3);
        },

        /**
         * Set delivery unavailable message.
         *
         * @param $this
         */
        _setDeliveryUnavailableMsg: function ($this) {
            $this._hideSpinner($this);
            const $message = $this.$('.jsDeliveryUnavailableMsg');
            $message.removeAttr('hidden');

            if ($this._hasMicrosoftRestrictions($this)) {
                const $crimeaAlert = $message.find('.jsCrimeaAlert');
                if (!$crimeaAlert.length) {
                    $message
                        .append(
                            '<div class="jsCrimeaAlert uk-alert uk-alert-warning">' +
                                'Внимание! К сожалению, мы не осуществляем доставку и продажу ПО Microsoft в Республику Крым и г. Севастополь.' +
                            '</div>')
                        .find('.jsCrimeaAlert').siblings().attr('hidden', 'hidden');
                }
                $this._hideAddressInput($this);
                $this._hideSendingDates($this);
                $this._blockOrderButton($this);
            } else {
                $message
                    .children().removeAttr('hidden')
                    .filter('.jsCrimeaAlert').remove();
                $this._showAddressInput($this);
                $this._showSendingDates($this);
                $this._unblockOrderButton($this);
            }

            $this.$('.jsTimeline').attr('hidden', 'hidden');
            $this.$('.jsCartDeliveryOptions').html('');
            $this.$('.jsCheckoutDelivery').remove();
            $this.$('[name^="' + $this.getOption('elementIdentifier') + '[days_"]').val('');
            $this._setDeliveryCost($this, -1);
            $this._hidePickupPoints($this);
        },

        /**
         * Hide delivery unavailable message.
         *
         * @param $this
         */
        _hideDeliveryUnavailableMsg: function ($this) {
            $this.$('.jsDeliveryUnavailableMsg').attr('hidden', 'hidden')
                .children().removeAttr('hidden')
                .filter('.jsCrimeaAlert').remove();

            $this._unblockOrderButton($this);
        },

        /**
         * Location is Crimea.
         *
         * @param $this
         */
        _locationIsCrimea: function ($this) {
            /** @type {UserLocation} */
            const userLocation = $this._getUserLocation($this);
            if (String(userLocation.city).indexOf('Севастополь') !== -1 ||
                String(userLocation.location).indexOf('Республика Крым') !== -1 ||
                String(userLocation.location).indexOf('Респ Крым') !== -1) {
                return true;
            }

            return false;
        },

        /**
         * Render delivery in checkout block.
         *
         * @param $this
         * @param name
         * @param price
         * @param rate
         */
        _renderDeliveryCheckout: function ($this, name, price) {
            let priceHtml = '';
            if (price === 0) {
                priceHtml = $this.lang.free;
            } else if (price > 0) {
                priceHtml = $this._priceFormat(price);
            }

            const output =
                '<li class="hp-cart-checkout-item jsCheckoutDelivery">' +
                    '<div class="uk-text-muted">' +
                        $this.lang.shipping +
                    '</div>' +
                    '<div class="uk-text-emphasis uk-heading-bullet">' +
                        name +
                    '</div>' +
                    '<div>' +
                        priceHtml +
                    '</div>' +
                '</li>';

            if ($this.$('.jsCheckoutDelivery').length) {
                $this.$('.jsCheckoutDelivery').remove();
            }

            $this.$('.hp-cart-checkout-items').append(output);

            $this._setDeliveryCost($this, price);
        },

        /**
         * Correct order price by delivery cost.
         *
         * @param $this
         * @param shippingPrice
         */
        _setDeliveryCost: function ($this, shippingPrice) {
            const $priceEl = $this.$('.jsCartSummary').find('.jsCartTotalPrice'),
                  orderPrice = $this.$('.jsCartFirstStep').find('.jsCartTotalPrice').find('.simpleType').data('simpletypeValue');
            let newPrice = orderPrice + shippingPrice;

            if (parseInt(shippingPrice) < 0) {
                $this.$('[name="' + $this.getOption('elementIdentifier') + '[shipping_cost]"]').val('-1');
                newPrice = orderPrice;
            } else {
                $this.$('[name="' + $this.getOption('elementIdentifier') + '[shipping_cost]"]').val(shippingPrice);
            }

            const priceFormatted = $this._priceFormat(newPrice);

            $priceEl.find('.simpleType').html(priceFormatted);
        },

        /**
         * Show pickup point block.
         *
         * @param $this
         * @param {Array} pickupPointIds
         * @param {PickupPoint} defaultPickupPoint
         */
        _showActivePickupPoint: function ($this, pickupPointIds, defaultPickupPoint) {
            if (pickupPointIds.length > 0) {
                const linkQuery = defaultPickupPoint.coordinates || defaultPickupPoint.address;
                $this.$('.jsPickupPoint').html(defaultPickupPoint.address);
                $this.$('.jsPickupPointMapLink').attr('href', 'https://maps.yandex.ru/?text=' + linkQuery);

                $this.$('.jsPickupPointWrapper').removeAttr('hidden');

                $this.$('[name="' + $this.getOption('elementIdentifier') + '[pickup_point_address]"]').val(defaultPickupPoint.address);

                if (pickupPointIds.length > 1) {
                    $this.$('.jsChoosePickupPoint').removeAttr('hidden');
                    $this.$('.jsPickupPointsCount').html('(' + pickupPointIds.length + ')');
                    $('.jsPickupPointsList').html('<tr class="uk-text-center"><td><span uk-spinner></span></td></tr>');
                    $('.jsPickupPointsListFooter').attr('hidden', 'hidden');
                    $('.jsPickupPointsFilter').find('input').val('');
                } else {
                    $this.$('.jsChoosePickupPoint').attr('hidden', 'hidden');
                }
            } else {
                $this._hidePickupPoints($this);
            }
        },

        /**
         * Get active delivery option
         *
         * @param $this
         */
        _getActiveDeliveryOption: function ($this) {
            return $this.$('.jsCartDeliveryOptions').find('input:checked').closest('.jsCartDeliveryOption');
        },

        /**
         * Handle success get pickup points info
         *
         * @param $this
         * @param {object} response
         */
        _handleSuccessGetPickupPointsInfo: function ($this, response) {
            const pickupPointsInfo = response.body;

            const $activeDeliveryOption = $this._getActiveDeliveryOption($this);
            $activeDeliveryOption.data('pickupPointsInfo', pickupPointsInfo);

            $this._buildPickupPointsList($this, pickupPointsInfo);
        },

        /**
         * Handle fail get pickup points info
         *
         * @param $this
         */
        _handleFailGetPickupPointsInfo: function ($this) {
            $('.jsPickupPointsList').html('<div class="uk-alert uk-alert-danger">Возникла ошибка при получении пунктов самовывоза.</div>');
        },

        /**
         * Build pickup points list
         *
         * @param $this
         * @param {PickupPoint[]} pickupPointsInfo
         */
        _buildPickupPointsList: function ($this, pickupPointsInfo) {
            const activePickupAddress = $this.$('[name="' + $this.getOption('elementIdentifier') + '[pickup_point_address]"]').val(),
                  points = [];

            for (let id in pickupPointsInfo) {
                if (Object.hasOwnProperty.call(pickupPointsInfo, id)) {
                    const point = pickupPointsInfo[id],
                          address = point.address,
                          linkQuery = point.coordinates || address,
                          checked = activePickupAddress === address,
                          pointHtml = '<tr data-address=\'' + (address).toLowerCase() + '\'>' +
                                        '<td class="uk-table-shrink">' +
                                            '<input type="radio" class="uk-radio" name="pickup-points" value=\'' + address + '\'' + (checked ? ' checked' : '') + '>' +
                                        '</td>' +
                                        '<td>' +
                                            '<div class="uk-flex">' +
                                                '<span class="uk-flex-none uk-visible@s uk-margin-small-right uk-icon" style="padding-top: 5px;" uk-icon="location"></span>' +
                                                '<div class="uk-width-expand">' +
                                                    '<div>Адрес пункта выдачи:</div>' +
                                                    '<div class="tm-text-medium uk-text-emphasis">' + address + '</div>' +
                                                    '<div><a href=\'https://maps.yandex.ru/?text=' + linkQuery + '\' target="_blank" rel="noopener">Показать на карте</a></div>' +
                                                '</div>' +
                                            '</div>' +
                                        '</td>' +
                                    '</tr';

                    const $pickupPoint = $(pointHtml);
                    $pickupPoint.data('point', point);
                    points.push($pickupPoint);
                }
            }

            $('.jsPickupPointsList').html(points);
        },

        /**
         * On change delivery option.
         *
         * @param $this
         * @param {PickupPoint} point
         */
        _setPickupPoint: function ($this, point) {
            $this.$('.jsPickupPoint').html(point.address);
            $this.$('.jsPickupPointMapLink').attr('href', 'https://maps.yandex.ru/?text=' + (point.coordinates || point.address));

            $this.$('[name="' + $this.getOption('elementIdentifier') + '[pickup_point_address]"]').val(point.address);
        },

        /**
         * Hide pickup point block.
         *
         * @param $this
         */
        _hidePickupPoints: function ($this) {
            $this.$('.jsPickupPointWrapper').attr('hidden', 'hidden');
            $this.$('[name="' + $this.getOption('elementIdentifier') + '[pickup_point_address]"]').val('');
        },

        /**
         * Show address input.
         *
         * @param $this
         */
        _showAddressInput: function ($this) {
            $this.$('.jsAddressInput').removeAttr('disabled')
                .closest('.tm-label-infield').removeAttr('hidden');
        },

        /**
         * Hide address input.
         *
         * @param $this
         * @private
         */
        _hideAddressInput: function ($this) {
            $this.$('.jsAddressInput').attr('disabled', 'disabled')
                .closest('.tm-label-infield').attr('hidden', 'hidden')
        },

        /**
         * Show spinner.
         *
         * @param $this
         */
        _showSpinner: function ($this) {
            $this.$('.jsDeliverySpinner').removeAttr('hidden');
            $this._hideDeliveryUnavailableMsg($this);
            $this.$('.jsCartDeliveryOptions').html('');
            $this.$('.jsCheckoutDelivery').remove();
        },

        /**
         * Hide spinner.
         *
         * @param $this
         */
        _hideSpinner: function ($this) {
            $this.$('.jsDeliverySpinner').attr('hidden', 'hidden')
        },

        /**
         * Set estimated dates.
         *
         * @param $this
         * @param {function=} successCallback
         */
        _setDates: function ($this, successCallback) {
            $this._clearDates($this);

            $.ajax({
                'url'       : '/index.php?tmpl=component',
                'dataType'  : 'json',
                'type'      : 'POST',
                'data'      : {
                    'option'     : 'com_hyperpc',
                    'task'       : 'cart.get-dates',
                    'format'     : 'raw'
                }
            })
            .done(function (response) {
                const $stores = $this.$('.jsStoresList');

                $this.orderPickingDates = response.stores || {};
                $this._updateSendingFromStoreId($this);
                if (typeof successCallback === 'function') {
                    successCallback();
                }

                for (let storeId in $this.orderPickingDates) {
                    const storeData =  $this.orderPickingDates[storeId],
                          $store = $stores.find('[data-storeid="' + storeId + '"]'),
                          $available = typeof storeData.availableNow !== 'undefined' ? storeData.availableNow : false,
                          storePickup = storeData.pickup || {};

                    $store
                        .removeClass('uk-disabled')
                        .data('available', $available)
                        .attr('data-available', $available)
                        .find('.jsConditionsDeliveryPickingDate')
                        .data('raw', storePickup.raw || '')
                        .text(storePickup.value || '');

                    if ($store.find('input[type="radio"]').is(':checked')) {
                        $this.$('[name="' + $this.getOption('elementIdentifier') + '[store_pickup_dates]"]').val(storePickup.raw || '');
                    }

                    if (storeId.toString() === "1") { // Moscow store  // TODO check this
                        $(document).trigger('availabilityChange', $available);
                    }
                }

                $this._updateEstimatedSendingDates($this);
            })
            .fail(function (error) {
                // error
            });
        },

        /**
         * Update estimated sending dates
         *
         * @param $this
         */
        _updateEstimatedSendingDates: function ($this) {
            /** @type {DateInfo} */
            const shippingDate = $this._getSendingDatesInfo($this);
            $this.$('.jsEstimatedSendingDates').text(shippingDate.value);
            if (!$this._hasMicrosoftRestrictions($this)) {
                $this._showSendingDates($this);
            }

            /** @type {DatesRange} dates */
            const dates = $this._splitDates(shippingDate.raw);

            $this.$('[name="' + $this.getOption('elementIdentifier') + '[sending_date_min]"]').val(dates.min);
            $this.$('[name="' + $this.getOption('elementIdentifier') + '[sending_date_max]"]').val(dates.max);

            $this._setTimelineDate($this, 'sending', shippingDate.raw);
        },

        /**
         * Get estimated sending dates
         *
         * @param $this
         * 
         * @returns {DatesRange} {
         *     min: string,
         *     max: string
         * }
         */
        _getEstimatedSendingDates: function ($this) {
            return {
                min: $this.$('[name="' + $this.getOption('elementIdentifier') + '[sending_date_min]"]').val(),
                max: $this.$('[name="' + $this.getOption('elementIdentifier') + '[sending_date_max]"]').val()
            }
        },

        /**
         * Get current delivery days
         *
         * @param $this
         * 
         * @returns {DaysMinMax} {
         *     min: number,
         *     max: number
         * }
         */
         _getCurrentDeliveryDays: function ($this) {
            return {
                min: $this.$('[name="' + $this.getOption('elementIdentifier') + '[days_min]"]').val(),
                max: $this.$('[name="' + $this.getOption('elementIdentifier') + '[days_max]"]').val()
            }
        },

        /**
         * Clear dates before update
         *
         * @param $this
         */
        _clearDates: function ($this) {
            $this.$('[name^="' + $this.getOption('elementIdentifier') + '[sending_date_"]').val('');
            $this.$('[name="' + $this.getOption('elementIdentifier') + '[store_pickup_dates]"]').val('');

            const spinnerHtml = '<span uk-spinner="ratio: 0.6"></span>';
            $this.$('.jsEstimatedSendingDates').html(spinnerHtml);
            $this.$('.jsConditionsDeliveryPickingDate').html(spinnerHtml);
        },

        /**
         * Show estimated sending dates.
         *
         * @param $this
         */
        _showSendingDates: function ($this) {
            $this.$('.jsEstimatedSending, .jsTimeline').removeAttr('hidden');
        },

        /**
         * Hide estimated sending dates.
         *
         * @param $this
         */
        _hideSendingDates: function ($this) {
            $this.$('.jsEstimatedSending, .jsTimeline').attr('hidden', 'hidden');
        },

        /**
         * Set delivery date in timeline
         *
         * @param $this
         */
         _setDeliveryDateInTimeline: function ($this) {
            /** @type {DatesRange} */
            const estimatedSendingDates = $this._getEstimatedSendingDates($this);

            if (estimatedSendingDates.min !== '') {
                /** @type {DaysMinMax} */
                const currentDeliveryDays = $this._getCurrentDeliveryDays($this);

                const minDate = new Date((new Date(estimatedSendingDates.min)).getTime() + currentDeliveryDays.min * 24 * 60 * 60 * 1000),
                      maxDate = new Date((new Date(estimatedSendingDates.max)).getTime() + currentDeliveryDays.max * 24 * 60 * 60 * 1000);

                $this._setTimelineDate($this, 'ready', minDate + ' - ' + maxDate);
            }
        },

        /**
         * Set timeline date
         *
         * @param $this
         * @param {string} step
         * @param {string} rawDate
         */
        _setTimelineDate: function ($this, step, rawDate) {
            const $timeline = $this.$('.jsTimeline');

            switch (step) {
                case 'place':
                    break;
                case 'sending':
                    const $step = $timeline.find('.jsTimelineSending'),
                          dateStr = $this._dateToString($this, rawDate);

                    $step.removeAttr('hidden')
                         .find('.jsTimelineDate').text(dateStr);

                    const $placeStep = $timeline.find('.jsTimelinePlace');
                    if ($placeStep.find('.jsTimelineDate').text().trim() === dateStr) {
                        $placeStep.attr('hidden', 'hidden');
                    } else {
                        $placeStep.removeAttr('hidden');
                    }

                    break;
                case 'ready':
                    const deliveryDateStr = $this._dateToString($this, rawDate),
                          $deliverStep = $timeline.find('.jsTimelineReady'),
                          $sendingStep = $timeline.find('.jsTimelineSending');

                    $deliverStep.find('.jsTimelineDate').text(deliveryDateStr);
                    if ($sendingStep.find('.jsTimelineDate').text().trim() === deliveryDateStr) {
                        $sendingStep.attr('hidden', 'hidden');
                    } else {
                        $sendingStep.removeAttr('hidden');
                    }

                    break;
            }

            if ($timeline.children().not('[hidden]').length < 2) {
                $timeline.attr('hidden', 'hidden');
            }
        },

        /**
         * Check payment methods.
         *
         * @param $this
         *
         */
        _checkPayments: function ($this) {
            const $paymentCash   = $this.$('#hp-payment-spot'),
                  $paymentCard   = $this.$('#hp-payment-card'),
                  $paymentCredit = $this.$('#hp-payment-credit');

            if ($this.$('[name="' + $this.getOption('elementIdentifier') + '[need_shipping]"]:checked').val() === '0') {
                $paymentCash.removeAttr('hidden');
                $paymentCard.removeAttr('hidden');
                $paymentCredit.removeAttr('hidden');
            } else {
                $this._setPaymentMethodAvailability(
                    $this._isPaymentCashAvailable($this),
                    $paymentCash,
                    $paymentCard
                );

                $this._setPaymentMethodAvailability(
                    $this._isPaymentCreditAvailable($this),
                    $paymentCredit,
                    $paymentCard
                );

                $this._setPaymentMethodAvailability(
                    $this._isPaymentCardAvailable($this),
                    $paymentCard,
                    $paymentCash
                );
            }
        },

        /**
         * Show/hide payment method
         *
         * @param {bool} available
         * @param $method
         * @param $reserveMethod
         */
        _setPaymentMethodAvailability: function(available, $method, $reserveMethod) {
            if (available) {
                $method.removeAttr('hidden');
            } else {
                $method.attr('hidden', 'hidden');
                if ($method.find('input[type="radio"]').is(':checked')) {
                    $reserveMethod.find('input[type="radio"]').prop('checked', true);
                }
            }
        },

        /**
         * Is cash payment available.
         *
         * @param $this
         *
         * @returns {boolean}
         */
        _isPaymentCashAvailable: function ($this) {
            if ($this._activeDeliveryOptionIsYandexGo($this)) {
                return false;
            }

            if ($this._hasHyperpcCourier($this) || $this.getOption('cashOnDelivery')) {
                return true
            }

            return false;
        },

        /**
         * Is card payment available.
         *
         * @param $this
         *
         * @returns {boolean}
         */
        _isPaymentCardAvailable: function ($this) {
            // if ($this._hasHyperpcCourier($this)) {
            //     return false
            // }

            return true;
        },

        /**
         * Is credit available.
         *
         * @param $this
         *
         * @returns {boolean}
         */
         _isPaymentCreditAvailable: function ($this) {
            if ($this._activeDeliveryOptionIsYandexGo($this)) {
                return false;
            }

            return true;
        },

        /**
         * Active delivery option is Yandex.Go
         *
         * @param $this
         *
         * @returns {boolean}
         */
        _activeDeliveryOptionIsYandexGo: function ($this) {
            const $activeDeliveryOption = $this._getActiveDeliveryOption($this);
            if ($activeDeliveryOption.length && $this._tariffNameIsYandexGo($activeDeliveryOption.text())) {
                return true;
            }

            return false;
        },

        /**
         * Is there hyperpc courier in the delivery options
         *
         * @param $this
         *
         * @returns {boolean}
         */
        _hasHyperpcCourier: function ($this) {
            const $deliveryOptions = $this.$('[name="' + $this.getOption('elementIdentifier') + '[delivery_service]"]');

            return $deliveryOptions.filter('[value~="HYPERPC"]').length > 0;
        },

        /**
         * Get accessed value
         *
         * @param $this
         *
         * @returns {number}
         */
        _getAssessedValue: function ($this) {
            return $this.$('.jsTotalPriceValue').val();
        },

        /**
         * Get courier state
         *
         * @param $this
         * @returns {string}
         */
        _getCourierState: function ($this) {
            if ($this._hasProductOrNotebook($this)) {
                // Если компьютер или ноутбук и доступна доставка курьером HYPERPC, то другие варианты не нужны
                return $this.hyperpcCourier.ONLY;
            }

            return $this.hyperpcCourier.DEFAULT;
        },

        /**
         * Is there a product or notebook in the cart?
         *
         * @param $this
         */
        _hasProductOrNotebook: function ($this) {
            const $cartItemRows = $this.$('.jsCartItemRow'),
                  hasProduct = $cartItemRows.filter('[data-product-type="product"], [data-product-type="configuration"]').length > 0,
                  hasNotebook = $cartItemRows.filter('[data-product-type="notebook"]').length > 0;

            return hasProduct || hasNotebook;
        },

        /**
         * Is there a microsoft products in the cart?
         *
         * @param $this
         */
        _hasMicrosoftProducts: function ($this) {
            if ($this._hasProductOrNotebook($this)) {
                // TODO check configurations
                return true;
            }

            const $cartItemRows = $this.$('.jsCartItemRow');
            let hasMicrosoftPart = false;
            $cartItemRows.each(function() {
                const $item = $(this);
                if ($item.is('[data-type="part"]')) {
                    if (/microsoft|xbox/i.test($item.text())) {
                        hasMicrosoftPart = true;
                        return false;
                    }
                }
            });

            return hasMicrosoftPart;
        },

        /**
         * Has Microsoft restrictions. Always returns false now
         *
         * @param $this
         * 
         * @returns  boolean
         */
        _hasMicrosoftRestrictions: function ($this) {
            return false; //$this._locationIsCrimea($this) && $this._hasMicrosoftProducts($this);
        },

        /**
         * Block submit order button
         *
         * @param $this 
         */
        _blockOrderButton: function ($this) {
            $this.$('.jsSubmitOrder').attr('disabled', 'disabled');
        },

        /**
         * Unblock submit order button
         *
         * @param $this 
         */
        _unblockOrderButton: function ($this) {
            $this.$('.jsSubmitOrder').removeAttr('disabled');
        },

        /**
         * Set default store option
         *
         * @param $this
         */
        _setDefaultStoreOption: function ($this) {
            const $stores = $this.$('.jsStoresListOption');
            $stores.first().find('input[type="radio"]').prop('checked', true).trigger('change');
        },

        /**
         * On change need shipping param.
         *
         * @param e
         * @param $this
         *
         */
        'beforeshow #field-yandex_delivery .uk-switcher': function (e, $this) {
            const $tab = $(e.target);
            if (!$tab.is('.uk-drop')) {
                const $radio = $this.$('[name="' + $this.getOption('elementIdentifier') + '[need_shipping]"]').eq($tab.index());
                $radio.prop('checked', true);

                switch ($radio.val()) {
                    case '0': // from shop
                        $this.$('.jsCartDeliveryOption').find('input[type="radio"]').prop('checked', false);
                        $this.$('.jsCheckoutDelivery').remove();
                        $this.$('.jsAddressInput').attr('disabled', 'disabled');

                        $this.$('[name="' + $this.getOption('elementIdentifier') + '[store_pickup_dates]"]').removeAttr('disabled');
                        $this._setDefaultStoreOption($this);

                        $this._setDeliveryCost($this, -1);
                        $this.$('[name="' + $this.getOption('elementIdentifier') + '[shipping_cost]"]').attr('disabled', 'disabled');
                        $this.$('[name^="' + $this.getOption('elementIdentifier') + '[days_"]').attr('disabled', 'disabled').val('');
                        $this.$('[name^="' + $this.getOption('elementIdentifier') + '[sending_date_"]').attr('disabled', 'disabled');
                        $this.$('[name^="' + $this.getOption('elementIdentifier') + '[parcel_"]').attr('disabled', 'disabled');

                        $this._hidePickupPoints($this);
                        $this._unblockOrderButton($this);
                        break;
                    case '1': // shipping
                        $this.$('.jsStoresListOption').find('input[type="radio"]').prop('checked', false);
                        $this.$('[name="' + $this.getOption('elementIdentifier') + '[store_pickup_dates]"]').attr('disabled', 'disabled').val('');
                        $this._setDefaultDeliveryOption($this);
                        $this.$('.jsAddressInput').removeAttr('disabled');
                        $this.$('[name="' + $this.getOption('elementIdentifier') + '[shipping_cost]"]').removeAttr('disabled');
                        $this.$('[name^="' + $this.getOption('elementIdentifier') + '[days_"]').removeAttr('disabled');
                        $this.$('[name^="' + $this.getOption('elementIdentifier') + '[sending_date_"]').removeAttr('disabled');
                        $this.$('[name^="' + $this.getOption('elementIdentifier') + '[parcel_"]').removeAttr('disabled');
                        $this._hidePickupPoints($this);
                        if ($this._hasMicrosoftRestrictions($this)) {
                            $this._blockOrderButton($this);
                        }
                        break;
                }

                $this._checkPayments($this);
                $(document).trigger('deliveryChange');
            }
        },

        /**
         * On click delivery option row.
         *
         * @param e
         * @param $this
         * 
         */
        'click .jsCartDeliveryOption': function (e, $this) {
            const $radio = $(this).find('input[type="radio"]');
            if (!$radio.is(':checked')) {
                $radio.prop('checked', true).trigger('change');
            }
        },

        /**
         * On click store row.
         *
         * @param e
         * @param $this
         * 
         */
        'click .jsStoresListOption': function (e, $this) {
            $(this).find('input[type="radio"]').prop('checked', true).trigger('change');
        },

        /**
         * On change delivery option.
         *
         * @param e
         * @param $this
         * 
         */
        'change .jsCartDeliveryOption input[type="radio"]': function (e, $this) {
            const $radio = $(this),
                  $option = $radio.closest('.jsCartDeliveryOption'),
                  optionData = $option.data();

            $this._renderDeliveryCheckout($this, $radio.attr('value'), optionData.cost);

            if (optionData.method === 'todoor' || optionData.method === 'post') {
                $this._showAddressInput($this);
                $this._hidePickupPoints($this);
            } else if (optionData.method === 'pickup') {
                $this._hideAddressInput($this);
                $this._showActivePickupPoint($this, (optionData.pickupPointIds || []), (optionData.defaultPickupPoint || ''));
            }

            if (optionData.method === 'todoor' && $this._tariffNameIsYandexGo($option.text())) { // Срочная доставка
                $this._hideSendingDates($this);
                $this.$('[name^="' + $this.getOption('elementIdentifier') + '[sending_date_"]').each(function() {
                    const $input = $(this);
                    $input
                        .data('originalDate', $input.val())
                        .val($this.today);
                });
            } else {
                $this._showSendingDates($this);
                $this.$('[name^="' + $this.getOption('elementIdentifier') + '[sending_date_"]').each(function() {
                    const $input = $(this);
                    if ($input.data('originalDate')) {
                        $input
                            .val($input.data('originalDate'))
                            .data($input.data('originalDate', false));
                    }
                });
            }

            $this.$('[name="' + $this.getOption('elementIdentifier') + '[days_min]"]').val(optionData.days.min);
            $this.$('[name="' + $this.getOption('elementIdentifier') + '[days_max]"]').val(optionData.days.max);

            $this._setDeliveryDateInTimeline($this);

            $this._checkPayments($this);

            $(document).trigger('deliveryChange');
        },

        /**
         * On change delivery option.
         *
         * @param e
         * @param $this
         * 
         */
        'change .jsStoresListOption input[type="radio"]': function (e, $this) {
            const $radio = $(this),
                  optionDates = $radio.closest('.jsStoresListOption').find('.jsConditionsDeliveryPickingDate').data('raw');

            $this.$('[name="' + $this.getOption('elementIdentifier') + '[store_pickup_dates]"]').val(optionDates);
        },

        /**
         * Go to checkout.
         *
         * @param e
         * @param $this
         * 
         */
        'click .jsToSecondStep': function (e, $this) {
            if ($(this).data('update')) {
                if ($this._hasMicrosoftRestrictions($this)) {
                    $this._setDeliveryUnavailableMsg($this);
                } else {
                    $this._hideSendingDates($this);
                    $this._setDates($this, function() {
                        $this._updateDelivery($this);
                    });
                }
                $this._updateParcelInfo($this);
            }
        },

        /**
         * Оn submit order.
         *
         * @param e
         * @param $this
         */
        'submit form[action$="cart"], form[action$="credit"]': function (e, $this) {
            const $form = $(this),
                  $addressInput = $form.find('.jsAddressInput'),
                  $originalAddressInput = $this.$('[name="' + $this.getOption('elementIdentifier') + '[original_address]"]');
            if (!$addressInput.is(':disabled')) {
                if ($originalAddressInput.data('need_update') === true) {
                    $originalAddressInput.data('need_update', false);

                    /** @type {UserLocation} */
                    const userLocation = $this._getUserLocation($this);
                    let originalAddress

                    if ($this._getCountryCode(userLocation) === 'RU') {
                        originalAddress = [
                            'Россия',
                            userLocation.location || userLocation.city,
                            $addressInput.val()
                        ].join(', ');

                        $originalAddressInput.val(originalAddress);

                        if (!$addressInput.data('dadata_checked')) {
                            e.preventDefault();
                            $addressInput.data('dadata_checked', true);

                            $.ajax({
                                'url'       : '/index.php?tmpl=component',
                                'dataType'  : 'json',
                                'type'      : 'POST',
                                'data'      : {
                                    'option'     : 'com_hyperpc',
                                    'task'       : 'delivery.clean-address',
                                    'format'     : 'raw',
                                    'address'    : originalAddress
                                }
                            })
                            .done(function(data) {
                                if (Array.isArray(data)) {
                                    const addressData = data[0];

                                    if (addressData.qc === 0) {
                                        // адрес распознан точно

                                        const localityArr = [
                                            addressData.region_with_type
                                        ];

                                        if (addressData.city_with_type !== addressData.region_with_type) {
                                            localityArr.push(addressData.city_with_type);
                                        }

                                        localityArr.push(addressData.settlement_with_type);

                                        const localityFiltered = localityArr.filter(function(item) {
                                            return item != null;
                                        });

                                        // Адрес до уровня улицы
                                        const locality = localityFiltered.join(', ');
                                        $this.$('[name="' + $this.getOption('elementIdentifier') + '[granular_address_locality]"]').val(locality);

                                        // Индекс почтового отделения
                                        const postalCode = addressData.postal_code || '';
                                        $this.$('[name="' + $this.getOption('elementIdentifier') + '[granular_address_postal_code]"]').val(postalCode);

                                        // Улица (проспект, переулок, проезд, шоссе и т.д)
                                        const street = {
                                            'type' : addressData.street_type_full || '',
                                            'name' : addressData.street || ''
                                        };
                                        $this.$('[name="' + $this.getOption('elementIdentifier') + '[granular_address_street_type]"]').val(street.type);
                                        $this.$('[name="' + $this.getOption('elementIdentifier') + '[granular_address_street_name]"]').val(street.name);

                                        // Дом
                                        const house = {
                                            'type' : addressData.house_type_full || '',
                                            'name' : addressData.house || ''
                                        };
                                        $this.$('[name="' + $this.getOption('elementIdentifier') + '[granular_address_house_type]"]').val(house.type);
                                        $this.$('[name="' + $this.getOption('elementIdentifier') + '[granular_address_house_name]"]').val(house.name);

                                        // Корпус/строение
                                        const block = {
                                            'type' : addressData.block_type_full || '',
                                            'name' : addressData.block || ''
                                        };
                                        $this.$('[name="' + $this.getOption('elementIdentifier') + '[granular_address_block_type]"]').val(block.type);
                                        $this.$('[name="' + $this.getOption('elementIdentifier') + '[granular_address_block_name]"]').val(block.name);

                                        // Квартира/офис
                                        const flat = {
                                            'type' : addressData.flat_type_full || '',
                                            'name' : addressData.flat || ''
                                        }
                                        $this.$('[name="' + $this.getOption('elementIdentifier') + '[granular_address_flat_type]"]').val(flat.type);
                                        $this.$('[name="' + $this.getOption('elementIdentifier') + '[granular_address_flat_name]"]').val(flat.name);

                                        // ФИАС код
                                        let fias_id = '';
                                        if (addressData.qc_complete === 0) {
                                            fias_id = addressData.fias_id || '';
                                        } else if (addressData.qc_house === 2) {
                                            fias_id = addressData.fias_id || '';
                                        }
                                        $this.$('[name="' + $this.getOption('elementIdentifier') + '[fias_id]"]').val(fias_id);
                                    }
                                } else {
                                    // error
                                }

                                $form.submit();
                            }).fail(function() {
                                // error
                                $form.submit();
                            });
                        }
                    } else {
                        const originalAddressParts = [];
                        if (userLocation.location !== '') {
                            originalAddressParts.push(userLocation.location);
                        } else {
                            originalAddressParts.push([
                                userLocation.country,
                                userLocation.city
                            ]);
                        }
                        originalAddressParts.push($addressInput.val())
                        originalAddress = originalAddressParts.join(', ');
                        $originalAddressInput.val(originalAddress);
                    }
                }
            } else { // ПВЗ
                $this.$('[name^="' + $this.getOption('elementIdentifier') + '[granular_address_"]').val('');
                $this.$('[name="' + $this.getOption('elementIdentifier') + '[original_address]"]').val('');
                $this.$('[name="' + $this.getOption('elementIdentifier') + '[fias_id"]').val('');

                let pickupPointAddress = $this.$('[name="' + $this.getOption('elementIdentifier') + '[pickup_point_address]"]').val();
                if (/^\d{6},\sул\./.test(pickupPointAddress)) { // there is no city in the address
                    const location = localStorage.getItem('hp_geo_location');
                    if (location) {
                        pickupPointAddress = pickupPointAddress.slice(0, 8) + location.replace(/^\d{6},\s/, '') + ', ' + pickupPointAddress.slice(8);
                        $this.$('[name="' + $this.getOption('elementIdentifier') + '[pickup_point_address]"]').val(pickupPointAddress);
                    }
                }
            }
        },

        /**
         * On type in address input.
         *
         * @param e
         * @param $this
         */
        'input .jsAddressInput': function (e, $this) {
            const $originalAddressInput = $this.$('[name="' + $this.getOption('elementIdentifier') + '[original_address]"]');
            if ($originalAddressInput.data('need_update') !== true) {
                $originalAddressInput.data('need_update', true);
            }
        },

        /**
         * On click choose another pickup point link.
         *
         * @param e
         * @param $this
         */
        'click [href="#pickup-points"]': function (e, $this) {
            const $activeOption = $this._getActiveDeliveryOption($this);
            let pickupPointsInfo = $activeOption.data('pickupPointsInfo');
            if (!pickupPointsInfo) {
                $this._getPickupPointsInfo($this, $activeOption.data('pickupPointIds'));
            } else {
                $this._buildPickupPointsList($this, pickupPointsInfo)
            }
        },

    });
});
