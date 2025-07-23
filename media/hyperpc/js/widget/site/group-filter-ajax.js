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

    JBZoo.widget('HyperPC.GroupFilterAjax', {
        gridClassWFilters   : '',
        gridClassDefault    : '',
        clearAllFiltersText : 'Clear all filters',
        context             : 'groups'
    }, {

        $body         : null,
        $groupFilters : null,
        $itemsWrapper : null,
        currentState  : {},
        stickyFilters : null,

        /**
         * Initialize widget.
         *
         * @param $this
         */
        init: function ($this) {
            $this._onInit($this);
        },

        /**
         * On init widget.
         *
         * @param $this
         * @private
         */
        _onInit: function ($this) {
            $this.$body         = $('body');
            $this.$itemsWrapper = $('.jsGroupItems');
            $this.$groupFilters = $this.$('.hp-group__filters');
            $this.currentState  = $this.el.data('filters') || {};

            const $groupFilters = $this.$groupFilters;
            if (!$groupFilters.is('.uk-modal')) {
                if (!$groupFilters.is('[hidden]')) {
                    $this._initSidebarSticky($this);
                }

                UIkit.util.on($groupFilters, 'beforeshow', function (e) {
                    if ($(e.target).is($this.$groupFilters)) {
                        $this.$itemsWrapper
                            .removeClass($this.getOption('gridClassDefault'))
                            .addClass($this.getOption('gridClassWFilters'));
                    }
                });

                UIkit.util.on($groupFilters, 'show', function (e) {
                    if ($(e.target).is($this.$groupFilters)) {
                        if ($this.stickyFilters === null) {
                            $this._initSidebarSticky($this);
                        } else {
                            $this.stickyFilters.updateSticky();
                        }
                    }
                });

                UIkit.util.on($groupFilters, 'hidden', function (e) {
                    if ($(e.target).is($this.$groupFilters)) {
                        $this.$itemsWrapper
                            .removeClass($this.getOption('gridClassWFilters'))
                            .addClass($this.getOption('gridClassDefault'));
                    }
                });

                UIkit.util.on('#detail', 'shown hidden', function () {
                    $this.stickyFilters && $this.stickyFilters.updateSticky();
                });
            }

            $this.$body.on('click', '.jsClearAllFilters', function (e) {
                if ($(e.target).is('button, a')) {
                    $this._update($this, 'clearAll');
                }
            });
        },

        /**
         * Init sidebar sticky
         *
         * @param $this
         */
         _initSidebarSticky: function ($this) {
            if (window.StickySidebar) {
                const $filterAccordion = $this.$('.jsGroupFiltersSticky').find('[uk-accordion]');
                UIkit.util.on($filterAccordion, 'shown hidden', function () {
                    $this._checkSticky($this);
                });

                const $filtersNav = $this.$('.hp-group__filters-nav'),
                      filtersNavTop = Number.parseInt($filtersNav.css('top')) || 51,
                      filtersNavHeight = $filtersNav.outerHeight(),
                      topSpacing = filtersNavTop + filtersNavHeight + 20;

                $this.stickyFilters = $this.$('.jsGroupFiltersSticky').stickySidebar({
                    topSpacing: topSpacing,
                    bottomSpacing: 20
                }).data('stickySidebar');

                $this._checkSticky($this);
            }
        },

        /**
         * Check sticky
         *
         * @param $this
         */
        _checkSticky: function ($this) {
            const $sticky = $this.$('.jsGroupFiltersSticky');

            if ($this.stickyFilters && $sticky.length) {
                const stickyHeight = $sticky.find('[uk-accordion]').height(),
                      groupHeight  = $this.$('.jsGroupItems').height();

                if (groupHeight <= stickyHeight) {
                    $sticky
                        .find('.inner-wrapper-sticky')
                        .addClass('tm-position-static')
                        .css('transform', 'translate3d(0px, 0px, 0px)');
                } else {
                    $sticky.find('.inner-wrapper-sticky').removeClass('tm-position-static');
                }

                $this.stickyFilters.updateSticky();
            }
        },

        /**
         * Update group
         *
         * @param $this
         * @param {string} task
         * @param {string} property
         * @param {string} value
         */
        _update: function ($this, task, property, value) {
            switch (task) {
                case 'set':
                    $this._addValue($this, property, value);
                    break;
                case 'setSingle':
                    $this._addSingleValue($this, property, value);
                    break;
                case 'unset':
                    $this._removeValue($this, property, value);
                    break;
                case 'unsetSingle':
                    $this._removeSingleValue($this, property, value);
                    break;
                case 'clearAll':
                    $this._clearAll($this);
                    break;
            }

            $this._ajaxFilterParts($this);
        },

        /**
         * Add single value.
         *
         * @param $this
         * @param property
         * @param value
         * @private
         */
        _addSingleValue: function ($this, property, value) {
            $this.currentState[property] = value;
            $this.$body.find('.hp-group-filter').filter('[data-filter="' + property + '"]')
                .find('.jsFilterMark').html('&bull;');
        },

        /**
         * Remove single value.
         *
         * @param $this
         * @param property
         * @param value
         * @private
         */
        _removeSingleValue: function ($this, property, value) {
            delete $this.currentState[property];
            $this.$body.find('.hp-group-filter').filter('[data-filter="' + property + '"]')
                .find('.jsFilterMark').html('');
        },

        /**
         * Add value
         *
         * @param $this
         * @param {string} property
         * @param {string} value
         */
        _addValue: function ($this, property, value) {
            const values = $this.currentState[property] || [];
            if (values.indexOf(value) === -1) {
                values.push(value);
            }

            $this.currentState[property] = values;
            $this.$body.find('.hp-group-filter').filter('[data-filter="' + property + '"]')
                .find('.jsFilterMark').html('&bull;');
        },

        /**
         * Remove value
         *
         * @param $this
         * @param {string} property
         * @param {string} value
         */
        _removeValue: function ($this, property, value) {
            const values = $this.currentState[property] || [],
                  index = values.indexOf(value);
            if (index !== -1) {
                values.splice(index, 1);
                $this.currentState[property] = values;
            }

            if ($this.currentState[property].length === 0) {
                $this.$body.find('.hp-group-filter').filter('[data-filter="' + property + '"]')
                    .find('.jsFilterMark').html('');
            }
        },

        /**
         * Clear all filters
         *
         * @param $this
         */
        _clearAll: function ($this) {
            $this.currentState = {};
            $this.$body.find('.jsFilterButton').find('input').prop('checked', false);
            $this.$body.find('.jsFilterMark').html('');
        },

        /**
         * Check clear all button
         *
         * @param $this
         */
        _checkClearAllButton: function ($this) {
            const $button = $('.jsClearAllFilters');
            if ($this._areFiltersEmpty($this)) {
                $button.attr('hidden', 'hidden');
            } else {
                $button.removeAttr('hidden');
            }
        },

        /**
         * Are filters empty
         *
         * @param $this
         */
        _areFiltersEmpty: function ($this) {
            for (const property in $this.currentState) {
                if ($this.currentState[property].length > 0) {
                    return false
                }
            }

            return true;
        },

        /**
         * Get active filters count
         *
         * @param $this
         */
        _getActiveFiltersCount: function ($this) {
            let count = 0;
            for (const property in $this.currentState) {
                if ($this.currentState[property].length > 0) {
                    count++;
                }
            }

            return count;
        },

        /**
         * Set active filters count
         *
         * @param $this
         */
        _setActiveFiltersCount: function ($this) {
            const count    = $this._getActiveFiltersCount($this),
                  $countEl = $this.$('.jsActiveFiltersCount');
            $countEl.text('(' + count + ')');
            if (count > 0) {
                $countEl.removeAttr('hidden');
            } else {
                $countEl.attr('hidden', 'hidden');
            }
        },

        /**
         * Get ajax params for send.
         *
         * @param $this
         * @returns {{method: string, data: {task: string, filters: *}, dataType: string, url: *}}
         * @private
         */
        _getAjaxParams: function ($this) {
            const data = {};
            for (let key in $this.currentState) {
                if (Object.prototype.hasOwnProperty.call($this.currentState, key)) {
                    const values = $this.currentState[key];
                    if (values.length) {
                        data[key] = values.join('|'); /** @todo process ranges by different way */
                    }
                }
            }

            return {
                'url'      : document.location.pathname + '?task=' + $this.getOption('context') + '.filter-parts',
                'dataType' : 'json',
                'method'   : 'GET',
                'data'     : data
            };
        },

        /**
         * Ajax filter parts
         *
         * @param $this
         */
        _ajaxFilterParts: function ($this) {
            $this._blockInterface($this);

            $.ajax($this._getAjaxParams($this))
            .done(function(response) {
                if (response.result) {
                    $this._onSuccessAjaxResponseResult($this, response);
                } else {
                    // error
                    const html = '<div class="uk-width-1-1">' +
                                     '<div class="uk-text-center">' +
                                         '<div class="uk-text-large">' + response.message + '</div>' +
                                         '<div class="uk-margin">' + 
                                             '<button class="jsClearAllFilters uk-button uk-button-default" type="button">' +
                                                 $this.getOption('clearAllFiltersText') +
                                             '</button>' +
                                         '</div>' +
                                     '</div>' +
                                 '</div>';

                    $this.$itemsWrapper.html(html);
                }

                const resultsCount = typeof response.resultsCount !== 'undefined' ? response.resultsCount : $this.$itemsWrapper.children().length;
                $this._setResultsCount($this, resultsCount);
            })
            .fail(function($xhr, textStatus, errorThrown) {
                const msg = $xhr.status ? $xhr.statusText : 'Connection error';
                UIkit.notification(msg, 'danger');
                // TODO set prev state
            })
            .always(function() {
                $this._checkSticky($this);
                $this._setActiveFiltersCount($this);
                $this._checkClearAllButton($this);
                $this._unblockInterface($this);
            });
        },

        /**
         * On success ajax response result.
         *
         * @param $this
         * @param response
         * @private
         */
        _onSuccessAjaxResponseResult: function ($this, response) {
            $this.$itemsWrapper.html(response.html);
            $this._actualizeFilterButtons($this, response.filters.available);
            history.replaceState(null, null, response.filters.url);
        },

        /**
         * Set results count
         *
         * @param $this
         * @param {number} resultsCount
         */
        _setResultsCount: function ($this, resultsCount) {
            const text    = resultsCount > 0 ? '(' + resultsCount  + ')' : '',
                  $el     = $this.$body.find('.jsFiltersResultCount'),
                  $button = $el.closest('.uk-button');
            $this.$body.find('.jsFiltersResultCount').text(text);
            if (resultsCount > 0) {
                $button.removeAttr('disabled')
                       .removeClass('uk-disabled');
            } else {
                $button.attr('disabled', 'disabled')
                       .addClass('uk-disabled');
            }
        },

        /**
         * Blocking filters and items wrapper
         *
         * @param $this
         */
        _blockInterface: function ($this) {
            if ($this.$groupFilters.is('.uk-modal')) {
                $this.$groupFilters.append(
                    '<div class="jsFiltersModalLoader uk-position-fixed uk-position-cover uk-overlay uk-overlay-default uk-flex uk-flex-center uk-flex-middle">' +
                        '<span uk-spinner class="uk-icon uk-spinner"></span>' +
                    '</div>'
                );
            }

            $this._openLoader();
        },

        /**
         * Unblocking filters and items wrapper
         *
         * @param $this
         */
        _unblockInterface: function ($this) {
            if ($this.$groupFilters.is('.uk-modal')) {
                $this.$body.find('.jsFiltersModalLoader').remove();

                UIkit.util.once($this.$groupFilters, 'hidden', () => {
                    UIkit.scroll('', {offset:70}).scrollTo('.jsGroupItemsWrapper');
                });
            }

            $this._hideLoader();
        },

        /**
         * Actuelize filter buttons
         *
         * It should named as _actualizeFilters
         *
         * @param $this
         * @param {object} availableFilters
         */
        _actualizeFilterButtons: function ($this, availableFilters) {
            const $filters = $this.$body.find('.hp-group-filter');

            availableFilters.forEach(filter => {
                const $filter = $filters.filter('[data-filter="' + filter.key + '"]');

                if (filter.type === 'checkboxes') {
                    filter.options.forEach(option => {
                        const $input = $filter.find('input[type=checkbox][value="' + option.value + '"]'),
                              $wrapper = $input.closest('.jsFilterButton'),
                              $count = $wrapper.find('.jsFilterButtonCount');

                        if (option.count > 0) {
                            $count.text(option.count);
                            $wrapper.removeClass('uk-disabled');
                        } else {
                            $count.text('');
                            if (!$wrapper.hasClass('uk-active')) {
                                $wrapper.addClass('uk-disabled');
                            }
                        }
                    });
                } else if (filter.type === 'range') {
                    $filter.find('.jsRangeFrom').val(filter.options.minValue);
                    $filter.find('.jsRangeTo').val(filter.options.maxValue);
                }
            });
        },

        /**
         * On keyup range input.
         *
         * @param e
         * @param $this
         */
        'keyup .jsRange input': function (e, $this) {
            const $el = $(this);
            let value = $el.val();
            value = value.replace(/[^0-9]/g, '');

            if (Number(value) === 0) {
                value = '';
            }

            $el.val(value);
        },

        /**
         * On change filter option.
         *
         * @param e
         * @param $this
         */
        'change {document} .jsFilterButton input': function (e, $this) {
            const $input = $(this),
                  $option = $input.closest('.jsFilterButton'),
                  task = $input.is(':checked') ? 'set' : 'unset',
                  value = $input.val(),
                  property = $input.closest('.hp-group-filter').data('filter');

            if (task === 'set') {
                $option.addClass('uk-active');
            } else if (task === 'unset') {
                $option.removeClass('uk-active');
                $input.trigger('blur');
            }

            $this._update($this, task, property, value);
        },

        /**
         * On click "show results" button.
         *
         * @param e
         * @param $this
         */
         'click {document} .jsCloseFiltersModal': function (e, $this) {
            const $button = $(this),
                  $modal = $button.closest('.uk-modal');

            UIkit.modal($modal).hide();
        },

        /**
         * On change range filter option.
         *
         * @param e
         * @param $this
         */
        'change {document} .jsRange input': function (e, $this) {
            const $input = $(this),
                  property = $input.closest('.hp-group-filter').data('filter');

            let task = 'setSingle';

            const $wrapper = $input.closest('.jsRange'),
                  $from = $wrapper.find('input.jsRangeFrom'),
                  $to = $wrapper.find('input.jsRangeTo'),
                  fromValue = $from.val() || 0,
                  toValue = $to.val() || 0,
                  value = fromValue + ':' + toValue;

            if (value === '0:0') {
                task = 'unsetSingle';
            }

            $this._update($this, task, property, [value]);
        },
    });
});
