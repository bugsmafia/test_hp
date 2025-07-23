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

    JBZoo.widget('HyperPC.ConfiguratorStickyBottom', {}, {

        /**
         * Initialize widget.
         *
         * @param $this
         */
        init: function ($this) {
            $this.$body = $('body');
            $this.$stickyNavigation = $this.$('.hp-step-configurator__sticky');
            $this.$stickyNavigationPlaceholder = $this.$stickyNavigation.next('.hp-step-configurator__sticky-placeholder');

            $this._updateSticky($this);

            let resizeTimer;
            $(window)
                .on('scroll', function() {
                    $this._updateSticky($this);
                })
                .on('resize', function(e) {
                    clearTimeout(resizeTimer);
                    resizeTimer = setTimeout(function() {
                        $this._updateSticky($this);
                    }, 250);
                });
        },

        /**
         * Update sticky
         *
         * @param $this
         */
        _updateSticky : function ($this) {
            const componentClientRect = $this.el.get(0).getBoundingClientRect();

            if ((componentClientRect.top + componentClientRect.height) < window.innerHeight) {
                $this.$stickyNavigation.removeClass('hp-step-configurator__sticky--fixed');
                $this.$stickyNavigationPlaceholder.attr('style', '');
                $this.$body.removeClass('has-toolbar-bottom');
            } else {
                const stickyClientRect = $this.$stickyNavigation.get(0).getBoundingClientRect();
                $this.$stickyNavigationPlaceholder.css('height', stickyClientRect.height + 'px');
                $this.$stickyNavigation.addClass('hp-step-configurator__sticky--fixed');
                $this.$body.addClass('has-toolbar-bottom');
            }
        },

    });
});