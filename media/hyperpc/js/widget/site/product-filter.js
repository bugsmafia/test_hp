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
 * @author     Sergey Kalistratov   <kalistratov.s.m@gmail.ru>
 * @author     Artem Vyshnevskiy
 */

jQuery(function ($) {

    JBZoo.widget('HyperPC.GroupFilterAjax.SiteProductFilter', {
        'fieldControlName'    : null,
        'uriPath'             : null,
        'token'               : null
    }, {

        /**
         * Initialize widget.
         *
         * @param $this
         */
        init : function ($this) {
            $this._onInit($this);

            $this._setPriceRangePlaceholders($this);
        },

        /**
         * Get ajax params for send.
         *
         * @param $this
         * @returns {{method: string, data: {task: string, filters: *}, dataType: string, url: *}}
         */
        _getAjaxParams : function ($this) {
            return {
                'url'      : '/index.php',
                'dataType' : 'json',
                'method'   : 'POST',
                'data'     : $.extend(true, {}, $this.el.data('ajax'), {
                    'filters' : $this.currentState
                }),
                'timeout'   : 15000,
                'headers'   : {
                    'X-CSRF-Token' : $this.getOption('token')
                }
            };
        },

        /**
         * On success ajax response result.
         *
         * @param $this
         * @param response
         */
        _onSuccessAjaxResponseResult : function ($this, response) {
            $this.$itemsWrapper.html(response.html);

            if (typeof $().raty === 'function') {
                $this.$itemsWrapper.find('.jsRatingStars').raty({
                    starType : 'i',
                    readOnly : true
                });
            }

            $this._actualizeFilterButtons($this, response.filters.available);
            $this._setPriceRangePlaceholders($this);

            if (response.dbQuery) {
                $this.$('.jsFilterQueryDump').html(response.dbQuery);
            }

            let newHistoryState = $this.getOption('uriPath');
            if (response.url !== '') {
                newHistoryState += '?' + response.url;
            }

            history.replaceState(null, null, newHistoryState);

            document.dispatchEvent(new CustomEvent('hpproductsupdated'));
        },

        /**
         * Actuelize filter buttons
         *
         * @param $this
         * @param {object} availableFilters
         */
        _actualizeFilterButtons: function ($this, availableFilters) {
            $this.$body.find('.hp-group-filter').each(function() {
                const $groupFilter = $(this),
                      property = $groupFilter.data('filter');

                const availableButtons = availableFilters[property];
                $groupFilter.find('.jsFilterButton').each(function () {
                    const $filterButton = $(this),
                          propData = availableButtons[$filterButton.find('input').val()] || {count: 0},
                          count = propData.count || 0;

                    if (count > 0) {
                        $filterButton.removeClass('uk-disabled')
                                     .find('.jsFilterButtonCount').text(count);
                    } else {
                        if (!$filterButton.hasClass('uk-active')) {
                            $filterButton.addClass('uk-disabled');
                        }
                        $filterButton.find('.jsFilterButtonCount').text('');
                    }
                });

            });
        },

        /**
         * Clear all filters
         *
         * @param $this
         */
        _clearAll : function ($this) {
            $this.currentState = {};
            $this.$body.find('.jsFilterButton').find('input').prop('checked', false);
            $this.$body.find('.jsFilterStore').prop('selectedIndex', 0);
            $this.$body.find('.jsPriceRange input').val('').removeAttr('value');
            $this.$body.find('.jsFilterMark').html('');
        },

        /**
         * Set min and max price as placeholders in the price range inputs
         *
         * @param $this
         */
        _setPriceRangePlaceholders : function ($this) {
            const $schemas = $this.$itemsWrapper.find('[type="application/ld+json"]');
            const prices = $schemas.map(function (i, item) {
                const obj = JSON.parse(item.innerText);
                return obj.offers[0].price;
            }).toArray();

            const minPrice = Math.min(...prices),
                  maxPrice = Math.max(...prices);

            const $priceRangeWrapper = $this.$body.find('.jsPriceRange');
            $priceRangeWrapper.find('input.jsPriceFrom').attr('placeholder', minPrice === Infinity ? '' : minPrice);
            $priceRangeWrapper.find('input.jsPriceTo').attr('placeholder', maxPrice === Infinity ? '' : maxPrice);
        },

        /**
         * On change filter option.
         *
         * @param e
         * @param $this
         */
        'change {document} .jsFilterStore' : function (e, $this) {
            const $input   = $(this),
                  $option  = $input.closest('.jsFilterButton'),
                  task     = ($input.val() !== '') ? 'setSingle' : 'unsetSingle',
                  value    = $input.val(),
                  property = $input.closest('.hp-group-filter').data('filter');

            if (task === 'setSingle') {
                $option.addClass('uk-active');
            } else if (task === 'unsetSingle') {
                $option.removeClass('uk-active');
                $input.trigger('blur');
            }

            $this._update($this, task, property, value);
        },

        /**
         * On keyup.
         *
         * @param $this
         */
        'keyup .jsPriceRange input' : function ($this) {
            let value = $(this).val();
            value = value.replace(/[^0-9]/g, '');
            $(this).val(value);

            if (value === '0' || value === 0) {
                $(this).val('');
            }
        },

        /**
         * On change filter option.
         *
         * @param e
         * @param $this
         */
        'change {document} .jsPriceRange input' : function (e, $this) {
            const $input  = $(this),
                property  = $input.closest('.hp-group-filter').data('filter');

            let value = ':',
                task  = 'setSingle';

            //  Setup price range concat (:) value.
            const $priceRangeWrapper = $input.closest('.jsPriceRange');
            if ($priceRangeWrapper.length) {
                const $priceFrom = $priceRangeWrapper.find('input.jsPriceFrom'),
                      $priceTo   = $priceRangeWrapper.find('input.jsPriceTo');

                const priceFromVal = ($priceFrom.val() !== '') ? $priceFrom.val() : 0,
                      priceToVal   = ($priceTo.val() !== '') ? $priceTo.val() : 0;

                value = priceFromVal + ':' + priceToVal;
            }

            if (value === ':' || value === '0:0') {
                task = 'unsetSingle';
            }

            $this._update($this, task, property, value);
        },
    });

});
