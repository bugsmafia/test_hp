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

    JBZoo.widget('HyperPC.CartModuleCompare', {}, {

        isConfigurator: false,

        /**
         * Initialize widget.
         *
         * @param $this
         */
        init: function ($this) {
            $this.isConfigurator = $('body').hasClass('tmpl-configurator');

            $(window).on('storage', function (e) {
                if (e.key === 'hp_compared_items_count') {
                    $this._setComparedItemsCount($this);
                }
            });

            document.addEventListener('hpcompareupdated', (e) => {
                $this._onCompareUpdated($this, e.detail);
            });
        },

        /**
         * On compare list updated.
         *
         * @param $this
         * @param data
         */
        _onCompareUpdated: function ($this, data) {
            if (typeof data !== 'undefined' && typeof data.totalCount !== 'undefined') {
                $this._setComparedItemsCount($this, data.totalCount);
            }
        },

        /**
         * Set compared items count.
         *
         * @param $this
         * @param {number} [itemsCount]
         */
        _setComparedItemsCount: function ($this, itemsCount) {
            const count = itemsCount || localStorage.getItem('hp_compared_items_count'),
                  hasItems = count > 0,
                  $badge = $this.$('.jsCartModuleCompareBadge').html(count);

            if (hasItems) {
                $badge.removeAttr('hidden');
            } else {
                $badge.attr('hidden', 'hidden');
            }

            if ($this.isConfigurator) {
                if (hasItems) {
                    $this.el.removeAttr('hidden');
                } else {
                    $this.el.attr('hidden', 'hidden');
                }
            }
        }
    });
});
