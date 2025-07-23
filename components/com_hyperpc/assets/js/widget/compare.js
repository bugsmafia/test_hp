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

    JBZoo.widget('HyperPC.SiteCompare', {
        'emptyMsg' : ''
    }, {

        /**
         * Initialize widget.
         *
         * @param $this
         */
        init : function ($this) {
            $(window).on('storage', function (e) {
                switch (e.key) {
                    case 'hp_compared_items_count':
                        $this._updateComparedItems($this);
                        break;
                }
            });
        },

        /**
         * Reload compared items
         * 
         * @param $this
         *
         * @todo ajax reload and update
         */
        _updateComparedItems : function ($this) {
            document.location.reload();
        },

        /**
         * Show alert with empty message
         * 
         * @param $this
         */
        _showAlertEmpty : function ($this) {
            $this.$('.hp-compare-wrapper').fadeOut(250, function() {
                $(this)
                    .html(
                        '<div class="uk-container uk-container-small">' +
                            '<div class="uk-alert uk-alert-warning">' +
                                $this.getOption('emptyMsg') +
                            '</div>' +
                        '</div>')
                    .fadeIn(250);
            });
        },

        /**
         * Update table width
         *
         * @param $this
         * @param $table
         */
        _updateTableWidth: function ($this, $table) {
            $table = $table || $this.$('.hp-table-compare');
            const $columns = $table.find('tr').first().children();

            $table.css('minWidth', (225 * $columns.length) + 'px')
                  .find('[colspan]').attr('colspan', $columns.length);
        },

        /**
         * Clear all compare items.
         *
         * @param e
         * @param $this
         */
        'click .jsClearCompare' : function (e, $this) {
            $this.ajax({
                'data'     : {
                    'format' : 'raw',
                    'tmpl'   : 'component',
                    'task'   : 'compare.clear'
                },
                'dataType' : 'json',
                'url'      : '/index.php',
                'success'  : function (data) {
                    if (data.result === true) {
                        $this._showAlertEmpty($this);

                        document.dispatchEvent(new CustomEvent('hpcompareupdated', {
                            detail: {
                                task: 'remove',
                                totalCount: 0,
                            }
                        }));
        
                        localStorage.setItem('hp_compared_items_count', 0);
                        localStorage.setItem('hp_compared_items', '{}');
                    }
                }
            });

            $(this).blur();
            e.preventDefault();
        },

        /**
         * Remove item from compare.
         *
         * @param e
         * @param $this
         */
        'click .jsRemoveCompareItem' : function (e, $this) {
            const $item  = $(this),
                  itemId = $item.data('id'),
                  type   = $item.data('type');

            const args = {
                'itemId' : itemId,
                'type'   : type
            };

            $this.ajax({
                'data'     : {
                    'format' : 'raw',
                    'tmpl'   : 'component',
                    'task'   : 'compare.remove',
                    'args'   : args
                },
                'dataType' : 'json',
                'url'      : '/index.php',
                'success'  : function (data) {
                    if (data.result === true) {
                        const $table = $item.closest('table'),
                              count  = $table.data('count') - 1;
                        $table.data('count', count);

                        const $cells = $this.$('[data-part-id="' + itemId + '"]'),
                              $last  = $cells.last();

                        $cells.fadeOut(250, function() {
                            const $cell = $(this);
                            $cell.remove();

                            if ($cell.is($last)) {
                                $this.$('.jsShowCompareSidebar').removeAttr('hidden');
                                $this._updateTableWidth($this, $table);

                                document.dispatchEvent(new CustomEvent('hpcompareupdated', {
                                    detail: {
                                        task: 'remove',
                                        totalCount: data.count
                                    }
                                }));

                                localStorage.setItem('hp_compared_items_count', data.count);
                                localStorage.setItem('hp_compared_items', JSON.stringify(data.items));
                            }
                        });

                        const isSwitcher = $table.closest('ul').hasClass('uk-switcher');
                        if (count === 1) { // last item in table
                            const $equalToggleButtonsSelector = '.jsCompareShowAll, .jsCompareHideEqual';
                            if (isSwitcher) {
                                $table.closest('li').find($equalToggleButtonsSelector).attr('hidden', 'hidden');
                            } else {
                                $this.$($equalToggleButtonsSelector).attr('hidden', 'hidden');
                            }
                            $table.find('.jsEqualValues').removeClass('uk-hidden');
                        } else if (count === 0 && (data.count === count || !isSwitcher)) {
                            $this._handleRemoveLast($this);
                        } else if (count === 0 && data.count !== 0 && isSwitcher) {
                            $table.closest('li').fadeOut(250, function() {
                                $this.$('a[href="#' + $table.data('group') + '"]').parent().remove();
                                $(this).remove();
                            });
                        }
                    }
                }
            });

            e.preventDefault();
        },

        /**
         * Handle remove last item from compare
         *
         * @param $this
         */
        _handleRemoveLast : function($this) {
            $this._showAlertEmpty($this);
        },

        /**
         * Show all properties.
         *
         * @param e
         * @param $this
         */
        'click .jsCompareShowAll' : function (e, $this) {
            e.preventDefault();
            $(this).addClass('uk-active uk-disabled')
                   .siblings('.jsCompareHideEqual').removeClass('uk-active uk-disabled').removeAttr('style');
            $(this).closest('li').find('.jsEqualValues').removeClass('uk-hidden');
        },

        /**
         * Hide equal properties .
         *
         * @param e
         * @param $this
         */
        'click .jsCompareHideEqual' : function (e, $this) {
            e.preventDefault();
            $(this).addClass('uk-active uk-disabled')
                   .siblings('.jsCompareShowAll').removeClass('uk-active uk-disabled').removeAttr('style');
            $(this).closest('li').find('.jsEqualValues').addClass('uk-hidden');
        }
    });
});
