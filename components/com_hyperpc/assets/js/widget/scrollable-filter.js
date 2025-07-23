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

    JBZoo.widget('HyperPC.ScrollableFilter', {}, {

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
            let resizeTimer;
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
        _update: function ($this, $activeItem) {
            if (!$this._isOverflowed($this)) {
                $this.el.css('transform', 'translateX(0px)');
                return;
            }

            if (typeof $activeItem === 'undefined') {
                $activeItem = $this._getActive($this);
            }

            if (!$activeItem.length) {
                $this.el.css('transform', 'translateX(0px)');
                return;
            }

            const elWidth         = $this.el.width(),
                  parentWidth     = $this._getParentWidth($this),
                  activeItemWidth = $activeItem.innerWidth(),
                  offsetLeft      = $activeItem['0'].offsetLeft;

            $this.currentOffset = $this._calculateOffset(elWidth, parentWidth, activeItemWidth, offsetLeft);

            $this.el.css('transform', 'translateX(' + (-$this.currentOffset) + 'px)');
        },

        /**
        * Calculate element offset.
        *
        * @param {number} elWidth width of the element
        * @param {number} parentWidth width of element's parent
        * @param {number} itemWidth width of the active item
        * @param {number} itemOffset offset of the active item
        * @returns {number}
        * @private
        */
       _calculateOffset: function (elWidth, parentWidth, itemWidth, itemOffset) {
            const targetItemOffset = (parentWidth - itemWidth) / 2,
                  offset = Math.round(itemOffset - targetItemOffset);

            if (offset > 0) {
                const maxOffset = elWidth - parentWidth;
                return Math.min(offset, maxOffset);
            }

            return 0;
       },

        /**
         * Check element overflowing.
         *
         * @param $this
         * @returns {boolean}
         * @private
         */
        _isOverflowed: function ($this) {
            const navWidth = $this.el.width();

            return navWidth > $this._getParentWidth($this);
        },

        /**
         * Get active item.
         *
         * @param $this
         * @returns {Object} jQuery element
         * @private
         */
        _getActive: function ($this) {
            return $this.el.find('.uk-active');
        },

        /**
         * Get wrapper width.
         *
         * @param $this
         * @returns {number}
         * @private
         */
        _getParentWidth: function ($this) {
            return $this.el.parent().width();
        },

        /**
         * On mouseup and touchend.
         *
         * @param $this
         * @private
         */
        _release: function ($this) {
            $this.el.css('transition', '');

            const maxOffset = $this.el.width() - $this._getParentWidth($this);

            if ($this.currentOffset + $this.delta <= 0) {
                $this.currentOffset = 0;
            } else if (($this.currentOffset + $this.delta) > maxOffset) {
                $this.currentOffset = maxOffset;
            } else {
                $this.currentOffset += $this.delta;
            }

            $this.el.css('transform', 'translateX(' + (-$this.currentOffset) + 'px)');
            $this.delta = 0;
            $this.swipeStarted = false;
            $this.touchDetecting = false;

            clearTimeout($this.releaseTimer);
            $this.releaseTimer = setTimeout(function() {
                if (!$this.swipeStarted && !$this.touchDetecting) {
                    $this._update($this);
                }
            }, 6000);
        },

        /**
         * Set new element position.
         *
         * @param $this
         * @private
         */
        _scroll: function ($this, newX, newY) {
            const x = $this.touch.clientX,
                  y = $this.touch.clientY;

            if ($this.touchDetecting){
                if ( Math.abs(x - newX) >= Math.abs(y - newY) ) {
                    $this.swipeStarted = true;
                }

                $this.touchDetecting = false;
            }

            if ($this.swipeStarted) {
                const maxOffset = $this.el.width() - $this._getParentWidth($this),
                      diff = (x - newX) - $this.delta;
                if ($this.currentOffset + $this.delta <= 0 || ($this.currentOffset + $this.delta) >= maxOffset) {
                    $this.delta += diff / 100;
                } else {
                    $this.delta = x - newX;
                }

            }

            $this.el.css('transition', 'none');
            $this.el.css('transform', 'translateX(' + (-($this.currentOffset + $this.delta)) + 'px)');
        },

        'click .jsFilterButton': function(e, $this) {
            $this._update($this, $(this));
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

            if (!$.inArray($this.touch, e.originalEvent.changedTouches)) {
                return;
            }

            const newX = e.originalEvent.changedTouches[0].clientX,
                  newY = e.originalEvent.changedTouches[0].clientY;

            $this._scroll($this, newX, newY);
        },

        /**
         * Touchend on the element.
         *
         * @param e
         * @param $this
         */
        'touchend {element}': function(e, $this) {
            $this._release($this);
        },

        /**
         * Mousedown on the element.
         *
         * @param e
         * @param $this
         */
        'mousedown {element}': function(e, $this) {
            if (!$this._isOverflowed($this)){
                return;
            }

            $this.touch = e.originalEvent;
            $this.touchDetecting = true;
        },

        /**
         * Mousemove on the element.
         *
         * @param e
         * @param $this
         */
        'mousemove {document} body': function(e, $this) {
            if (!$this.swipeStarted && !$this.touchDetecting){
                return;
            }

            const newX = e.originalEvent.clientX,
                  newY = e.originalEvent.clientY;

            $this._scroll($this, newX, newY);
        },

        /**
         * Mouseup on the element.
         *
         * @param e
         * @param $this
         */
        'mouseup {document} body': function(e, $this) {
            $this._release($this);
        }

    });
});
