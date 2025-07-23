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

    JBZoo.widget('HyperPC.Geo.YandexDelivery.Card', {
            'deliveryCacheLifetime' : 3600 * 72 * 1000, // 72h
            'lang' : {}
        },

        {

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
         * @property {string|number} length item length in cm
         * @property {string|number} width item width in cm
         * @property {string|number} height item height in cm
         * @property {string|number} weight item weight in kg
         *
         * @typedef {Object} DatesRange
         * @property {string} min date in "Jan 01 2000" format
         * @property {string} max date in "Jan 01 2000" format
         *
         * @typedef {Object} DaysForShipping
         * @property {number} min min days for delivery
         * @property {number} max max days for delivery
         *
         * @typedef {Object} DateInfo
         * @property {string} raw date in "Jan 01 2000" or "Jan 01 2000 - Jan 02 2000" format
         * @property {string} value easy to read value
         */

        deliveryCache : {},
        cacheKey : '',
        activeAjax : null,

        lang: {
            free: '',
            startsFrom: '',
            methodName: {
                todoor: '',
                connection: '',
                express: '',
                pickup: '',
                post: '',
                taxi: ''
            }
        },

        /**
         * Initialize widget.
         *
         * @param $this
         */
        init : function ($this) {
            if (typeof $this.getOption('lang') === 'object') {
                $.extend(true, $this.lang, $this.getOption('lang'));
            }

            $this.deliveryCache = $this._localStorageAvailable() ? JSON.parse(localStorage.getItem('hp_delivery_cache')) || {} : {};

            $this.cacheKey = $this._getCacheKey($this);
            $this._clearOutdatedCache($this);

            $this.constructor.parent.init($this);
            $this.constructor.parent.constructor.parent.init($this);

            $this.el.on('datesUpdated', function(e, pickingDates) {
                $this.orderPickingDates = pickingDates || {};
                $this._updateDelivery($this);
            });
        },

        /**
         * Get accessed value
         *
         * @param $this
         *
         * @returns {number}
         */
        _getAssessedValue : function ($this) {
            return $('.hp-item-price__price').eq(0).find('.simpleType-value').attr('content');
        },

        /**
         * Get item cache key.
         *
         * @param $this
         */
        _getCacheKey : function ($this) {
            /** @type {ParcelInfo} */
            const parcelInfo = $this._getParcelInfo($this),
                  itemType = $this._getItemType($this),
                  price = $this._getAssessedValue($this);
            return [itemType, parcelInfo.length, parcelInfo.width, parcelInfo.height, parcelInfo.weight, price].join('-');
        },

        /**
         * Get item type.
         *
         * @param $this
         * @returns {string}
         */
        _getItemType : function ($this) {
            return $this.el.data('itemtype');
        },

        /**
         * Clear outdated delivery cache.
         *
         * @param $this
         */
        _clearOutdatedCache : function ($this) {
            const currentDate = new Date();
            for (cacheItem in $this.deliveryCache) {
                const itemCreated = new Date($this.deliveryCache[cacheItem].created);
                if (currentDate - itemCreated > this.options.deliveryCacheLifetime) {
                    delete $this.deliveryCache[cacheItem];
                }
            }

            if ($this._localStorageAvailable()) {
                localStorage.setItem('hp_delivery_cache', JSON.stringify($this.deliveryCache));
            }
        },

        /**
         * Check user side cache
         *
         * @param $this
         * @returns {boolean}
         */
        _checkUserSideCache : function ($this) {
            /** @type {UserLocation} */
            const userLocation = $this._getUserLocation($this);

            const cacheItem = $this.deliveryCache[$this.cacheKey];
            if (cacheItem && typeof cacheItem[$this.cityIdentifier] !== 'undefined' &&
                cacheItem[$this.cityIdentifier] === userLocation[$this.cityIdentifier] &&
                cacheItem.sendingFrom === $this.sendingFrom
            ) {
                return true;
            }

            return false;
        },

        /**
         * Update delivery from cache
         *
         * @param $this
         */
        _updateDeliveryFromCache : function ($this) {
            const data = $this.deliveryCache[$this.cacheKey].info;

            if (!Object.keys(data).length) {
                $this._setDeliveryUnavailableMsg($this);
            } else {
                let html = '';
                for (let method in data) {
                    html += $this._getDeliveryMethodHtml($this, method, data[method].days, data[method].cost);
                }

                $this._hideSpinner($this);
                $this.$('.jsDeliveryOptions').append(html);
            }
        },

        /**
         * Get courier state
         *
         * @param $this
         * @returns {string}
         */
        _getCourierState : function ($this) {
            const itemType = $this._getItemType($this);
            let hyperpcCourierState = $this.hyperpcCourier.DEFAULT;
            if ((itemType === 'product' || itemType === 'notebook')) {
                // Если компьютер или ноутбук и доступна доставка курьером HYPERPC, то другие варианты не нужны
                hyperpcCourierState = $this.hyperpcCourier.ONLY;
            }

            return hyperpcCourierState;
        },

        /**
         * Get parcel dimensions and weight
         *
         * @param $this
         * @returns {ParcelInfo}
         */
        _getParcelInfo : function ($this) {
            /** @type {ParcelInfo} */
            const parcelInfo = {
                'length' : $this.el.data('dimensions').length,
                'width' : $this.el.data('dimensions').width,
                'height' : $this.el.data('dimensions').height,
                'weight' : $this.el.data('weight')
            }

            return parcelInfo;
        },

        /**
         * Get sending dates
         *
         * @param $this
         *
         * @returns {DatesRange} {
         *     min: string,
         *     max: string
         * }
         */
        _getSendingDates : function ($this) {
            /** @type {DateInfo} */
            const datesInfo = $this._getSendingDatesInfo($this);

            return $this._splitDates(datesInfo.raw);
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
        _handleSuccessUpdateDelivery : function ($this, response, userLocation, parcelInfo, hyperpcCourierState) {
            const responseData = response.body || false,
                  itemType = $this._getItemType($this),
                  methodsHtml = []

            if (typeof responseData === 'object') {
                $this.deliveryCache[$this.cacheKey] = {
                    created: new Date(),
                    sendingFrom: $this.sendingFrom,
                    info: {},
                    hasHyperpcCourier: false
                };

                $this.deliveryCache[$this.cacheKey][$this.cityIdentifier] = userLocation[$this.cityIdentifier];

                for (let method in responseData) {
                    const methodData = responseData[method],
                          expressShipping = {};
                    let deliveryWithConnection,
                        deliveryTaxi;

                    for (let index in methodData) {
                        const tariff = methodData[index];

                        tariff.cost += $this._getInsuranceCost($this, tariff.companyName);

                        if (tariff.companyName === 'Курьер HYPERPC') {
                            $this.deliveryCache[$this.cacheKey].hasHyperpcCourier = true;
                            if (itemType === 'product' || itemType === 'notebook') {
                                const connectionCost = $this.getOption('connectionCost');
                                deliveryWithConnection = [{ // delivery with connection
                                    companyName: '',
                                    cost: tariff.cost + connectionCost,
                                    maxDays: tariff.maxDays,
                                    minDays: tariff.minDays,
                                }];
                            }
                        }

                        if ($this._tariffNameIsYandexGo(tariff.companyName)) {
                            delete methodData[index];
                            deliveryTaxi = [tariff];
                        }

                        if ($this._tariffNameIsExpress(tariff.companyName)) {
                            delete methodData[index];
                            expressShipping[index] = tariff;
                        }
                    }

                    if (deliveryTaxi) {
                        methodsHtml.push($this._buildDeliveryMethodHtml($this, deliveryTaxi, 'taxi'));
                        deliveryTaxi = null;
                    }

                    if (Object.keys(methodData).length > 0) {
                        methodsHtml.push($this._buildDeliveryMethodHtml($this, methodData, method));

                        if (deliveryWithConnection) {
                            methodsHtml.push($this._buildDeliveryMethodHtml($this, deliveryWithConnection, 'connection'));
                            deliveryWithConnection = null;
                        }
                    }

                    if (method === 'todoor' && Object.keys(expressShipping).length) {
                        methodsHtml.push($this._buildDeliveryMethodHtml($this, expressShipping, 'express'));
                    }
                }
            }

            if (!methodsHtml.length) {
                $this._setDeliveryUnavailableMsg($this);
            } else {
                $this.$('.jsDeliveryOptions').append(methodsHtml);
                $this._hideSpinner($this);
            }

            if ($this._localStorageAvailable()) {
                localStorage.setItem('hp_delivery_cache', JSON.stringify($this.deliveryCache));
            }
        },

        /**
         * Build delivery method HTML
         *
         * @param $this
         * @param {array} deliveryMethod
         * @param {string} methodAlias
         *
         * @returns {string}
         */
        _buildDeliveryMethodHtml : function ($this, deliveryMethod, methodAlias) {
            const deliveryMethodLength = Object.keys(deliveryMethod).length,
                  methodConditions = {
                      maxCost : 0,
                      minCost : 0,
                      minDays : -1,
                      maxDays : -1
                  };

            if (deliveryMethodLength === 1) {
                const method = deliveryMethod[Object.keys(deliveryMethod)[0]];
                methodConditions.minCost = method.cost;
                methodConditions.maxCost = method.cost;

                methodConditions.minDays = method.minDays;
                methodConditions.maxDays = method.maxDays;
            } else if (deliveryMethodLength > 1) {
                for (let tariffId in deliveryMethod) {
                    if (Object.hasOwnProperty.call(deliveryMethod, tariffId)) {
                        const tariff = deliveryMethod[tariffId];

                        if (methodConditions.minDays === -1) {
                            methodConditions.minCost = tariff.cost;
                            methodConditions.maxCost = tariff.cost;
                            methodConditions.minDays = tariff.minDays;
                        } else {
                            methodConditions.minCost = Math.min(methodConditions.minCost, tariff.cost);
                            methodConditions.maxCost = Math.max(methodConditions.maxCost, tariff.cost);
                            methodConditions.minDays = Math.min(methodConditions.minDays, tariff.minDays);
                        }

                        methodConditions.maxDays = Math.max(methodConditions.maxDays, tariff.maxDays);
                    }
                }
            }

            /** @type {DaysForShipping} */
            const methodDays = {
                min : methodConditions.minDays,
                max : methodConditions.maxDays
            };

            let methodCost = $this._priceFormat(methodConditions.minCost);
            if (methodConditions.minCost === 0 && methodConditions.maxCost === 0) {
                methodCost = $this.lang.free;
            } else if (methodConditions.minCost !== methodConditions.maxCost || methodAlias === 'taxi') {
                methodCost = $this.lang.startsFrom.replace('%s', $this._priceFormat(methodConditions.minCost));
            }

            $this.deliveryCache[$this.cacheKey].info[methodAlias] = {
                'cost' : methodCost,
                'days' : methodDays
            };

            return $this._getDeliveryMethodHtml($this, methodAlias, methodDays, methodCost);
        },

        /**
         * Get delivery dates in string format
         *
         * @param $this
         * @param {DaysForShipping} days
         * @param {string} methodAlias
         *
         * @returns {string}
         */
        _getDeliveryDatesString : function ($this, days, methodAlias) {
            if (methodAlias === 'taxi') {
                return 'Доставка Yandex.Go';
            }

            /** @type {DatesRange} */
            const sendingDates = $this._getSendingDates($this),
                  minDate = new Date(sendingDates.min),
                  maxDate = new Date(sendingDates.max)

            minDate.setHours(0,0);
            maxDate.setHours(0,0);

            // Clone days
            /** @type {DaysForShipping} */
            const methodDays = Object.assign({}, days);

            // Set days for delivery by HYPERPC courier
            if ($this.deliveryCache[$this.cacheKey].hasHyperpcCourier &&
                (methodAlias === 'todoor' || methodAlias === 'connection')
            ) {
                const today = $this.el.data('today');
                if (typeof today !== 'undefined') {
                    const todayDate = new Date(today);

                    Object.assign(methodDays, $this._getHypeprcCourierDays(todayDate, minDate));
                }
            }

            minDate.setDate(minDate.getDate() + methodDays.min);
            maxDate.setDate(maxDate.getDate() + methodDays.max);

            return $this._dateToString($this, minDate + ' - ' + maxDate);
        },

        /**
         * Get delivery method icon
         *
         * @param {string} methodAlias
         *
         * @returns {string}
         */
        _getMethodIconFromAlias : function (methodAlias) {
            let icon = 'world';
            switch (methodAlias) {
                case 'todoor':
                    icon = 'location';
                    break;
                case 'express':
                    icon = 'bolt';
                    break;
                case 'pickup':
                    icon = 'home';
                    break;
                case 'connection':
                    icon = 'desktop';
                    break;
                case 'taxi':
                    icon = 'clock';
                    break;
            }

            return icon;
        },

        /**
         * Get delivery method html
         *
         * @param $this
         * @param {string} methodAlias
         * @param {DaysForShipping} days
         * @param {string} cost
         *
         * @returns {string}
         */
        _getDeliveryMethodHtml : function ($this, methodAlias, days, cost) {
            if (methodAlias === 'taxi' && !$this._canSendTaxi($this)) {
                return '';
            }

            const methodName = $this._getMethodNameFromAlias(methodAlias),
                  daysString = $this._getDeliveryDatesString($this, days, methodAlias),
                  icon = $this._getMethodIconFromAlias(methodAlias);

            return '<li>' +
                        '<div class="uk-grid uk-grid-small uk-flex-middle">' +
                            '<div class="uk-flex-none">' +
                                '<span uk-icon="icon: ' + icon + '; ratio: 1.5"></span>' +
                            '</div>' +
                            '<div class="uk-width-expand">' +
                                '<div class="uk-grid uk-grid-small uk-flex-between uk-flex-middle">' +
                                    '<div>' +
                                        '<div class="tm-text-medium uk-text-emphasis" style="line-height: 1.2">' + methodName + '</div>' +
                                        '<div class="uk-text-muted">' + daysString + '</div>' +
                                    '</div>' +
                                    '<div>' +
                                        '<span class="uk-text-nowrap uk-text-emphasis">' + cost + '</span>' +
                                    '</div>' +
                                '</div>' +
                            '</div>' +
                        '</div>' +
                    '</li>';
        },

        /**
         * Can send item by taxi (in 2 hours)
         *
         * @param $this
         *
         * @returns {boolean}
         */
        _canSendTaxi : function ($this) {
            const stores = $this.orderPickingDates,
                  storeId = $this.sendingFrom;

            return stores[storeId] && stores[storeId]['availableNow'];
        },

        /**
         * Get delivery method title from its alias
         *
         * @param {string} methodAlias
         * @returns {string}
         */
        _getMethodNameFromAlias : function (methodAlias) {
            let methodName = '';
            switch (methodAlias) {
                case 'todoor':
                case 'pickup':
                case 'post':
                case 'connection':
                case 'express':
                    methodName = $this.lang.methodName[methodAlias];
                    break;
                case 'taxi': // TODO show link to article or at least get hours from options
                    methodName = 'Срочная доставка за 2 часа';
                    methodName += '&nbsp;<span uk-icon="icon: question; ratio: 0.7" title="Доступно при заказе до 19:00" uk-tooltip></span>';
                    break;
            }

            return methodName;
        },

        /**
         * Checks if the tariff is express.
         *
         * @param {string} tariffName
         * @returns {bool}
         */
        _tariffNameIsExpress : function (tariffName) {
            return tariffName.search(/(Express)/i) !== -1;
        },

        /**
         * Set delivery unavailable message to the delivery options container.
         *
         * @param $this
         */
        _setDeliveryUnavailableMsg : function ($this) {
            $this._hideSpinner($this);
            $this.$('.jsDeliveryOptions li').attr('hidden', 'hidden');
            $this.$('.jsDeliveryUnavailableMsg').removeAttr('hidden');
        },

        /**
         * Show spinner.
         *
         * @param $this
         */
        _showSpinner : function ($this) {
            $this.$('.jsDeliverySpinner').removeAttr('hidden');
            $this.$('.jsDeliveryOptions').find('li').remove();
            $this.$('.jsDeliveryUnavailableMsg').attr('hidden', 'hidden');
        },

        /**
         * Hide spinner.
         *
         * @param $this
         */
        _hideSpinner : function ($this) {
            $this.$('.jsDeliverySpinner').attr('hidden', 'hidden')
        }

    });

});