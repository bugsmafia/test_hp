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

    JBZoo.widget('HyperPC.ConfiguratorSteps', {
        complectations : [],
        availableComplectations : [],
        activeComplectation : 0,
        platform: {},
        platformState: {},
        currentStep : 1,
    }, {

        /**
         * Initialize widget.
         *
         * @param $this
         */
        init: function ($this) {
            // fix radio state after history navigate
            $this.$('[type="radio"][checked]').prop('checked', 'checked');

            $this.complectations = $this.getOption('complectations');
            $this.availableComplectations = $this.getOption('availableComplectations');
            $this.activeComplectation = $this.getOption('activeComplectation');
            $this.platform = $this.getOption('platform');
            $this.platformState = $this.getOption('platformState');
            $this.currentStep = $this.getOption('currentStep');

            $this.stepsCount = $this.$('.jsStepConfiguratorProgress').children().length;

            $this._updateProgressOffset($this);
            let resizeTimer;
            $(window).on('resize', function (e) {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(function () {
                    $this._updateProgressOffset($this);
                }, 250);
            });
        },

        /**
         * Changes page state according to the current step
         *
         * @param $this
         */
        _setStep: function ($this) {
            if ($this.currentStep > 0 && $this.currentStep <= $this.stepsCount) {
                switch ($this.currentStep) {
                    case 1:
                    case 2:
                        $this._openLoader();
                        window.location.href = $this.$('.jsStepConfiguratorProgress').children().eq($this.currentStep - 1).attr('href');
                        break;
                    case 3:
                        const $targetStep = $this.$('.jsStepConfiguratorSwitcher').children().eq($this.currentStep - 1);
                        if ($targetStep.html().trim() === '') {
                            $this.currentStep--;
                            $this._setStep($this);
                            return;
                        }

                        $this.$('.jsComplectationSummary').attr('hidden', 'hidden');
                        $this._toggleStepNav($this);
                        break;
                    case 4:
                        $this.$('.jsComplectationSummary').removeAttr('hidden');
                        $this._toggleStepNav($this);
                        break;
                    case 5:
                        $this._openLoader();
                        window.location.href = $this.complectations[$this.activeComplectation].href;
                        break;
                    case $this.stepsCount:
                        $this.$('.jsStepForward').addClass('uk-disabled');
                        break;
                }
            }

            if ($this.el.get(0).getBoundingClientRect().top < 0) {
                window.scrollTo(0,116);
            }
        },

        /**
         * Toggle step nav
         *
         * @param $this
         */
        _toggleStepNav: function ($this) {
            $this.$('.jsStepConfiguratorSwitcher')
                .children()
                .eq($this.currentStep - 1)
                .addClass('uk-active')
                .siblings()
                .removeClass('uk-active');

            $this.$('.jsStepConfiguratorProgress').children().each(function () {
                const $stepNavItem = $(this),
                      index = $stepNavItem.index();
                $stepNavItem.removeClass('hp-step-configurator__step--past hp-step-configurator__step--active')
                if (index < $this.currentStep - 1) {
                    $stepNavItem
                        .addClass('hp-step-configurator__step--past')
                } else if (index === $this.currentStep - 1) {
                    $stepNavItem
                        .addClass('hp-step-configurator__step--active')
                }
            });

            $this._updateProgressOffset($this);
        },

        /**
         * Updates progress offset
         *
         * @param $this
         */
        _updateProgressOffset: function ($this) {
            const $progress = $this.$('.jsStepConfiguratorProgress'),
                  $parent = $progress.parent(),
                  progressWidth = $progress.get(0).scrollWidth,
                  parentWidth = $parent.width();

            if (progressWidth > parentWidth) {
                const maxOffset = progressWidth - parentWidth;

                let targetOffset = 0;
                $progress.children().filter('.hp-step-configurator__step--past').each(function() {
                    targetOffset += this.offsetWidth;
                })

                $progress.css('transform', 'translateX(-' + Math.min(maxOffset, targetOffset) + 'px)');
            } else {
                $progress.css('transform', '');
            }
        },

        /**
         * Price format
         *
         * @param   price
         *
         * @returns {*|string}
         */
        _priceFormat: function (price) {
            const moneyConfig = window.Joomla.getOptions('moneyConfig') || {
                'decimal_sep': '.',
                'thousands_sep': ' ',
                'num_decimals': 0
            };

            return window.JBZoo.numFormat(price, moneyConfig.num_decimals, moneyConfig.decimal_sep, moneyConfig.thousands_sep);
        },

        /**
         * On click platform param button
         *
         * @param e
         * @param $this
         */
        'click .jsPlatformParamsSection button': function (e, $this) {
            const $button = $(this);
            if ($button.hasClass('uk-button-primary')) {
                return;
            }

            const $section = $button.closest('.jsPlatformParamsSection');

            $section
                .find('.uk-button')
                .filter('.uk-button-primary')
                .removeClass('uk-button-primary')
                .addClass('uk-button-default')
                .find('[uk-icon]')
                .attr('uk-icon', 'plus-circle');
            $button
                .removeClass('uk-button-default')
                .addClass('uk-button-primary')
                .find('[uk-icon]')
                .attr('uk-icon', 'check');

            const eventProp = $section.data('prop'),
                  eventValue = $button.data('value');

            $this.platform[eventProp]['value'] = eventValue;

            const stateKeys = Object.keys($this.platform);
            const checkProps = stateKeys.splice(stateKeys.indexOf(eventProp) + 1);
            const meanProps = stateKeys;

            $this.availableComplectations = [];
            meanProps.forEach(function (prop, index) {
                const value = $this.platform[prop]['value'];
                if (index === 0) {
                    $this.availableComplectations = $this.platformState[prop][value].related;
                } else {
                    $this.availableComplectations = $this.availableComplectations.filter(function (key) {
                        return $this.platformState[prop][value].related.indexOf(key) !== -1;
                    });
                }

                // toogle active state
                if (prop === eventProp) {
                    for (let value in $this.platformState[prop]) {
                        $this.platformState[prop][value].isActive = (value === eventValue);
                    }
                }
            });

            checkProps.forEach(function (prop, index) {
                const sectionState = $this.platformState[prop];
                let activeItem = null;
                for (let key in sectionState) {
                    const item = sectionState[key],
                          itemRelated = item.related;

                    if ($this.availableComplectations.some(function (complectationId) {
                        return itemRelated.indexOf(complectationId) !== -1;
                    })) {
                        item.isDisabled = false;
                        if (item.isActive) {
                            activeItem = item;
                        }
                    } else {
                        item.isDisabled = true;
                        item.isActive = false;
                    }

                    $this.platformState[prop][key] = item;
                }

                if (activeItem !== null) {
                    $this.availableComplectations = $this.availableComplectations.filter(function (key) {
                        return activeItem.related.indexOf(key) !== -1;
                    });
                } else {
                    let found = false;
                    for (let key in $this.platformState[prop]) {
                        if ($this.platformState[prop][key].isDisabled === false && !found) {
                            $this.platformState[prop][key].isActive = true;
                            $this.platform[prop]['value'] = key;
                            found = true;
                            $this.availableComplectations = $this.availableComplectations.filter(function (complectationId) {
                                return $this.platformState[prop][key].related.indexOf(complectationId) !== -1;
                            });
                        }
                    }
                }

                // update buttons
                const $section = $this.$('.jsPlatformParamsSection').filter('[data-prop="' + prop + '"]');
                $section.find('button').each(function () {
                    const $button = $(this),
                          key = $button.data('value'),
                          isActive = $this.platformState[prop][key].isActive;

                    $button
                        .removeClass('uk-button-default uk-button-primary uk-disabled')
                        .addClass(isActive ? 'uk-button-primary' : 'uk-button-default')
                        .find('[uk-icon]').attr('uk-icon', isActive ? 'check' : 'plus-circle');

                    if ($this.platformState[prop][key].isDisabled) {
                        $button.addClass('uk-disabled');
                    }
                });
            });

            //update platform summary
            const $platformSummaryList = $this.$('.jsPlatformParamsSummary');
            $platformSummaryList.children().each(function () {
                const $param = $(this),
                      prop = $param.data('prop');

                $param.find('.jsPlatformParamsSummaryValue').text($this.platform[prop]['value']);
            });

            // filter complectations
            const $complectations = $this.$('[data-complectation]');
            $complectations.attr('hidden', 'hidden').find('input').prop('checked', false);
            $this.availableComplectations.forEach(function(id) {
                $complectations.filter('[data-complectation="' + id + '"]').removeAttr('hidden');
            });

            // set available complectation
            let startPrice = 0;
            $this.availableComplectations.forEach(function (complectationId) {
                const complectationPrice = $this.complectations[complectationId].price;
                if (startPrice === 0) {
                    startPrice = complectationPrice;
                    $this.activeComplectation = complectationId;
                } else {
                    if (complectationPrice < startPrice) {
                        startPrice = complectationPrice;
                        $this.activeComplectation = complectationId;
                    }
                }
            });
            $complectations
                .filter('[data-complectation="' + $this.activeComplectation + '"]')
                .find('input').prop('checked', true).trigger('change');
        },

        /**
         * On click progress step
         *
         * @param e
         * @param $this
         */
        'click .jsStepConfiguratorProgress > *': function (e, $this) {
            const $target = $(this),
                  index = $target.index();

            $this.currentStep = index + 1;
            $this._setStep($this);
        },

        /**
         * On click step back button
         *
         * @param e
         * @param $this
         */
        'click .jsStepBack': function (e, $this) {
            $this.currentStep--;
            $this._setStep($this);
        },

        /**
         * On click step forward button
         *
         * @param e
         * @param $this
         */
        'click .jsStepForward': function (e, $this) {
            $this.currentStep++;
            $this._setStep($this);
        },

        /**
         * On change complectation
         *
         * @param e
         * @param $this
         */
        'change .hp-configurator-complectation input': function (e, $this) {
            const $complectation = $(this).closest('.hp-configurator-complectation'),
                  id = $complectation.data('complectation');
            $this.activeComplectation = id;
            $this.$('.jsComplectationSummaryValue').text($this.complectations[id].name);
            $this.$('.jsStartPrice').text($this._priceFormat($this.complectations[id].price));
        }

    });
});