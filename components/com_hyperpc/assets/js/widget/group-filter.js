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

    JBZoo.widget('HyperPC.GroupFilter', {
        'filtersLiteMode' : true
    }, {

        /**
         * Initialize widget.
         *
         * @param $this
         */
        init : function ($this) {
            const $group = $this.el;
            if (typeof $group.data('filters') !== 'undefined') {
                const groupFilters = $group.data('filters');

                $this._actualizeFilterButtons($this);

                for (let prop in groupFilters) {
                    if (typeof groupFilters[prop] === 'object' && groupFilters[prop][0] !== '') {
                        const $filter = $group.find('.hp-group-filter[data-filter=' + prop + ']');

                        $filter.find('.jsFilterButton[data-value=' + groupFilters[prop][0] + ']').trigger('click');
                    }
                }
            }
        },

        /**
         * Remove excess filter buttons
         * 
         * @param $this
         */
        _actualizeFilterButtons : function ($this) {
            const actualFilters = $this._getActualFilters($this);
            $this.$('.hp-group-filter').each(function() {
                const $groupFilter = $(this),
                      property = $groupFilter.data('filter');

                if (!(property in actualFilters)){
                    $groupFilter.remove();
                    return;
                }

                $groupFilter.find('.jsFilterButton').each(function(){
                    const $filterButton = $(this),
                          value = $filterButton.data('value').toString();

                    if (actualFilters[property].indexOf(value) == -1) {
                        $filterButton.remove();
                    }
                });
            });
        },

        /**
         * Get actual filters
         * 
         * @param $this
         * 
         * @returns {Object}
         */
        _getActualFilters : function ($this) {
            const actualFilters = {},
                  $groupParts   = $this._getGroupParts($this);

            $groupParts.each(function() {
                const partFilters = $(this).data('field-value');

                for (let key in partFilters) {
                    if (typeof actualFilters[key] === 'undefined') {
                        actualFilters[key] = [];
                    }

                    if (actualFilters[key].indexOf(partFilters[key]) == -1) {
                        actualFilters[key].push(partFilters[key]);
                    }
                }
            });

            return actualFilters;
        },

        /**
         * Get group parts.
         *
         * @param $this
         * 
         * @returns {(jQuery object)}
         */
        _getGroupParts : function ($this) {
            return $this.$('.tm-part-teaser:not([hidden])');
        },

        /**
         * Set filter data to group.
         *
         * @param $this
         * @param {string} property
         * @param {string} value
         * @param {string} task
         */
        _setGroupFilter : function($this, property, value, task) {
            const groupFilters = $this.el.data('filters');

            if (typeof groupFilters[property] !== 'object') {
                groupFilters[property] = [];
            }

            if (task === 'set') {
                if ($this.getOption('filtersLiteMode')) {
                    groupFilters[property] = [];
                    groupFilters[property].push(value);
                } else {
                    if (groupFilters[property].indexOf(value) == -1) {
                        groupFilters[property].push(value);
                    }
                }
            } else if (task === 'unset') {
                const index = groupFilters[property].indexOf(value);
                groupFilters[property].splice(index, 1);
            }

            $this.el.data('filters', groupFilters);

            if ($this.getOption('filtersLiteMode')) {
                $this._filterParts($this, property, task);
            } else {
                $this._ajaxFilterParts($this, groupFilters);
            }
        },

        /**
         * Show/hide filtered parts.
         *
         * @param $this
         * @param {string} property
         * @param {string} task
         */
        _filterParts : function($this, property, task) {
            const groupFilters = $this.el.data('filters'),
                  $parts = $this._getGroupParts($this),
                  availableProp = {};

            let filterIsEmpty = true;

            for (let filter in groupFilters) {
                if (groupFilters[filter] && groupFilters[filter].length > 0 ) {
                    filterIsEmpty = false;
                    break;
                }
            }

            if (filterIsEmpty) {
                $parts.removeClass('uk-hidden');
                $this.$('.jsFilterButton').removeClass('uk-disabled');
                UIkit.margin($this.$('.hp-group-items')).$emit(event = 'update');
                UIkit.heightMatch($this.$('.hp-group-items')).$emit(event = 'update');

                return false;
            }

            $parts.each(function() {
                const $part          = $(this),
                      partProperties = $part.data('field-value');

                let partVisible;

                for (let prop in partProperties) {
                    const propValue = partProperties[prop].toString();

                    if (typeof groupFilters[prop] === 'object' && groupFilters[prop].length > 0) {
                        if (groupFilters[prop].indexOf(propValue) != -1 && partVisible !== false) {
                            partVisible = true;
                        } else {
                            partVisible = false;
                        }
                    }
                }

                if (partVisible === true) {
                    $part.removeClass('uk-hidden');

                    for (let prop in partProperties) {
                        if (prop === property && task === 'set') continue;
                        if (typeof availableProp[prop] === 'undefined') availableProp[prop] = [];

                        if (availableProp[prop].indexOf(partProperties[prop]) == -1) {
                            availableProp[prop].push(partProperties[prop]);
                        }
                    }
                } else if (partVisible === false) {
                    $part.addClass('uk-hidden');
                }
            });

            if (typeof availableProp[property] !== 'undefined') {
                if (task === 'unset' && availableProp[property].length > 0) {
                    delete availableProp[property];
                }
            }

            UIkit.margin($this.$('.hp-group-items')).$emit(type = 'update');
            UIkit.heightMatch($this.$('.hp-group-items')).$emit(type = 'update');
            $this._checkFilters($this, availableProp);
        },

        /**
         * Enable/disable filters button.
         *
         * @param $this
         * @param {Object} availableProp
         */
        _checkFilters : function($this, availableProp) {
            $this.$('.hp-group-filter').each(function(){
                const $filter  = $(this),
                      property = $filter.data('filter');

                if (!(property in availableProp)) return;

                $filter.find('.jsFilterButton').each(function(){
                    const $button = $(this),
                          value = $button.data('value').toString();

                    if (availableProp[property].indexOf(value) == -1) {
                        $button.addClass('uk-disabled');
                    } else {
                        $button.removeClass('uk-disabled');
                    }
                });
            });
        },

        /**
         * On click filter button.
         *
         * @param e
         * @param $this
         */
        'click .jsFilterButton' : function (e, $this) {
            const $button  = $(this),
                  task     = $button.hasClass('uk-active') ? 'unset' : 'set',
                  property = $button.closest('.hp-group-filter').data('filter'),
                  value    = $button.data('value').toString();

            if (task === 'set') {
                $button.addClass('uk-active');
                if ($this.getOption('filtersLiteMode')) {
                    $button.siblings().removeClass('uk-active');
                }
            } else if (task === 'unset') {
                $button.removeClass('uk-active');
            }

            $this._setGroupFilter($this, property, value, task);
        }

    });
});
