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

    JBZoo.widget('HyperPC.ConfiguratorStepModel', {}, {

        /**
         * Initialize widget.
         *
         * @param $this
         */
        init : function ($this) {
            $this.$modelImage = $this.$('.jsStepConfiguratorModelImg');
            $this.$modelName = $this.$('.jsStepConfiguratorModelName');

            $this.$slider = $this.$('[uk-slider]');
            $this.$sliderItems = $this.$('.jsStepConfiguratorModelSliderItems').children();
            $this.maxIndex = $this.$sliderItems.length - 1;

            // fix radio state after history navigate
            $this.$sliderItems.find('[type="radio"][checked]').prop('checked', 'checked');

            $this.activeIndex = $this.$sliderItems.find('input').filter(':checked').closest('.jsStepConfiguratorModelSliderItems > div').index();
            if ($this.activeIndex > 0) {
                $this._sliderGoToIndex($this, $this.activeIndex);
            }

            $this._checkSliderWidth($this);

            let resizeTimer;
            $(window).on('resize', function(e) {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(function() {
                    $this._checkSliderWidth($this);
                }, 250);
            });

            let showTimer;
            UIkit && UIkit.util.on($this.$slider, 'itemshown', function(e) {
                clearTimeout(showTimer);
                showTimer = setTimeout(function() {
                    $this._updateSeriesNav($this);
                }, 350);
            });
        },

        /**
         * Check slider width
         *
         * @param $this
         */
        _checkSliderWidth : function ($this) {
            let slidesWidth = 0;
            $this.$sliderItems.each(function(index, el) {
                slidesWidth += el.clientWidth;
            });

            const $sliderWrapper = $this.$sliderItems.parent();
            if (slidesWidth < $this.$slider.get(0).clientWidth) {
                $sliderWrapper.addClass('uk-flex-center');
            } else if ($sliderWrapper.is('.uk-flex-center')) {
                $sliderWrapper.removeClass('uk-flex-center');
                $this._sliderGoToIndex($this, $this.activeIndex);
            }
        },

        /**
         * Update series nav
         *
         * @param $this
         */
        _updateSeriesNav : function ($this) {
            const $seriesNav = $this.$('.jsStepConfiguratorModelSliderSeriesNav');
            if ($this.$sliderItems.last().hasClass('uk-active')) {
                $seriesNav.children().last().addClass('uk-active').siblings().removeClass('uk-active');
            } else {
                const $actives = $this.$sliderItems.filter('.uk-active');
                if ($actives.length) {
                    const firstVisible = $actives.first().index();
                    let $activeNav = $seriesNav.children().first();
                    $seriesNav.children().each(function() {
                        const $item = $(this),
                              start = $item.data('slider-item');

                        if (start > firstVisible) {
                            $activeNav.addClass('uk-active').siblings().removeClass('uk-active');
                            return false;
                        } else {
                            $activeNav = $item;
                        }
                    });
                }
            }
        },

        /**
         * Update active model
         *
         * @param $this
         */
        _updateActiveModel : function($this) {
            $this.$sliderItems.eq($this.activeIndex).find('input').prop('checked', 'checked').trigger('change');
        },

        /**
         * Handle active model change
         *
         * @param $this
         */
        _handleActiveModelChange : function($this) {
            const $activeItem = $this.$sliderItems.eq($this.activeIndex),
                  imgSrc = $activeItem.find('img').data('fullsize'),
                  modelName = $activeItem.find('input').data('title');

            $this.$modelName.text(modelName);
            $this.$modelImage.attr('src', imgSrc);

            $this._updateModelsNav($this);
            $this._updateModelsSelect($this);
            $this._sliderGoToIndex($this, $this.activeIndex);
        },

        /**
         * Update models nav
         *
         * @param $this
         */
        _updateModelsNav : function($this) {
            const $prev = $this.$('.jsStepConfiguratorPrevModel'),
                  $next = $this.$('.jsStepConfiguratorNextModel');

            $prev.removeAttr('hidden');
            $next.removeAttr('hidden');
            if ($this.activeIndex < 1) {
                $prev.attr('hidden', 'hidden');
            } else if ($this.activeIndex >= $this.maxIndex) {
                $next.attr('hidden', 'hidden');
            }
        },

        /**
         * Update models select
         *
         * @param $this
         */
        _updateModelsSelect : function ($this) {
            $this.$('.jsStepConfiguratorModelSelect').children().eq($this.activeIndex).prop('selected', 'selected');
        },

        /**
         * Handle model navigate
         *
         * @param $this
         * @param {number} index
         */
        _sliderGoToIndex : function ($this, index) {
            const targeItemOffset = $this.$sliderItems.eq(index).get(0).offsetLeft,
                    $sliderItemsWrapper = $this.$sliderItems.first().parent(),
                    maxOffset = $sliderItemsWrapper.get(0).scrollWidth - $sliderItemsWrapper.get(0).clientWidth;

            UIkit && UIkit.slider($this.$slider).$destroy();

            $sliderItemsWrapper
                .css('transition', 'transform .3s')
                .css('transform', 'translate3d(-' + Math.min(targeItemOffset, maxOffset) + 'px, 0px, 0px)');

            setTimeout(() => {
                $sliderItemsWrapper.css('transition', '');
                UIkit && UIkit.slider($this.$slider, {finite: true, index: index});
            }, 400);
        },

        /**
         * Handle model navigate
         *
         * @param $this
         */
        _handleModelNavigate : function($this) {
            $this._updateActiveModel($this);
        },

        /**
         * On change .jsStepConfiguratorModelSelect
         *
         * @param e
         * @param $this
         */
        'change .jsStepConfiguratorModelSelect' : function (e, $this) {
            $this.activeIndex = $(this).find(':selected').index();
            $this._updateActiveModel($this);
        },

        /**
         * On change step-configurator-model radio
         *
         * @param e
         * @param $this
         */
        'change [name="step-configurator-model"]' : function (e, $this) {
            const $input = $(this),
                  $item = $input.parent();

            $this.activeIndex = $item.parent().index();

            $this._handleActiveModelChange($this);
        },

        /**
         * On click .jsStepConfiguratorPrevModel
         *
         * @param e
         * @param $this
         */
        'click .jsStepConfiguratorPrevModel' : function (e, $this) {
            e.preventDefault();
            $this.activeIndex--;
            $this._handleModelNavigate($this);
        },

        /**
         * On click .jsStepConfiguratorNextModel
         *
         * @param e
         * @param $this
         */
        'click .jsStepConfiguratorNextModel' : function (e, $this) {
            e.preventDefault();
            $this.activeIndex++;
            $this._handleModelNavigate($this);
        },

        /**
         * On click .jsStepConfiguratorModelSliderSeriesNav a
         *
         * @param e
         * @param $this
         */
        'click .jsStepConfiguratorModelSliderSeriesNav a' : function(e, $this) {
            e.preventDefault();
            const $li = $(this).parent(),
                  targetIndex = $li.data('sliderItem');
            $li.addClass('uk-active').siblings().removeClass('uk-active');

            $this._sliderGoToIndex($this, targetIndex);
        },

        /**
         * On click .jsStepForward
         *
         * @param e
         * @param $this
         */
        'click .jsStepForward' : function (e, $this) {
            document.location.href = $this.$('[name="step-configurator-model"]:checked').val();
        },

        /**
         * On click .jsStepBack
         *
         * @param e
         * @param $this
         */
        'click .jsStepBack' : function(e, $this) {
            document.location.href = $this.$('.hp-step-configurator__step--active').prev().attr('href');
        },

    });
});