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

    JBZoo.widget('HyperPC.SidebarSticky', {
        'topOffset'    : 61,
        'bottomOffset' : 0
    }, {

        uikitSticky     : null,
        overheight      : false,
        pageOffset      : 0,
        isFixedUp       : false,
        isFixedDown     : false,
        isMarginSet     : true,

        /**
         * Initialize widget.
         *
         * @param $this
         */
        init : function ($this) {

            if (this._isOverheight($this)) {
                this._update($this);
            } else {
                this.uikitSticky = UIkit.sticky($this.el, {
                    offset: this.getOption('topOffset'),
                    media: '@m',
                    bottom: true
                });
            }

            $(window).on('scroll', function(){

                if (!$this.overheight) {
                    return;
                }

                $this._update($this);
            });

            var resizeTimer;
            $(window).on('resize', function(){

                clearTimeout(resizeTimer);
                
                resizeTimer = setTimeout(function() {
                    
                    if ($this._isOverheight($this)) {

                        if ($this.uikitSticky !== null){
                            $this.uikitSticky.$destroy();

                        }

                        $this._update($this);
                    } else {
                        if ($this.uikitSticky === null){
                            $this.uikitSticky = UIkit.sticky($this.el, {
                                offset: $this.getOption('topOffset'),
                                media: '@m'
                            });
                        }
                    }

                }, 250);

            });

        },

        /**
         * Update element position.
         *
         * @param $this
         * @private
         */
        _update: function ($this) {
            var sidebarTop = $this.el["0"].offsetTop;
            var sidebarBottom = sidebarTop + $this.el["0"].clientHeight;
            var windowBottom  = window.pageYOffset + window.outerHeight;
            var parrentOffset = $this.el.parent()["0"].offsetTop;

            // скролл вниз
            if ($this._scrollDirection($this) == 'down') {
                $this.pageOffset = window.pageYOffset;
                // еще не зафиксировано снизу
                if (!$this.isFixedDown) {

                    if ($this.isFixedUp) {
                        
                        $this.isFixedDown = false;
                        $this.isMarginSet    = true;
                        $this.isFixedUp   = false;

                        $this.el.removeClass('uk-sticky-fixed');
                        $this.el.css({
                            position  : '',
                            top       : '',
                            width     : '',
                            marginTop : window.pageYOffset - $this.el.parent()["0"].offsetTop + $this.getOption('topOffset')

                        });

                        return;
                    }

                    // низ блока совпал с низом страницы с учетом bottomOffset
                    if (windowBottom >= sidebarBottom + $this.getOption('bottomOffset')) {
                        $this.isFixedDown = true;
                        $this.isMarginSet = false;
                        $this.isFixedUp   = false;

                        $this.el.addClass('uk-sticky-fixed');
                        $this.el.css({
                            position : "fixed",
                            bottom   : $this.getOption('bottomOffset'),
                            width    : $this.el.width()
                        });
                    }


                }
            // скролл вверх
            } else if ($this._scrollDirection($this) == 'up') {
                $this.pageOffset = window.pageYOffset;
                //блок фиксирован снизу
                if ($this.isFixedDown) {
                    $this.isFixedDown = false;
                    $this.isMarginSet = true;
                    $this.isFixedUp   = false;

                    $this.el.removeClass('uk-sticky-fixed');
                    $this.el.css({
                        position  : '',
                        bottom    : '',
                        width     : '',
                        marginTop : window.pageYOffset - $this.el.parent()["0"].offsetTop + $this.el["0"].offsetTop
                    });
                // уже не фиксирован снизу, но не выше родителя
                } else if ($this.isMarginSet || $this.isFixedUp) {
                    // позиция задана маргином
                    if($this.isMarginSet) {

                        // isMarginSet -> isFixedUp
                        if (sidebarTop >= window.pageYOffset + $this.getOption('topOffset')) {

                            $this.isFixedDown = false;
                            $this.isMarginSet = false;
                            $this.isFixedUp   = true;

                            $this.el.addClass('uk-sticky-fixed');
                            $this.el.css({
                                position : "fixed",
                                top      : $this.getOption('topOffset'),
                                width    : $this.el.width(),
                                margin   : ''
                            });

                        }

                    } else if ($this.isFixedUp) {

                        if (window.pageYOffset + $this.getOption('topOffset') <= parrentOffset) {
                            console.log('isFixedUp -> unfix');

                            $this.isFixedDown = false;
                            $this.isMarginSet = false;
                            $this.isFixedUp   = false;

                            $this.el.removeClass('uk-sticky-fixed');
                            $this.el.css({
                                position : '',
                                top      : '',
                                width    : '',
                                margin   : ''
                            });
                        }

                    }

                }

            }

        },

        /**
         * Check element height.
         *
         * @param $this
         * @returns {boolean}
         * @private
         */
        _isOverheight: function($this) {
            if ($this.el.height() + $this.getOption('topOffset') + $this.getOption('bottomOffset') > window.outerHeight) {
                this.overheight = true;
                return true;
            } else {
                this.overheight = false;
                return false;
            }
        },

        /**
         * Detecting scroll direction.
         *
         * @param $this
         * @returns {string}
         * @private
         */
        _scrollDirection: function($this) {
            var scrollDirection = 'same';
            if ($this.pageOffset < window.pageYOffset) {
                scrollDirection = 'down';
            } else if ($this.pageOffset > window.pageYOffset) {
                scrollDirection = 'up';
            }

            return scrollDirection;
        }

    });
});