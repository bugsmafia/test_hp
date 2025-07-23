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

    JBZoo.widget('HyperPC.SiteProductMiniConfigurator', {
        'items' : {}
    }, {

        /**
         * Initialize widget.
         *
         * @param $this
         */
        init : function ($this) {
            $this._setInitPosition($this);

            const url = new URL(window.location.href);
            if (url.searchParams.get('window_width')) {
                $this.el.css('maxWidth', url.searchParams.get('window_width') + 'px');
            }

            UIkit.util.on($this.el, 'itemshow', function(e) {
                const eventDetails = e.detail[0],
                      firstActive = eventDetails.index + 1,
                      lastActive = eventDetails.index + (eventDetails.length - eventDetails.maxIndex);

                $this.$('.jsSliderNavActiveRange').text(
                    firstActive === lastActive ? firstActive : firstActive + ' - ' + lastActive
                );
            });
        },

        /**
         * Set init position.
         *
         * @param $this
         */
        _setInitPosition : function($this) {
            const $defaultItem = $this.$('.jsIsDefault'),
                  defaultIndex = $defaultItem.index(),
                  $sliderItemsWrapper = $this.$('.uk-slider-items'),
                  slidesCount  = $sliderItemsWrapper.children().length;

            if (defaultIndex !== 0 && slidesCount > 0) {
                const slideWidth = $sliderItemsWrapper.get(0).scrollWidth / slidesCount,
                      maxOffset = $sliderItemsWrapper.get(0).scrollWidth - $sliderItemsWrapper.get(0).clientWidth,
                      offset = Math.min(maxOffset, slideWidth * defaultIndex);

                $sliderItemsWrapper
                    .css('transform', 'translate3d(-' + offset + 'px, 0px, 0px)');
            }

            UIkit.slider($this.el, {index: defaultIndex, finite: true, velocity: 10});
        },

        /**
         * Choose group part.
         *
         * @param e
         * @param $this
         */
        'click .jsItemChoose' : function (e, $this) {
            const $button = $(this),
                  itemKey = $button.data('itemkey'),
                  items   = $this.getOption('items');

            window.parent.$(window.parent.document).trigger('partupdated', items[itemKey]);

            $(window.parent.document)
                .find('.uk-lightbox')
                .filter('.uk-open')
                .find('[uk-close], [data-uk-close]')
                .eq(0)
                .trigger('click');

            e.preventDefault();
        },
    });
});
