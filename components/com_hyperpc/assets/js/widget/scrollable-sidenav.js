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

    JBZoo.widget('HyperPC.ScrollableSidenav', {}, {

        eventTimer     : 0,
        itemHeight     : 30,
        currentOffset  : 0,
        touch          : '',
        touchDetecting : false,
        swipeStarted   : false,
        delta          : 0,

        /**
         * Initialize widget.
         *
         * @param $this
         */
        init : function ($this) {

            var resizeTimer;
            $(window).on('resize', function(e) {
                clearTimeout(resizeTimer);

                resizeTimer = setTimeout(function() {
                    $this._update($this);
                }, 250);

            });

            $(window).on('load', function(){
                $this._update($this);
            });

        },

         /**
         * Update element offset.
         *
         * @param $this
         * @private
         */
        _update: function ($this) {
            if (!$this._isOverflowed($this)) {
                this.el.css('transform', 'translateX(0px)');
                return;
            }

            var activeItem      = $this._getActive($this);
            var wrapperHeight   = $this._getWrapperHeight($this);
            var offsetTop       = activeItem['0'].offsetTop;

            $this.currentOffset = $this._calculateOffset($this, wrapperHeight, offsetTop);

            this.el.css('transform', 'translateY(' + (-$this.currentOffset) + 'px)');

        },

        /**
         * Calculate element offset.
         *
         * @param $this
         * @param {number} wrapperHeight height of the wrapper
         * @param {number} itemOffset offset of the active item
         * @returns {?number}
         * @private
         */
        _calculateOffset: function ($this, wrapperHeight, itemOffset) {
            var targetItemOffset = (wrapperHeight - $this.itemHeight) / 2;
            var maxOffset = $this._getMaxOffset($this);

            var offset = Math.round(itemOffset - targetItemOffset);

            if (offset > 0) {
                if (offset >= maxOffset) {
                    return maxOffset;
                } else {
                    return offset;
                }
            } else {
                return 0;
            }

        },

        /**
         * Check element overflowing.
         *
         * @param $this
         * @returns {boolean}
         * @private
         */
        _isOverflowed: function ($this) {

            if ($this._getElHeight($this) > $this._getWrapperHeight($this)) {
                return true;
            } else {
                return false;
            }
        },

        /**
         * Get active item.
         *
         * @param $this
         * @returns {Object} jQuery element
         * @private
         */
        _getActive: function ($this) {
            var activeItem = $this.el.find('li.uk-active');

            return activeItem;
        },

        /**
         * Get wrapper height.
         *
         * @param $this
         * @returns {number}
         * @private
         */
        _getWrapperHeight: function ($this) {
            return $this.el.parent().height();
        },

        /**
         * Get max offset.
         *
         * @param $this
         * @returns {number}
         * @private
         */
        _getMaxOffset: function ($this) {
            return $this._getElHeight($this) - $this._getWrapperHeight($this);
        },

        /**
         * Get element height.
         *
         * @param $this
         * @returns {number}
         * @private
         */
        _getElHeight: function ($this) {
            return $this.el.outerHeight();
        },

        /**
         * Scrollspy nav event.
         *
         * @param e
         * @param $this
         */
        'active {element}': function (e, $this) {

            clearTimeout($this.eventTimer);

            $this.eventTimer = setTimeout(function() {
                $this._update($this);
            }, 250);

        },

        /**
         * Touchstart on the element.
         *
         * @param e
         * @param $this
         */
        'touchstart {element}': function(e, $this) {
            if (!$this._isOverflowed($this) || e.originalEvent.touches.length != 1){
                return;
            }

            $this.touch = e.originalEvent.changedTouches[0];
            $this.touchDetecting = true;

        },

        /**
         * Touchmove on the element.
         *
         * @param e
         * @param $this
         */
        'touchmove {element}': function(e, $this) {
            if (!$this.swipeStarted && !$this.touchDetecting){
                return;
            }

            if ( !$.inArray($this.touch, e.originalEvent.changedTouches)) {
                return;
            }

            var x    = $this.touch.clientX;
            var y    = $this.touch.clientY;
            var newX = e.originalEvent.changedTouches[0].clientX;
            var newY = e.originalEvent.changedTouches[0].clientY;

            if ($this.touchDetecting){
                if ( Math.abs(y - newY) >= Math.abs(x - newX) ) {
                    e.preventDefault();
                    $this.swipeStarted = true;
                }

                $this.touchDetecting = false;
            }

            if ($this.swipeStarted) {
                e.preventDefault();
                var maxOffset = $this._getMaxOffset($this);
                var diff = (y - newY) - $this.delta;
                if ($this.currentOffset + $this.delta <= 0 || ($this.currentOffset + $this.delta) >= maxOffset) {
                    $this.delta += diff / 100;
                } else {
                    $this.delta = y - newY;
                }

            }

            $this.el.css('transition', 'none');
            $this.el.css('transform', 'translateY(' + (-($this.currentOffset + $this.delta)) + 'px)');

        },

        /**
         * Touchend on the element.
         *
         * @param e
         * @param $this
         */
        'touchend {element}': function(e, $this) {
            $this.el.css('transition', '');

            if (!$this.swipeStarted){
                return;
            }

            var maxOffset = $this._getMaxOffset($this);

            if ($this.currentOffset + $this.delta <= 0) {
                $this.currentOffset = 0;
            } else if (($this.currentOffset + $this.delta) > maxOffset) {
                $this.currentOffset = maxOffset;
            } else {
                $this.currentOffset += $this.delta;
            }

            $this.el.css('transform', 'translateY(' + (-$this.currentOffset) + 'px)');
            $this.delta = 0;
            $this.swipeStarted = false;
            $this.touchDetecting = false;

            e.preventDefault();
            return false;

        }

    });
});