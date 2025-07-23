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

    JBZoo.widget('HyperPC.ConfiguratorGroupNav', {}, {

        pause: false,
        activeDelay: false,

        /**
         * Initialize widget.
         *
         * @param $this
         */
        init: function($this) {
            $(window).on('scroll', function(e) {
                $this.pause = false;
            });
        },

        /**
         * Handle active event on the element.
         *
         * @param e
         * @param $this
         */
        'active {element}': function(e, $this) {
            if ($this.pause) return;

            var $firstLevelActive = $this.$('li.uk-active').closest('li.uk-parent');

            if ($firstLevelActive.is(':not(.tm-active)')) {
                $firstLevelActive
                    .addClass('tm-active')
                    .siblings()
                    .removeClass('tm-active');
            }

            if (!$this.activeDelay && $firstLevelActive.is(':not(.uk-open)')) {
                UIkit.nav($this.el[0]).toggle($firstLevelActive.index(), false);
            }
        },

        /**
         * Handle click on a first level item.
         *
         * @param e
         * @param $this
         */
        'click li.uk-parent:not(.tm-active)': function(e, $this) {
            $this.pause = true;
        },

        /**
         * Handle click on a second level item.
         *
         * @param e
         * @param $this
         */
        'click .uk-nav-sub > li': function(e, $this) {
            $this.activeDelay = true;

            setTimeout(function() {
                $this.activeDelay  = false;
            }, 1000);
        },

    });

});