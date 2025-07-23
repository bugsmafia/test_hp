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

    JBZoo.widget('HyperPC.SiteCompare.Products', {}, {

        /**
         * Initialize widget.
         *
         * @param $this
         */
        init : function ($this) {
            $this.constructor.parent.init($this);

            $this._updateTableWidth($this);

            let resizeTimer;
            $(window).on('resize', function(e) {
                clearTimeout(resizeTimer);

                resizeTimer = setTimeout(function() {
                    $this._updateTableWidth($this);
                }, 250);

            });

            document.addEventListener('hpcompareupdated', (e) => {
                const data = e.detail;
                if (data.task && data.task === 'remove') {
                    $this.$('.jsScrollable').trigger('hpcontentupdated');
                } else {
                    $this._updateComparedItems($this, data.html, data.groupKey);
                }
            });
        },

        /**
         * Reload compared items
         *
         * @param $this
         * @param html
         * @param groupKey
         */
        _updateComparedItems : function ($this, html, groupKey) {
            if (typeof html !== undefined && typeof groupKey !== 'undefined') {
                const $compareWrapper = $this.$('.jsCompareWrapper');
                $compareWrapper.html(html);

                const $table = $compareWrapper.find('.hp-table-compare');
                if ($table.hasClass('jsScrollable') && window.JBZoo.isWidgetExists('HyperPCScrollable')) {
                    $table.HyperPCScrollable({});
                    $this._updateTableWidth($this);
                    $table.trigger('hpcontentupdated');
                }
            } else {
                document.location.reload();
            }
        },

        /**
         * Update table width
         * 
         * @param $this
         */
        _updateTableWidth : function ($this, $table) {
            $table = $table || $this.$('.hp-table-compare');
            if (!$table.is('.jsScrollable')) {
                $this.constructor.parent._updateTableWidth($this, $table);
                return;
            }

            const $columns = $table.find('tr').first().children().not('[hidden]');

            let minWidth = 0;
            $columns.each(function() {
                minWidth += $(this).outerWidth();
            });

            minWidth = Math.max(minWidth, $table.parent().width());

            $table.css('minWidth', minWidth + 'px');
        },

        /**
         * Handle remove last item from compare
         * 
         * @param $this
         */
         _handleRemoveLast : function ($this) {
             const $table = $this.$('.hp-table-compare');
             $table.find('tr').not('.hp-table-compare__head').remove();
         },

         /**
          * On click category in compare offcanvas bar
          * 
          * @param e
          * @param $this
          */
         'click {document} .jsCompareSidebarCategory' : function(e, $this) {
             const $content = $(this).find('.jsCompareProductsList').clone();
             $('.jsCompareSidebarStep2Content').html($content.removeAttr('hidden'));
         }

    });
});
