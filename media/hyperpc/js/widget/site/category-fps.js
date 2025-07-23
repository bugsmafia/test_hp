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

    JBZoo.widget('HyperPC.SiteCategoryFps', {}, {

        $control : null,
        $imageEl : null,

        /**
         * Initialize widget.
         *
         * @param $this
         */
        init : function ($this) {
            $this.$control = $this.$('.jsFpsControl');

            const $gameControl = $this.$control.find('.jsFpsGameSelect');
            let $defaultOption = $gameControl.find('option[selected]');

            if ($defaultOption.length === 0) {
                const gamesCount = $gameControl.find('option').length,
                      rand = Math.floor(Math.random() * gamesCount);
                $defaultOption = $gameControl.find('option').eq(rand).attr('selected', 'selected');
            }

            $this.$imageEl = $this.$('.jsFpsGameImg');
            UIkit.img($this.$imageEl, {
                dataSrc: $defaultOption.data('img')
            });

            $this._updateFps($this);

            $(document).on('hpproductsloaded', function() {
                $this._updateFps($this);
            });
        },

        /**
         * Update fps in teasers.
         *
         * @param $this
         * @private
         */
        _updateFps : function ($this) {
            const state = $this._getFpsState($this);
            $this.$('.jsProductTeaserFps').each(function() {
                const $teaserFps = $(this),
                      fps = $teaserFps.data('fps'),
                      game = state['game'],
                      quality = state['quality'],
                      resolution = state['resolution'],
                      topLimit   = 200;

                if (fps[game] && fps[game][quality][resolution] !== 0) {
                    const barHeight = 100 - ((topLimit - fps[game][quality][resolution]) / (topLimit * 0.01));
                    $teaserFps.find('.hp-product-teaser-fps__clip, .jsFpsCounter').css('bottom', Math.min(barHeight, 100) + '%');
                    $teaserFps.find('.hp-product-teaser-fps__bar')
                        .css('clip-path', 'inset(' + (100 - barHeight) + '% 0 0)')
                        .css('-webkit-clip-path', 'inset(' + (100 - barHeight) + '% 0 0)');
                    $teaserFps.find('.jsFpsCounter').animate({'opacity' : 0.25}, 250, function() {
                        $(this).html(fps[game][quality][resolution]).animate({'opacity' : 1}, 250);
                    });
                } else {
                    $teaserFps.find('.hp-product-teaser-fps__clip, .jsFpsCounter').css('bottom', '0');
                    $teaserFps.find('.hp-product-teaser-fps__bar')
                        .css('clip-path', 'inset(100% 0 0)')
                        .css('-webkit-clip-path', 'inset(100% 0 0)');
                    $teaserFps.find('.jsFpsCounter').html('&nbsp;');
                }
            });
        },

        /**
         * Get current fps params.
         *
         * @param $this
         * @returns {Object}
         * @private
         */
        _getFpsState : function ($this) {
            return {
                'game'       : $this.$control.find('.jsFpsGameSelect').val(),
                'resolution' : $this.$control.find('.jsFpsResolutionSelect').val(),
                'quality'    : 'ultra'
            }
        },

        /**
         * On change fps params.
         *
         * @param e
         * @param $this
         */
        'change .jsFpsControl select' : function (e, $this) {
            const $select = $(this);
            if ($select.hasClass('jsFpsGameSelect')) {
                $this.$imageEl.attr('src', $select.find('option:selected').data('img'))
            }

            const $firstTeaser = $this.$('.jsProductTeaserFps').eq(0).parent(),
                  firstTeaserPosition = $firstTeaser.offset().top + $firstTeaser.height(),
                  windowPosition = window.scrollY + window.innerHeight;

            if (firstTeaserPosition > windowPosition) {
                const fakeLink = document.createElement('a'),
                      offsetCorection = window.innerWidth < 960 ? 65 : 130,
                      offset = window.innerHeight - ($firstTeaser.height() + offsetCorection);

                UIkit.util.on(fakeLink, 'scrolled', function () {
                    $this._updateFps($this);
                });
                UIkit.scroll(fakeLink, {offset: offset}).scrollTo($firstTeaser);
            } else {
                $this._updateFps($this);
            }
        }

    });
});
