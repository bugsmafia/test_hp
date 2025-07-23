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

    JBZoo.widget('HyperPC.StickyBottom', {}, {

        fixedBottom : false,

        /**
         * Initialize widget.
         *
         * @param $this
         */
        init : function ($this) {
            $this.el.after( "<div class='uk-sticky-placeholder' hidden></div>" )
                    .next()
                    .css('margin', 0)
                    .css('height', $this.el.height());

            var resizeTimer;
            $(window).on('resize', function(e) {
                clearTimeout(resizeTimer);

                resizeTimer = setTimeout(function() {
                    $this._update($this);
                }, 250);

            });

            $("[id$=-configuration]").on('shown hidden', function() {
                $this._update($this);
            });

            $this._update($this);
        },

        /**
         * Update element offset.
         *
         * @param $this
         * @private
         */
        _update: function ($this) {
            if ($this.el.parent().attr('hidden')) return;

            if($this._isOverflowed($this)) {
                if ($this.fixedBottom) return;

                $this.el.addClass('hp-cart-sticky-bottom')
                        .next()
                        .removeAttr('hidden');
                $this.fixedBottom = true;
            } else {
                if (!$this.fixedBottom) return;

                $this.el.removeClass('hp-cart-sticky-bottom')
                        .next()
                        .attr('hidden', '');
                $this.fixedBottom = false;
            }

            $this.el.next().css('height', $this.el.outerHeight());
        },

        /**
         * Check element overflowing.
         *
         * @param $this
         * @returns {boolean}
         * @private
         */
        _isOverflowed: function ($this) {

            if ($('body').height() > $(window).height()) {
                return true;
            } else {
                return false;
            }
        }

    });
});