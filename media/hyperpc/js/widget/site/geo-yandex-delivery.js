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

    JBZoo.widget('HyperPC.Geo.YandexDelivery', {
            'siteContext'         : 'hyperpc',
            'connectionCost'      : 750,
            'cityIdentifier'      : 'geoId',
            'defaultSendingGeoId' : 213, // Moscow
            'orderPickingDates'   : {},
            'langTag'             : 'en-GB',
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
         * @property {number} min days for delivery
         * @property {number} max days for delivery
         *
         * @typedef {Object} DateInfo
         * @property {string} raw date in "Jan 01 2000" or "Jan 01 2000 - Jan 02 2000" format
         * @property {string} value easy to read value
         */

        activeAjax : null,

        sendingFrom : 1,
        orderPickingDates : [],

        /**
         * Need HYPERPC courier state.
         * 
         * @readonly
         * @enum {string}
         */
        hyperpcCourier : {
            DEFAULT: 'courier_default',
            EXCEPT: 'courier_except',
            ONLY: 'courier_only',
        },

        /**
         * Initialize widget.
         *
         * @param $this
         */
        init : function ($this) {
            Object.freeze($this.hyperpcCourier);

            $this.orderPickingDates = $this.getOption('orderPickingDates');

            $this.cityIdentifier = $this.getOption('cityIdentifier');
        },

        /**
         * Get accessed value
         *
         * @param $this
         * @returns {number}
         */
        _getAssessedValue : function ($this) {
            return 0;
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
                'length' : 10,
                'width' : 10,
                'height' : 10,
                'weight' : 1.00
            }

            return parcelInfo;
        },

        /**
         * On location defined.
         */
        _onLocationDefined : function () {
            $this = this;

            /** @type {UserLocation} */
            const userLocation = this._getUserLocation($this);

            $this._updateSendingFromStoreId($this, userLocation);

            if (userLocation[$this.cityIdentifier]) {
                $this._updateDelivery($this);
            } else {
                $this._setDeliveryUnavailableMsg($this);
            }
        },

        /**
         * Update sendingFrom
         *
         * @param $this
         * @param {UserLocation=} userLocation
         */
        _updateSendingFromStoreId : function ($this, userLocation) {
            /** @type {UserLocation} */
            userLocation = userLocation || $this._getUserLocation($this);

            if (Object.keys($this.orderPickingDates).length > 0) {
                let preferablySendFrom = $this.getOption('defaultSendingGeoId');
                if ($this._locationIsSpb(userLocation)) {
                    preferablySendFrom = 2; // TODO rewrite it
                }

                $this.sendingFrom = null;
                for (let storeId in $this.orderPickingDates) {
                    const storeData = $this.orderPickingDates[storeId];
                    if (parseInt(storeData.geoId) === preferablySendFrom && storeData.availableNow) {
                        $this.sendingFrom = storeId;
                    } else if (
                        $this.sendingFrom === null &&
                        parseInt(storeData.geoId) === $this.getOption('defaultSendingGeoId')
                    ) {
                        $this.sendingFrom = storeId;
                    }
                }

                if ($this.sendingFrom === null) {
                    $this.sendingFrom = Object.keys($this.orderPickingDates)[0];
                }
            }
        },

        /**
         * Check user side cache
         *
         * @param $this
         * @returns {boolean}
         */
        _checkUserSideCache : function ($this) {
            return false;
        },

        /**
         * Update delivery from cache
         *
         * @param $this
         */
        _updateDeliveryFromCache : function ($this) {},

        /**
         * Get courier state
         *
         * @param $this
         * @returns {string}
         */
        _getCourierState : function ($this) {
            return $this.hyperpcCourier.DEFAULT;
        },

        /**
         * Get days for shipping by HYPERPC courier
         * 
         * @param {Date} now
         * @param {Date} sendingTime
         *
         * @returns {DaysForShipping}
         */
        _getHypeprcCourierDays: function (now, sendingTime) {
            const currentHour = now.getHours(),
                  todayDate = new Date(now),
                  sendingDate = new Date(sendingTime),
                  msPerDay = 1000 * 60 * 60 * 24,
                  days = {
                    min: 1,
                    max: 1
                  };

            todayDate.setHours(0, 0);
            sendingDate.setHours(0, 0);

            const sendingDaysDiff = (sendingDate - todayDate) / msPerDay,
                  isTomorrow = sendingDaysDiff === 1;

            // если отправка завтра, но время с 11:00 до 16:59 то доставка также завтра
            if (isTomorrow && currentHour >= 11 && currentHour <= 16) {
                days.min = 0;
                days.max = 0;
            }

            return days;
        },

        /**
         * Get geoId from storeId
         * 
         * @param $this
         * @param {number} storeId
         * @returns {number}
         */
        _getGeoIdFromStoreId : function ($this, storeId) {
            if (Object.keys($this.orderPickingDates).length > 0) {
                const storeData = $this.orderPickingDates[storeId] || {geoId: $this.getOption('defaultSendingGeoId')};
                if (storeData.geoId && storeData.geoId !== '') {
                    return parseInt(storeData.geoId);
                }
            }

            return $this.getOption('defaultSendingGeoId');
        },

        /**
         * Get and update delivery conditions.
         *
         * @param $this
         * @param {UserLocation} userLocation
         */
        _updateDelivery : function ($this) {
            $this._showSpinner($this);

            /** @type {UserLocation} */
            const userLocation = $this._getUserLocation($this);

            if ($this._checkUserSideCache($this)) {
                $this._updateDeliveryFromCache($this);
                return;
            }

            const geoId = userLocation[$this.cityIdentifier];
            if (geoId === undefined) {
                console.error('Unknown city identifier');
                return;
            }

            /** @type {ParcelInfo} */
            const parcelInfo = $this._getParcelInfo($this);
            if (typeof parcelInfo === 'object') {
                const hyperpcCourierState = $this._getCourierState($this);

                if ($this.activeAjax) {
                    $this.activeAjax.hasAborted = true;
                    $this.activeAjax.abort();
                }

                $this.activeAjax = $.ajax({
                    'url'       : '/index.php?tmpl=component',
                    'dataType'  : 'json',
                    'type'      : 'POST',
                    'data'      : {
                        'option'          : 'com_hyperpc',
                        'task'            : 'delivery.get-delivery-options',
                        'format'          : 'raw',
                        'geo_id_to'       : geoId,
                        'geo_id_from'     : $this._getGeoIdFromStoreId($this, $this.sendingFrom),
                        'length'          : parcelInfo.length,
                        'width'           : parcelInfo.width,
                        'height'          : parcelInfo.height,
                        'weight'          : parcelInfo.weight,
                        'items_sum'       : $this._getAssessedValue($this),
                        'hyperpc_courier' : hyperpcCourierState
                    }
                });

                $this.activeAjax
                    .done(function(response) {
                        if (response.result) {
                            $this._handleSuccessUpdateDelivery($this, response, userLocation, parcelInfo, hyperpcCourierState);
                        } else {
                            $this._handleFailUpdateDelivery($this);
                        }
                    })
                    .fail(function (jqXHR) {
                        if (!jqXHR.hasAborted) {
                            $this._handleFailUpdateDelivery($this);
                        }
                    })
                    .always(function(jqXHR) {
                        $this.activeAjax = null;
                        if (!jqXHR.hasAborted) {
                            $this._afterUpdateDelivery($this);
                        }
                    });
            } else {
                $this._handleParcelInfoError($this);
            }
        },

        /**
         * Get insurance cost
         *
         * @param $this
         * @param companyName
         *
         * @returns {number}
         */
        _getInsuranceCost : function ($this, companyName) {
            const assessedValue = $this._getAssessedValue($this);
            let percent = 0.6;
            if (companyName.search(/(CDEK|СДЭК)/) !== -1) {
                percent = 0.5;
            } else if (companyName.search(/(Деловые линии|DelLin)/i) !== -1) {
                percent = 0.3;
            } else if (companyName.search(/DHL|Avis Logistics/i) !== -1) {
                percent = 1;
            } else if (companyName.search(/(Курьер HYPERPC|Курьер Yandex\.Go|Курьер ТК|FedEx)/i) !== -1) {
                return 0;
            }

            return Math.ceil((Math.floor(assessedValue / 100 * percent)) / 10) * 10;
        },

        /**
         * Get pickup points info
         *
         * @param $this
         * @param {number[]} pickupPointIds
         */
        _getPickupPointsInfo : function ($this, pickupPointIds) {
            if (typeof pickupPointIds === 'object' && pickupPointIds.length && pickupPointIds.length > 0) {
                if ($this.activeAjax) {
                    $this.activeAjax.hasAborted = true;
                    $this.activeAjax.abort();
                }

                /** @type {UserLocation} */
                const userLocation = $this._getUserLocation($this);

                const geoId = userLocation[$this.cityIdentifier];
                if (geoId === undefined) {
                    console.error('Unknown city identifier');
                    return;
                }

                $this.activeAjax = $.ajax({
                    'url'       : '/index.php?tmpl=component',
                    'dataType'  : 'json',
                    'type'      : 'POST',
                    'data'      : {
                        'option'           : 'com_hyperpc',
                        'task'             : 'delivery.get-pickup-points-info',
                        'format'           : 'raw',
                        'pickup_point_ids' : pickupPointIds,
                        'geo_id'           : geoId
                    }
                });

                $this.activeAjax
                    .done(function(response) {
                        if (response.result) {
                            $this._handleSuccessGetPickupPointsInfo($this, response);
                        } else {
                            $this._handleFailGetPickupPointsInfo($this);
                        }
                    })
                    .fail(function (jqXHR) {
                        if (!jqXHR.hasAborted) {
                            $this._handleFailGetPickupPointsInfo($this);
                        }
                    })
                    .always(function(jqXHR) {
                        $this.activeAjax = null;
                        if (!jqXHR.hasAborted) {}
                    });
            }
        },

        /**
         * Handle success get pickup points info
         *
         * @param $this
         * @param response
         */
        _handleSuccessGetPickupPointsInfo : function ($this, response) {},

        /**
         * Handle fail get pickup points info
         *
         * @param $this
         */
        _handleFailGetPickupPointsInfo : function ($this) {},

        /**
         * Handle parcel info error
         *
         * @param $this
         */
        _handleParcelInfoError : function ($this) {
            $this._setDeliveryUnavailableMsg($this);
        },

        /**
         *
         * @param $this
         * @param {Object} response
         * @param {UserLocation} userLocation
         * @param {ParcelInfo} parcelInfo
         * @param {string} hyperpcCourierState
         */
        _handleSuccessUpdateDelivery : function ($this, response, userLocation, parcelInfo, hyperpcCourierState) {
            $this._hideSpinner($this);
        },

        /**
         * Handle fail update delivery
         *
         * @param $this
         */
        _handleFailUpdateDelivery : function ($this) {
            $this._setDeliveryUnavailableMsg($this);
        },

        /**
         * After update delivery
         *
         * @param $this
         */
        _afterUpdateDelivery : function ($this) {},

        /**
         * Set delivery unavailable message to the delivery options container.
         *
         * @param $this
         */
        _setDeliveryUnavailableMsg : function ($this) {
            $this._hideSpinner($this);
        },

        /**
         * Show spinner.
         *
         * @param $this
         */
        _showSpinner : function ($this) {},

        /**
         * Hide spinner.
         *
         * @param $this
         */
        _hideSpinner : function ($this) {},

        /**
         * Checks if the tariff name is YandexGo.
         *
         * @param {string} tariffName
         */
        _tariffNameIsYandexGo : function (tariffName) {
            return tariffName.search(/(Yandex\.Go)/i) !== -1;
        },

        /**
         * Get min an max date from raw string
         *
         * @param {string} dates date in "Jan 01 2000" or "Jan 01 2000 - Jan 02 2000" format
         *
         * @returns {DatesRange} {
         *     min: string,
         *     max: string
         * }
         */
        _splitDates : function (dates) {
            const datesArr = dates.split(' - ');
            const minDate = datesArr[0];
            let maxDate = minDate;
            if (typeof datesArr[1] !== 'undefined') {
                maxDate = datesArr[1];
            }

            return {
                min: minDate,
                max: maxDate
            }
        },

        /**
         * Get sending dates info
         *
         * @param $this
         *
         * @returns {DateInfo} {
         *     raw: string;
         *     value: string;
         * }
         */
        _getSendingDatesInfo : function ($this) {
            if (typeof $this.orderPickingDates[$this.sendingFrom] === 'undefined') {
                return {raw: '', value: ''};
            }

            return $this.orderPickingDates[$this.sendingFrom].sending || {raw: '', value: ''};
        },

        /**
         * Raw date to easy to read format
         *
         * @param $this
         * @param {string} date raw date
         *
         * @returns {string}
         */
        _dateToString: function ($this, date) {
            const dates = $this._splitDates(date),
                  minDate = new Date(dates.min),
                  langTag = $this.getOption('langTag');

            const dateOptions = {
                month: 'long',
                day: 'numeric'
            }

            if (dates.max === dates.min) {
                return minDate.toLocaleDateString(langTag, dateOptions);
            }

            const maxDate = new Date(dates.max);
            if (minDate.getMonth() === maxDate.getMonth()) {
                const dateString = minDate.toLocaleDateString(langTag, dateOptions);

                return dateString.replace(/\d+/, minDate.getDate() + ' - ' + maxDate.getDate());
            }

            return minDate.toLocaleDateString(langTag, dateOptions) + ' - ' + maxDate.toLocaleDateString(langTag, dateOptions);
        },

        /**
         * Price format
         *
         * @param   price
         *
         * @returns {string}
         */
        _priceFormat: function (price) {
            const moneyConfig = window.Joomla.getOptions('moneyConfig') || {
                'decimal_sep': '.',
                'thousands_sep': ' ',
                'num_decimals': 0,
                'symbol': '₽',
                'format_positive': '%v %s'
            };

            const priceFormat = window.JBZoo.numFormat(price, moneyConfig.num_decimals, moneyConfig.decimal_sep, moneyConfig.thousands_sep);

            return moneyConfig.format_positive
                    .replace('%v', priceFormat)
                    .replace('%s', moneyConfig.symbol);
        }

    });

});