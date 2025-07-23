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
 * @author     Sergey Kalistratov <kalistratov.s.m@gmail.com>
 * @author     Artem Vyshnevskiy
 */

jQuery(function ($) {

    JBZoo.widget('HyperPC.SiteConfigurator', {
        'context'                   : 'legacy',
        'productId'                 : 0,
        'leaveConfirmMsg'           : 'Are you sure?',
        'changeComlectationMsg'     : 'Are you sure?',
        'txtSave'                   : 'Save',
        'txtContinue'               : 'Continue',
        'txtLeave'                  : 'Leave',
        'productName'               : '',
        'resetConfirmMsg'           : '',
        'msgTryAgain'               : '',
        'nothingSelectedImg'        : '',
        'hasWarnings'               : false,
        'isInCart'                  : false,
        'instockTooltipText'        : 'In stock',
        'preorderTooltipText'       : 'At a remote warehouse',
        'vat'                       : 20,
        'compatibilitiesData'       : [],
        'langUpdateInCart'          : 'Update in cart',
        'langSaveNew'               : 'Save the new one',
        'langConfigInCartModalTitle': 'Configuration #%d is in the cart.',
        'langConfigInCartModalSub'  : 'Update the configuration in the cart or save the new configuration?',
        'langNum'                   : '#',
        'langSpecification'         : 'Spacification',
        'langConfigurationChanged'  : 'Configuration changed',
        'langLoginBeforeSaveAlert'  : 'Login first please',

        'langCompatibilityModalTitle': '',
        'langCompatibilityIncompatibleWith': '',
        'langCompatibilityIncompatibleWithCurrentConfig': '',
        'langCompatibilityIncompatibleTitle': '',
        'langCompatibilityAutoreplaceTitle': '',
        'langCompatibilityAutoremoveTitle': '',
        'langCompatibilitySubmitButton': '',
        'langCompatibilityCancelButton': '',
    }, {

        /**
         * @typedef {Object} CompatibilityCheckData
         * @property {Set<HTMLElement>} replacements Set of replacement parts
         * @property {Set<HTMLElement>} removements Set of parts for remove
         * @property {Set<HTMLElement>} compatibleParts List of compatible parts
         * @property {Set<HTMLElement>} incompatibleParts List of incompatible parts
         * @property {Array<Array<HTMLElement>>} incompatiblePairs Array of incompatible pairs of parts
         */

        isUnsaved : false,
        isInCart : false,
        gtmConfig : {
            'event' : 'Configurator'
        },
        $groups: null,

        /**
         * Initialize widget.
         *
         * @param $this
        */
        init: function ($this) {
            $this.$groups = $this.$('.sub-group-content');

            $this.$groups.each(function () {
                const $group = $(this),
                      $parts = $group.find('.hp-part-wrapper'),
                      $partsChecked = $parts.filter('.hp-part-checked');

                $this._checkGroupFilters($this, $group, $parts);

                $this.gtmConfig['ConfigID'] = $this.$('.jsSavedConfigurationId').val();
                const groupName = $group.find('.sub-group-description > h3').text().trim();
                if ($partsChecked.length > 0) {
                    const gtmGroupParts = [];
                    $partsChecked.each(function () {
                        const $part = $(this);
                        $this._setGroupPartImage($this, $part); // TODO do it on server side
                        $this._checkCompatibilities($this, $part, true); // TODO do it on server side
                        gtmGroupParts.push($part.data('name'));
                    });
                    $this.gtmConfig[groupName] = gtmGroupParts.join('|');
                } else {
                    $this.gtmConfig[groupName] = '';
                }
            });

            $this._updateTotalPrice($this);

            const $document = $(document);

            $this.$body = $('body');

            if (document.referrer !== '' && !/\/config(-\d+)?$/.test(document.referrer)) {
                $this.$('.jsLeaveConfigurator').attr('href', document.referrer);
            }

            const leaveLinksSelector = [
                '.uk-offcanvas li:not(.uk-parent) > a',
                '.hp-mod-cart-drop a:not([target="_blank"]):not([data-uk-toggle])',
                '.jsLeaveConfigurator',
                'a[href$="configurator"]',
            ];

            $document.on('click', leaveLinksSelector.join(', '), function (e) {
                if ($this.isUnsaved) {
                    e.preventDefault();
                    $this._handleLeaveConfigurator($this, $(this).attr('href'));
                }
            });

            $document.on('click', '.jsSaveOnLeave', function (e) {
                const $button = $(this);
                $this._lockButton($button);
                $this._handleManualSave($this, function () {
                    $button
                        .removeClass('uk-button-primary')
                        .addClass('uk-disabled uk-button-default')
                        .siblings('.jsGoToProduct')
                        .removeClass('uk-button-default')
                        .addClass('uk-button-primary');
                });
            });

            $document.on('click', '.jsGoToProduct', function (e) {
                UIkit.modal($(this).closest('.jsModalLeaveConfigurator').hide());
                $this._openLoader();
                document.location.href = $(e.target).data('href');
            });

            $document.on('change', '[name="complectation"]', function (e) {
                $(this)
                    .closest('.hp-configurator-complectation')
                    .addClass('hp-configurator-complectation--current')
                    .siblings()
                    .removeClass('hp-configurator-complectation--current');

                $this.$body.find('.jsProceedToLoadComplectation').removeAttr('disabled');
            });

            $document.on('click', '.jsProceedToLoadComplectation', function (e) {
                const $modal = $(this).closest('#change-platform-modal');
                const $radio = $modal.find('[name="complectation"]').filter(':checked');
                if (!$radio.is('disabled')) {
                    const href = $radio.val();
                    if ($this.isUnsaved) {
                        $this._handleChangeComlectation($this, href);
                    } else {
                        $this._openLoader();
                        document.location.href = href;
                    }
                }
                UIkit.modal($modal).hide();
                $modal.find('.jsProceedToLoadComplectation').attr('disabled', 'disabled');
                $modal.find('.hp-configurator-complectation')
                      .removeClass('hp-configurator-complectation--current')
                      .first().addClass('hp-configurator-complectation--current')
                      .find('[name="complectation"]').prop('checked', 'checked');
            });

            UIkit.util.on('#login-form-modal', 'hidden', function () {
                if (!$(this).hasClass('uk-open')) {
                    $this._setLoginMessage($this, '');
                    $this._unlockButton($this.$('.jsSaveConfig'));
                }
            });

            UIkit.util.on('#load-configuration-modal', 'show', function () {
                window.removeEventListener('beforeunload', $this._onBeforeUnloadCallback);
            });

            UIkit.util.on('#load-configuration-modal', 'hide', function () {
                $this.isUnsaved && window.addEventListener('beforeunload', $this._onBeforeUnloadCallback);
            });

            const $tapbarDropdown = $this.$('.tm-tapbar-button-more + .uk-dropdown');
            if ($tapbarDropdown.length) {
                UIkit.util.on($tapbarDropdown, 'show', function () {
                    $(this).closest('.uk-navbar-container').css('zIndex', '1000');
                });

                UIkit.util.on($tapbarDropdown, 'hide', function () {
                    $(this).closest('.uk-navbar-container').css('zIndex', '');
                });
            }

            $this.isInCart = $this.getOption('isInCart');

            $this._requestIdleCallback(function () {
                $this._initSidebarSticky($this);
            });

            $(window).on('storage', function (e) {
                switch (e.key) {
                    case 'hp_cart_items':
                        $this._checkCartState($this);
                        break;
                }
            });

            setTimeout(function () {
                const hash = location.hash;
                if ($this.$(hash).length > 0) {
                    const message = $('#system-message-container');
                    if (message.length > 0) {
                        message
                            .css({
                                'z-index'  : 9999,
                                'position' : 'relative'
                            })
                            .animate({height: 0, opacity: 0, MarginBottom: 0}, 1500, function () {
                                $(this).remove();
                                UIkit.modal(hash).show();
                            });
                    }
                }
            }, 1000);

            $this._checkWarnings($this);

            window.dataLayer = window.dataLayer || [];
            window.dataLayer.push({
                'event'    : 'hpTrackedAction',
                'hpAction' : 'visitedConfigurator'
            });
            $this.$('.jsOnlyInstockGlobal').one('change', function () {
                window.dataLayer.push({
                    'event'    : 'hpTrackedAction',
                    'hpAction' : 'onlyInstockChanged'
                });
            });
        },

        /**
         * Init sidebar sticky
         *
         * @param $this
         */
        _initSidebarSticky: function ($this) {
            let topSpacing = 60,
                bottomSpacing = 10;

            if ($this.$body.hasClass('device-table-yes') || $this.$body.hasClass('device-mobile-yes')) {
                topSpacing = 132;
                bottomSpacing = 61;
            }

            window.StickySidebar && $this.$('.jsConfigBox').stickySidebar({
                topSpacing: topSpacing,
                bottomSpacing: bottomSpacing
            });
        },

        /**
         * Checking availability state
         *
         * @param $this
         * 
         * @todo check configuration availability by ajax
         */
        _checkAvailability: function ($this) {
            const $availabilityWrapper = $this.$('.jsConfigurationAvailability');

            if ($availabilityWrapper.length > 0) {
                const configurationId = $this._getConfigurationId($this);

                if (!configurationId && !$this.isUnsaved) {
                    $('.jsAvailabilityTextInstock', $availabilityWrapper).removeAttr('hidden');
                    $('.jsAvailabilityTextPreorder', $availabilityWrapper).attr('hidden', 'hidden');
                } else {
                    $('.jsAvailabilityTextInstock', $availabilityWrapper).attr('hidden', 'hidden');
                    $('.jsAvailabilityTextPreorder', $availabilityWrapper).removeAttr('hidden');
                }
            }
        },

        /**
         * Checking init options to show a warning modal.
         *
         * @param e
         * @param $this
         */
        _checkWarnings: function ($this) {
            if ($this.getOption('hasWarnings')) {
                UIkit.modal('#hp-warning-modal').show();

                UIkit.util.on('#hp-warning-modal', 'hidden', function () {
                    $(this).find('.jsPriceDifferenceWarning').remove();
                });
            }
        },

        /**
         * Checks if configuration is in cart
         * 
         * @param $this
         */
        _checkCartState: function ($this) {
            const configId  = $this._getConfigurationId($this),
                  cartItems = JSON.parse(localStorage.getItem('hp_cart_items')) || [],
                  itemKey   = $this._getItemKey($this);

            isInCart = cartItems.some(function (item) {
                if (item.key && item.key === itemKey) {
                    return true;
                }
                return false;
            });

            if (configId > 0) {
                $this.isInCart = isInCart;
            } else {
                $this.isInCart = false;
            }

            if (!isInCart) {
                $this.$('.jsCartButtons').removeClass('hp-element-in-cart');
            } else if (!$this.isUnsaved) {
                $this.$('.jsCartButtons').addClass('hp-element-in-cart');
            }
        },

        /**
         * Get item key
         * 
         * @param $this
         * 
         * @returns {string}
         */
        _getItemKey: function ($this) {
            const configId = $this._getConfigurationId($this),
                  itemKey = 'product-' + $this.getOption('productId');

            if (configId > 0) {
                return itemKey + '-' + configId;
            }

            return itemKey;
        },

        /**
         * Set part image from its default option.
         *
         * @param $this
         * @param $part

         */
        _setPartDefaultImage: function ($this, $part) {
            const $defaultOption = $this._getDefaultOption($part);

            if ($defaultOption !== null) {
                const image = $defaultOption.data('image');

                $part.find('.hp-conf-part__image').children().attr('src', image);
            }
        },

        /**
         * Get part's default option.
         *
         * @param $part
         * @returns {(jQuery object|null)}
         */
        _getDefaultOption: function ($part) {
            const $options = $part.find('.hp-part-options');
            if ($options.length > 0) {
                const $defaultOption = $options.find('[value="' + $options.data('default-option') + '"]').closest('.hp-option');
                if ($defaultOption.length > 0) {
                    return $defaultOption;
                } else {
                    return $options.find('.hp-option').first();
                }
            }

            return null;
        },

        /**
         * Get part full price.
         *
         * @param $this
         * @param part
         * @returns {number}
         */
        _getPartPrice: function ($this, part) {
            const partPrice    = part.data('price'),
                  partOptions  = part.find('.hp-part-options'),
                  quantity     = $this._getPartQuantity(part);

            let optionPrice = 0;

            if (partOptions.length) {
                const checkedOption = partOptions.find('input[type=radio]:checked').closest('.hp-option');
                if (checkedOption.length === 1) {
                    optionPrice = checkedOption.data('price');
                }
            }

            return (partPrice + optionPrice) * quantity;
        },

        /**
         * Get part quantity value.
         *
         * @param $part
         * @returns {number}
         */
        _getPartQuantity: function ($part) {
            const $partQuantity = $part.find('.jsPartQuantity');

            if ($partQuantity.length > 0 && $partQuantity.val() > 1) {
                return $partQuantity.val();
            }

            return 1;
        },

        /**
         * Pick part in configurator.
         *
         * @param $this
         * @param $part
         * @param {number=} optionId
         */
        _selectPart: function ($this, $part, optionId) {
            $part.addClass('hp-part-checked');
            $part.find('.jsPartInput').prop('checked', true);
            $part.find('.jsUnsetPart').removeClass('uk-hidden');

            const $partOptions = $part.find('.hp-part-options');
            if ($partOptions.length > 0) {
                $part.find('.jsOptionToggle, .hp-conf-part__availability').addClass('uk-hidden');
                optionId = optionId || $partOptions.data('default-option');
                const $targetOption = $partOptions.find('[value="' + optionId + '"]').closest('.hp-option');
                $partOptions.attr('aria-hidden', 'false').removeAttr('hidden');
                $this._selectOption($part, $targetOption);
            }

            const $moreButton = $part.find('.hp-conf-part__more-btn');
            if ($moreButton.next().find('.jsUnsetPart').length === 0 && $partOptions.length > 0 && !$part.data('reloaded')) {
                $moreButton.addClass('uk-hidden');
            } else {
                $moreButton.removeClass('uk-hidden');
            }
        },

        /**
         * Pick option on picked part.
         *
         * @param part
         * @param option
         */
        _selectOption: function ($part, $option) {
            if ($option.find('.jsOptionInput').prop('checked') === false) {
                $option.find('.jsOptionInput').prop('checked', true);
            }
            $option.addClass('hp-option-checked').siblings().removeClass('hp-option-checked');

            const image = $option.data('image');
            $part.find('.hp-conf-part__image img').attr('src', image);
        },

        /**
         * Unset part.
         *
         * @param $this
         * @param $part
         */
        _unsetPart: function ($this, $part) {
            $part.removeClass('hp-part-checked');
            $part.find('.jsPartInput').prop('checked', false);
            $part.find('.jsUnsetPart').addClass('uk-hidden');
            $part.find('.jsOptionToggle,' +
                       '.hp-conf-part__availability,' +
                       '.hp-configurator-part-price')
                .removeClass('uk-hidden');
            $this._resetQuantity($part);

            const $partOptions = $part.find('.hp-part-options');
            $partOptions.find('input').prop('checked', false);
            $partOptions.find('.hp-option').removeClass('hp-option-checked');
            if (!$part.hasClass('hp-part-swatches')) {
                $partOptions.attr('aria-hidden', 'true').attr('hidden', 'hidden');
            }

            const $moreButton = $part.find('.hp-conf-part__more-btn');
            if ($moreButton.next().find('a:not(.uk-hidden)').length === 0) {
                $moreButton.addClass('uk-hidden');
            } else {
                $moreButton.removeClass('uk-hidden');
            }
            $this._setPartDefaultImage($this, $part);
        },

        /**
         * Reset part quantity.
         *
         * @param $part
         */
        _resetQuantity: function ($part) {
            const $partQuantity = $part.find('.jsPartQuantity');
            if ($partQuantity.length > 0) {
                const defaultQuantity = $partQuantity.data('default');
                $partQuantity.val(defaultQuantity);
            }
        },

        /**
         * Change selected part image.
         *
         * @param $this
         * @param $part
         */
        _setGroupPartImage: function ($this, $part) {
            const $subGroup = $part.closest('.sub-group-content');
            const $partsImage = $subGroup.find('.jsCheckedItemImage');

            if ($partsImage.length > 0) {
                const $checkedOption = $part.find('.hp-option-checked'),
                      $imageEl = $checkedOption.length ? $checkedOption : $part.find('.hp-configurator-part'),
                      selectedPartImageUrl = $imageEl.data('image');

                if (selectedPartImageUrl) {
                    $partsImage.attr('src', selectedPartImageUrl);
                } else {
                    $partsImage.attr('src', $this.getOption('nothingSelectedImg'));
                }

                if (typeof $subGroup.data('multiply') !== 'undefined') {
                    const partId = $part.data('id');
                    let images= [];
                    if (typeof $partsImage.data('images') !== 'undefined') {
                        images = $partsImage.data('images');
                    }
                    images[partId] = selectedPartImageUrl;
                    $partsImage.data('images', images);
                }
            }
        },

        /**
         * Reset selected part image in subgroup.
         *
         * @param $this
         * @param $part
         */
        _unsetGroupPartImage: function ($this, $part) {
            const $subGroup = $part.closest('.sub-group-content');
            const $partsImage = $subGroup.find('.jsCheckedItemImage');

            if ($partsImage.length > 0) {
                if (typeof $subGroup.data('multiply') !== 'undefined') {
                    const partId = $part.data('id'),
                          images = $partsImage.data('images')
    
                    delete images[partId];

                    const newImages = [],
                          imageUrls = [];
                    images.forEach(function (value, index) {
                        newImages[index] = value;
                        imageUrls.push(value);
                    });
                    $partsImage.data('images', newImages);
                    if (imageUrls.length > 0) {
                        $partsImage.attr('src', imageUrls.pop());
                    } else {
                        $partsImage.attr('src', $this.getOption('nothingSelectedImg'));
                    }
                } else {
                    $partsImage.attr('src', $this.getOption('nothingSelectedImg'));
                }
            }
        },

        /**
         * Update prices in group.
         *
         * @param $this
         * @param $group
         */
        _updateGroupPrice: function($this, $group) {
            const $groupParts  = $group.find('.hp-part-wrapper'),
                  $pickedParts = $groupParts.filter('.hp-part-checked'),
                  multiply     = (typeof $group.data('multiply') !== 'undefined');

            let groupTotal = 0;

            if ($pickedParts.length > 0) {
                $pickedParts.each(function(){
                    groupTotal += $this._getPartPrice($this, $(this));
                });

                $groupParts.each(function() {
                    const $part = $(this);
                    if ($part.hasClass('hp-part-checked')) {
                        $this._setPrice($this, $part, 0);
                    } else {
                        const quantity = $this._getPartQuantity($part),
                              partPrice = $part.data('price') * quantity;
                        if (multiply) {
                           $this._setPrice($this, $part, partPrice);
                        } else {
                           const priceDifference = partPrice - groupTotal;
                           $this._setPrice($this, $part, priceDifference);
                        }
                    }
                });
            } else {
                $groupParts.each(function () {
                    const $part = $(this),
                          quantity = $this._getPartQuantity($part)
                    $this._setPrice($this, $part, $part.data('price') * quantity);
                });
            }

            $groupParts.each(function () {
                const $part = $(this),
                      $optionsWrapper = $part.find('.hp-part-options');

                if ($optionsWrapper.length > 0) {
                    const partPrice = $part.data('price'),
                          quantity  = $this._getPartQuantity($part),
                          $options  = $optionsWrapper.children(),
                          $checkedOption = $options.filter('.hp-option-checked');

                    $options.each(function (){
                        const $option = $(this);
                        if ($option.hasClass('hp-option-checked')) {
                            $option.find('.jsOptionPrice').html('');
                        } else {
                            if (multiply) {
                                if ($checkedOption.length > 0) {
                                    const checkedPrice = $checkedOption.data('price');
                                    $this._setPrice($this, $option, ($option.data('price') - checkedPrice) * quantity);
                                } else {
                                    $this._setPrice($this, $option, ($option.data('price') + partPrice) * quantity);
                                }
                            } else {
                                const optionPriceDifference = (($option.data('price') + partPrice) * quantity) - groupTotal;
                                $this._setPrice($this, $option, optionPriceDifference);
                            }
                        }
                    });
                }
            });

            $group.data('groupTotal', groupTotal);
        },

        /**
         * Set price html to part or option.
         *
         * @param $this
         * @param item
         * @param price
         */
        _setPrice: function ($this, $item, price) {
            let newPrice = typeof price === 'undefined' ? $item.data('price') : price;
            if (typeof newPrice === 'string') {
               newPrice = parseInt(newPrice, 10);
            }

            if (newPrice === 0 || isNaN(newPrice)) {
                if ($item.is('.hp-part-wrapper')) {
                    $item.find('.hp-configurator-part-price').html('');
                } else if ($item.is('.hp-option')) {
                    $item.find('.jsOptionPrice').html('');
                }
            } else {
                if ($item.is('.hp-part-wrapper')) {
                    $item.find('.hp-configurator-part-price').html($this._priceDiffFormat($this, newPrice));
                } else if ($item.is('.hp-option')) {
                    $item.find('.jsOptionPrice').html('(' + $this._priceDiffFormat($this, newPrice) + ')');
                }
            }
        },

        /**
         * Update total price.
         *
         * @param $this
         */
        _updateTotalPrice: function ($this) {
            let totalPrice = 0;
            $this.$groups.each(function (){
                totalPrice += $(this).data('groupTotal');
            });

            $this._setTotalPrice($this, totalPrice);
            $this._updateCreditCalculationPrice($this, totalPrice);
        },

        /**
         * Update credit calculation price.
         *
         * @param $this
         * @param {number} configId
         */
        _updateCreditCalculationPrice: function ($this, price) {
            const $calculateCreditLink = $this.$('.jsItemMonthlyPayment').children().filter('a');
            if ($calculateCreditLink.length) {
                let paramsString = $calculateCreditLink.get(0).search;
                paramsString = paramsString.replace(/price=\d+/, 'price=' + price);

                $calculateCreditLink
                    .attr(
                        'href',
                        $calculateCreditLink.get(0).pathname + paramsString
                    );
            } else {
                const monthlyPayment = $this._calculateMonthlyPayment(price);
                $this._setMonthlyPayment($this, monthlyPayment);
            }
        },

        /**
         * Update credit calculation configId.
         *
         * @param $this
         * @param {number} configId
         */
        _updateCreditCalculationConfigId: function ($this, configId) {
            const $calculateCreditLink = $this.$('.jsItemMonthlyPayment').children().filter('a');
            if ($calculateCreditLink.length) {
                let paramsString = $calculateCreditLink.get(0).search;

                const configurationRe = /configuration_id=\d+/;
                paramsString = configurationRe.test(paramsString) ?
                                    paramsString.replace(configurationRe, 'configuration_id=' + configId) :
                                    paramsString + '&configuration_id=' + configId;

                $calculateCreditLink
                    .attr(
                        'href',
                        $calculateCreditLink.get(0).pathname + paramsString
                    );
            }
        },

        /**
         * Calculate monthly payment.
         *
         * @param {number} price
         * @param {number} [rate=18.00]
         * @param {number} [loanTerm=12]
         * @param {number} [downPayment=0]
         * @returns {number}
         */
        _calculateMonthlyPayment: function (price, rate, loanTerm, downPayment) {
            rate        = typeof rate === 'undefined' ? 20.00 : rate;
            loanTerm    = typeof loanTerm === 'undefined' ? 36 : loanTerm;
            downPayment = downPayment || 0;

            let monthlyPayment = 0;
            if (rate > 0) {
                monthlyPayment = (((price - downPayment) * rate) / 1200) / (1 - Math.pow((1 / (1 + (rate / 1200))), loanTerm));
            } else {
                monthlyPayment = (price - downPayment) / loanTerm;
            }

            return Math.ceil(monthlyPayment);
        },

        /**
         * Set new monthly payment.
         *
         * @param $this
         * @param {number} monthlyPayment
         */
        _setMonthlyPayment: function ($this, monthlyPayment) {
            $this._updatePriceElement($this, $('.jsItemMonthlyPayment'), monthlyPayment);
        },

        /**
         * Update right box.
         *
         * @param $this
         * @param $part
         * @param {string} action
         */
        _updateRightBox: function($this, $part, action) {
            let multiply = false;
            const groupId = parseInt($part.data('group'));
            if ($part.find('.jsPartInput').attr('type') === 'checkbox') {
                multiply = true;
            }

            const $boxGroup = $this.$('.hp-content-group-' + groupId);
            switch (action) {
                case 'remove':
                    if (multiply && $boxGroup.find('ul[id^=hp-box-group-] > li').length > 1) {
                        $boxGroup.find('.hp-box-part-' + $part.data('id')).remove();
                    } else {
                        $boxGroup.addClass('uk-hidden').html('');
                        if ($boxGroup.closest('.hp-root-group').find('.hp-sub-group li:not(.uk-hidden)').length === 0) {
                            $boxGroup.closest('.hp-root-group').addClass('uk-hidden');
                        }
                    }
                    $this._updateDaysToBuild($this);
                    break;
                case 'set':
                    if ($boxGroup.hasClass('uk-hidden')) {
                        $boxGroup.removeClass('uk-hidden').html($this._getGroupBoxHtml($this, $part));
                        $boxGroup.closest('.hp-root-group').removeClass('uk-hidden');
                    } else {
                        if (!multiply) {
                            $boxGroup.find('[id^=hp-box-group-]').html($this._getPartBoxHtml($this, $part));
                        } else {
                            $boxGroup.find('[id^=hp-box-group-]').append($this._getPartBoxHtml($this, $part));
                        }
                    }
                    $this._updateDaysToBuild($this);
                    break;
                case 'changeOption':
                    $boxGroup.find('.hp-box-part-' + $part.data('id') + ' > a').attr('href', $this._getPartBoxHref($part));
                    $boxGroup.find('.hp-box-part-' + $part.data('id') + ' .part-option').html($this._getOptionBoxHtml($part));
                    $this._updateDaysToBuild($this);
                    break;
                case 'changeQuantity':
                    $boxGroup.find('.hp-box-part-' + $part.data('id') + ' .jsBoxPartQuantity').html($this._getQuantityBoxHtml($part));
                    break;
            }

            switch (action) {
                case 'remove':
                case 'set':
                case 'changeOption':
                    const caseGroups = $('.jsBoxCaseImg').data('group');
                    if (typeof caseGroups === 'object' && caseGroups.indexOf(groupId) >= 0) {
                        $this._setBoxCaseImg($this);
                    }
                    break;
            }

            $this._updatePlatform($this, groupId);

            const groupName    = $part.closest('.sub-group-content').find('.sub-group-description > h3').text().trim(),
                  $pickedParts = $this.$('#hp-box-group-' + groupId + ' > li > a'),
                  partNames    = [];
            $pickedParts.each(function () {
                partNames.push($(this)[0].innerText);
            });
            $this.gtmConfig[groupName] = partNames.join('|');
        },

        /**
         * Update platform configuration
         *
         * @param $this
         * @param {number} groupId
         */
        _updatePlatform: function ($this, groupId) {
            const $platformConfigurationGroup = $this.$('.jsPlatformConfiguration').find('[data-group="' + groupId + '"]');
            if ($platformConfigurationGroup.length) {
                const $specsGroup = $this.$('.hp-configurator-box-groups').find('.hp-content-group-' + groupId);
                if ($specsGroup.hasClass('uk-hidden')) {
                    $platformConfigurationGroup.addClass('uk-flex-last')
                        .children().attr('hidden', 'hidden')
                } else {
                    $platformConfigurationGroup.removeClass('uk-flex-last')
                        .children().removeAttr('hidden');
                    const $parts = $specsGroup.find('[class^="hp-box-part"]'),
                          $platformConfigurationParts = $platformConfigurationGroup.find('.jsPlatformConfigurationGroupParts');
                    $platformConfigurationParts.html('');
                    $parts.each(function () {
                        $platformConfigurationParts.append('<div>' + $(this).text() + '</div>');
                    })
                }
            }
        },

        /**
         * Update days to build
         *
         * @param $this
         */
        _updateDaysToBuild: function ($this) {
            const $availabilityWrapper = $this.$('.jsConfigurationAvailability');
            if ($availabilityWrapper.length) {
                const defaultDays = $availabilityWrapper.data('default-days'),
                      $minDays = $availabilityWrapper.find('.jsMinDaysToBuild'),
                      $maxDays = $availabilityWrapper.find('.jsMaxDaysToBuild'),
                      $checkedParts = $this.$('.hp-part-checked'),
                      preorderCorrectionDays = {
                          min: 3,
                          max: 4
                      }

                let extraDays = 0,
                    preorderCorrection = false;
                $checkedParts.each(function (index, part) {
                    const $part = $(this);

                    if ($part.data('detached') === true) {
                        return true;
                    }

                    const $group = $part.closest('.sub-group-content'),
                          divideByAvailability = Boolean($group.find('.jsOnlyInstock').length);

                    if (divideByAvailability) {
                        const $checkedOption = $part.find('.hp-option-checked');
                        if (
                            ($checkedOption.length && $checkedOption.data('instock') === false) ||
                            $part.data('instock') === false
                        ) {
                            preorderCorrection = true
                        }
                    }

                    if (part.dataset.extraDays) {
                        extraDays = Math.max(parseInt(part.dataset.extraDays), extraDays);
                    }
                });

                if (extraDays < 4 && preorderCorrection) {
                    $minDays.text(defaultDays.min + preorderCorrectionDays.min);
                    $maxDays.text(defaultDays.max + preorderCorrectionDays.max);
                } else {
                    $minDays.text(defaultDays.min + extraDays);
                    $maxDays.text(defaultDays.max + extraDays);
                }
            }
        },

        /**
         * Set case image in the right box.
         *
         * @param $this
         */
        _setBoxCaseImg: function ($this) {
            const $boxCaseImg = $('.jsBoxCaseImg'),
                  caseGroups = $boxCaseImg.data('group');
            if (typeof caseGroups === 'object') {
                caseGroups.some(function (caseGroupId) {
                    const $group = $this.$groups.filter('[data-id="' + caseGroupId + '"]'),
                          $part = $group.find('.hp-part-wrapper').filter('.hp-part-checked');

                    if ($part.length === 1) {
                        const $partOptions = $part.find('.hp-part-options');
                        if ($partOptions.length) {
                            const $checkedOption = $partOptions.find('input[type=radio]:checked').closest('.hp-option');
                            if ($checkedOption.length === 1) {
                                const imgUrl = $checkedOption.data('image');
                                $boxCaseImg.find('img').attr('src', imgUrl)
                                return true;
                            }
                        } else {
                            const imgUrl = $part.find('.hp-configurator-part').data('image');
                            $boxCaseImg.find('img').attr('src', imgUrl)
                            return true;
                        }
                    }
                });
            }
        },

        /**
         * Get part right box href.
         *
         * @param $part
         * @returns {string}
         */
        _getPartBoxHref: function ($part) {
            const $partOptions   = $part.find('.hp-part-options'),
                  $checkedOption = $partOptions.find('input[type=radio]:checked').closest('.hp-option');

            let partUrl = $part.data('url');

            if ($checkedOption.length && $part.data('reloaded') != 1) {
                partUrl = $checkedOption.find('.jsLoadIframe').attr('href');
            }

            return typeof partUrl !== 'undefined' ? partUrl : false;
        },

        /**
         * Get part right box html.
         *
         * @param $this
         * @param $part
         *
         * @returns {string}
         */
        _getPartBoxHtml: function ($this, $part) {
            const $partOptions  = $part.find('.hp-part-options'),
                  $partQuantity = $part.find('.jsPartQuantity'),
                  partUrl       = $this._getPartBoxHref($part),
                  isReloaded    = $part.data('reloaded');

            let option   = '',
                quantity = '';

            if ($partQuantity.length) {
                quantity =
                    '<span class="jsBoxPartQuantity">' +
                        $this._getQuantityBoxHtml($part) +
                    '</span>';
            }

            if ($partOptions.length) {
                const $checkedOption = $partOptions.find('input[type=radio]:checked').closest('.hp-option');

                if ($checkedOption.length && isReloaded != '1') {
                    option =
                        '<span class="part-option uk-link-muted uk-text-nowrap">' +
                            $this._getOptionBoxHtml($part) +
                        '</span>';
                }
            }

            let partLink = '';

            if (!partUrl) {
                partLink =
                    '<span class="uk-text-muted">' +
                        $part.data('name') +
                        option +
                    '</span>';
            } else {
                partLink =
                    '<a class="uk-link-muted jsLoadIframe" href="' + partUrl + '">' +
                        $part.data('name') +
                        option +
                    '</a>';
            }

            const advantages = $part.data('advantages');

            return '<li class="hp-box-part-' + $part.data('id') + '"' + (advantages ? ' data-advantages=\'' + JSON.stringify(advantages) + '\'' : '') + '>' +
                        quantity +
                        partLink +
                    '</li>';
        },

        /**
         * Get group right box html.
         *
         * @param $this
         * @param part
         *
         * @returns {string}
         */
        _getGroupBoxHtml: function ($this, $part) {
            const groupName = $part.closest('.sub-group-content').find('.sub-group-description').find('.uk-h3').text();

            return '<span class="hp-content-group-title">' + groupName + '</span>' +
                '<ul id="hp-box-group-' + $part.data('group') + '" class="uk-list uk-margin-remove-top hp-sub-group-items">' +
                    $this._getPartBoxHtml($this, $part) +
                '</ul>';
        },

        /**
         * Get option right box html.
         *
         * @param part
         *
         * @returns {string}
         */
        _getOptionBoxHtml: function ($part) {
            const $partOptions = $part.find('.hp-part-options');

            let option = '';

            if ($partOptions.length) {
                const $checkedOption = $partOptions.find('input[type=radio]:checked').closest('.hp-option'),
                      optionName     = $checkedOption.data('name');

                if ($checkedOption.length) {
                    option = ' ' + optionName;
                }
            }

            return option;
        },

        /**
         * Get quantity right box html.
         *
         * @param $part
         *
         * @returns {string}
         */
        _getQuantityBoxHtml: function ($part) {
            const $partQuantity = $part.find('.jsPartQuantity');

            let quantity = '';

            if ($partQuantity.length && $partQuantity.val() > 1) {
                quantity = $partQuantity.val() + ' x ';
            }

            return quantity;
        },

        /**
         * Price difference format.
         *
         * @param $this
         * @param price
         *
         * @returns {string}
         */
        _priceDiffFormat: function ($this, price) {
            const moneyConfig = window.Joomla.getOptions('moneyConfig') || {
                'symbol': '₽',
                'format_negative': '-%v %s'
            };

            const sign = price > 0 ? '+' : '-';

            const format = moneyConfig.format_negative
                .replace('-', sign)
                .replace('%v', $this._priceFormat(Math.abs(price)))
                .replace('%s', moneyConfig.symbol);

            return format;
        },

        /**
         * Price format.
         *
         * @param price
         *
         * @returns {string}
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
         * Reset configuration.
         *
         * @param $this
         */
        _resetConfig: function ($this) {
            $this._openLoader();

            $this.ajax({
                'url'      : '/index.php',
                'dataType' : 'json',
                'data'     : {
                    'task'             : 'configurator.reset',
                    'context'          : $this.getOption('context'),
                    'productId'        : $this.getOption('productId'),
                    'configurationId'  : $this._getConfigurationId($this)
                },
                'success' : function (data) {
                    $this._hideLoader();
                    if (data.result === 'success') {
                        $this.$('.hp-part-checked').each(function(){
                            const $part = $(this);
                            $this._unsetPart($this, $part);
                            $this._updateRightBox($this, $part, 'remove');
                        });

                        const parts = data.parts;
                        const $configParts = $this.$('.hp-part-wrapper');
                        for (let i = 0; i < parts.length; i++) {
                            const $part = $configParts.filter('[data-id=' + parts[i].partId + ']');

                            if ($this._getPartQuantity($part) !== parts[i].quantity) {
                                $part.find('.jsPartQuantity').data('default', parts[i].quantity);
                                $this._resetQuantity($part);
                            }

                            if (parts[i].option !== 0 ) {
                                $this._selectPart($this, $part, parts[i].option);
                            } else {
                                $this._selectPart($this, $part);
                            }

                            const $group = $part.closest('.sub-group-content');

                            $this._setGroupPartImage($this, $part);
                            $this._updateRightBox($this, $part, 'set');
                            $this._updateGroupPrice($this, $group);

                            //update filters
                            const $filter = $group.find('[data-filter]'),
                                  filter = $filter.data('filter');

                            if (typeof filter !== 'undefined') {
                                let partFilterValue = $part.data(filter);
                                if (typeof partFilterValue !== 'undefined') {
                                    partFilterValue = partFilterValue.toString().split(' ')[0];
                                    const $filterButtons = $filter.find('[uk-filter-control]'),
                                          $filterButton = $filterButtons.filter('[uk-filter-control="[data-' + filter + '~=\'' + partFilterValue + '\']"]');
                                    $filterButton.addClass('uk-active').siblings().removeClass('uk-active');
                                    $this._filterGroup($filterButton, $group);
                                }
                            }
                        }
                        $this._updateTotalPrice($this);
                        $this._setUnsavedState($this, false);

                        if ($this.isInCart) {
                            $this.$('.jsCartButtons').addClass('hp-element-in-cart');
                        }
                    }
                }
            });
        },

        /**
         * Allow to reset config.
         *
         * @param $this
         */
        _allowResetConfig: function ($this) {
            $this.$('.jsConfigReset').removeClass('uk-disabled');
        },

        /**
         * Disallow to reset config.
         *
         * @param $this
         */
        _disallowResetConfig: function ($this) {
            $this.$('.jsConfigReset').addClass('uk-disabled');
        },

        /**
         * Allow to save config.
         *
         * @param $this
         */
        _allowSaveConfig: function ($this) {
            $this.$('.jsSaveConfig').removeClass('uk-disabled');
            $this.isUnsaved = true;
        },

        /**
         * Disallow to save config.
         *
         * @param $this
         */
        _disallowSaveConfig: function ($this) {
            $this.$('.jsSaveConfig').addClass('uk-disabled');
            $this.isUnsaved = false;
        },

        /**
         * Set config totel price.
         *
         * @param $this
         * @param {number} price
         */
        _setTotalPrice: function ($this, price) {
            $this._updatePriceElement($this, $this.$('.jsItemPrice'), price);

            const vat = $this.getOption('vat'),
                  vatValue = Math.round((price / (100 + vat)) * vat);

            $this._updatePriceElement($this, $this.$('.jsItemVat'), vatValue);
        },

        /**
         * Get config totel price.
         *
         * @param $this
         *
         * @returns {number}
         */
        _getTotalPrice: function ($this) {
            return $this.$('.jsItemPrice').eq(0).find('.simpleType-value').attr('content');
        },

        /**
         * Get configuration id.
         *
         * @param $this
         *
         * @returns {number}
         */
        _getConfigurationId: function ($this) {
            return parseInt($this.$('.jsSavedConfigurationId').val());
        },

        /**
         * Save configuration.
         *
         * @param $this
         * @param {function=} successCallback
         * @param {boolean=} supressNotification
         * @param {boolean=} forceCreate forced create new configuration
         */
        _saveConfig: function ($this, successCallback, supressNotification, forceCreate) {
            const formData = $this.$('.jsConfiguratorForm').serialize(),
                  configId = $this._getConfigurationId($this),
                  action = forceCreate || configId === 0 ? 'new' : 'update';

            const requestData = {
                'rand'      : JBZoo.rand(100, 999),
                'option'    : 'com_hyperpc',
                'tmpl'      : 'component',
                'format'    : 'raw',
                'productId' : $this.getOption('productId'),
                'price'     : $this._getTotalPrice($this),
                'context'   : $this.getOption('context')
            }

            if (action === 'update') {
                requestData.task = 'configurator.update';
                requestData.saved_configuration = configId;
            } else {
                requestData.task = 'configurator.create';
            }

            $this.isUnsaved = true;

            $.ajax({
                'url'      : '/index.php?' + formData,
                'dataType' : 'json',
                'type'     : 'POST',
                'data'     : requestData
            })
            .done(function (data) {
                if (data.result === 'success') {
                    let notificationTimeout = 4000;
                    switch (action) {
                        case 'update':
                            if (data.savedConfiguration !== configId) {
                                $this._setSavedConfiguration($this, data.savedConfiguration);
                                notificationTimeout = 8000;
                            }
                            break;
                        case 'new':
                            $this._setSavedConfiguration($this, data.savedConfiguration);
                            notificationTimeout = 8000;
                            break;
                    }
                    $this._setUnsavedState($this, false);

                    if (!supressNotification) {
                        UIkit.notification(data.msg, {status:'success', timeout: notificationTimeout})
                    }

                    if ($this.isInCart && action === 'update' && data.savedConfiguration === configId) {
                        $this.$('.jsCartButtons').addClass('hp-element-in-cart');
                    }

                    if (data.cartItems) {
                        const items = data.cartItems.items || [],
                              count = data.cartItems.count || 0;

                        document.dispatchEvent(new CustomEvent('hpcartupdated', {
                            detail: {
                                items: items,
                                count: count
                            }
                        }));
            
                        localStorage.setItem('hp_cart_items_count', count);
                        localStorage.setItem('hp_cart_items', JSON.stringify(items));
                    }

                    if (typeof successCallback === 'function') {
                        successCallback();
                    }

                    // track configuration save
                    if (action === 'new' || data.savedConfiguration !== configId) {
                        window.dataLayer.push({
                            'event'    : 'hpTrackedAction',
                            'hpAction' : 'configurationSaved'
                        });
                    }
                } else {
                    // error
                    UIkit.notification(data.msg, {status:'danger', timeout:0});
                }
            })
            .fail(function (xhr, status) {
                // error
                UIkit.notification($this.getOption('msgTryAgain'), {status:'danger', timeout:0});
            })
            .always(function () {
                $this._unlockButton($('.jsSaveConfig, .jsSaveOnLeave'));
            });
        },

        /**
         * Add config to cart.
         *
         * @param $this
         */
        _addToCart: function ($this) {
            const $cartButton = $this.$('.jsAddToCart'),
                  configId = $this._getConfigurationId($this),
                  isMoysklad = $this.getOption('context') === 'moysklad';

            const args = {
                'quantity' : 1,
                'id'       : $cartButton.data('id'),
                'type'     : isMoysklad ? 'position' : 'product',
            };

            if (configId > 0) {
                args.savedConfiguration = configId;
                args.type = 'configuration';
            }

            $.ajax({
                'url'      : '/index.php',
                'type'     : 'POST',
                'dataType' : 'json',
                'data'     : {
                    'rand'       : JBZoo.rand(100, 999),
                    'option'     : 'com_hyperpc',
                    'format'     : 'raw',
                    'task'       : 'cart.addToCart',
                    'tmpl'       : 'component',
                    'args'       : args,
                    'isMoysklad' : isMoysklad ? 'true' : 'false'
                }
            })
            .done(function (data) {
                if (!data.result) {
                    UIkit.notification(data.msg, {status:'danger', timeout:0});
                    return;
                }

                document.dispatchEvent(new CustomEvent('hpcartupdated', {
                    detail: {
                        items: data.items,
                        count: data.count
                    }
                }));
    
                localStorage.setItem('hp_cart_items_count', data.count);
                localStorage.setItem('hp_cart_items', JSON.stringify(data.items));

                $this.$('.jsCartButtons').addClass('hp-element-in-cart');

                if ($this._getUserId() !== 0) {
                    $this._disallowSaveConfig($this);
                }

                $this.isInCart = true;

                if (typeof dataLayer !== 'undefined') {
                    window.items = window.items || {};
                    const objectKey = Object.keys(window.items)[0];

                    if (typeof window.items[objectKey] === 'object') {
                        window.items[objectKey].price = $this._getTotalPrice($this);

                        const ecProduct = $.extend({}, window.items[objectKey]);

                        const ga4Item = {
                            'item_name'      : ecProduct.name,
                            'item_id'        : ecProduct.id,
                            'price'          : ecProduct.price,
                            'item_brand'     : ecProduct.brand || '',
                            'item_list_name' : ecProduct.list_name || '',
                            'item_list_id'   : ecProduct.list_id || '',
                            'quantity'       : ecProduct.quantity
                        };

                        const categories = ecProduct.categories.slice().reverse();
                        for (let i = 0; i < categories.length; i++) {
                            const propKey = 'item_category' + (i > 0 ? (i + 1) : '');
                            ga4Item[propKey] = categories[i];
                        }

                        dataLayer.push({ecommerce: null});
                        dataLayer.push({
                            'event': 'add_to_cart',
                            'ecommerce' : {
                                'currency': ecProduct.currency,
                                'items': [ga4Item]
                            }
                        });
                    }

                    $this.gtmConfig['ConfigID'] = configId;
                    dataLayer.push($this.gtmConfig);
                }
            })
            .fail(function (error) {
                const msg = error.msg || $this.getOption('msgTryAgain');
                UIkit.notification(msg, {status:'danger',timeout:0});
            })
            .always(function () {
                $this._unlockButton($cartButton);
            });
        },

        /**
         * Setup saved configuration id in hidden field.
         *
         * @param $this
         * @param savedConfiguration
         */
        _setSavedConfiguration: function ($this, savedConfiguration) {
            $this.$('.jsSavedConfigurationId').val(savedConfiguration);
            $this._updateLocationHref($this);
            $('.jsConfigNumber').html($this._formatId(savedConfiguration));
            $this.$('.jsConfigNumberWrapper').removeAttr('hidden');
            $this._updateCreditCalculationConfigId($this, savedConfiguration);

            const itemKey = $this._getItemKey($this);
            $this.$('.jsCartButtons').data('itemkey', itemKey).attr('data-itemkey', itemKey);
            $this._checkCartState($this);
        },

        /**
         * Format int.
         *
         * @param id
         * @returns {string}
         */
        _formatId: function (id) {
            if (parseInt(id) <= 0) {
                return '______'
            } else {
                return ('000000' + id).slice(-7);
            }
        },

        /**
         * Get configuration number text
         *
         * @param $this
         * @param {number} number
         * @returns {string}
         */
        _getConfigurationNumberText: function ($this, number) {
            return $this.getOption('langNum') + number;
        },

        /**
         * Update location href by personal configuration id.
         *
         * @param $this
         */
        _updateLocationHref: function ($this) {
            let baseUrl = location.href;
            const savedConfig = $this.$('.jsSavedConfigurationId').val(),
                  regexp      = new RegExp('config-' + savedConfig + '$', 'gi');

            if (!baseUrl.match(regexp)) { // url changed
                baseUrl = baseUrl.replace(/-[а-яА-ЯёЁa-zA-Z0-9]+$/, '');
                const historyState = history.state || {};
                history.replaceState(historyState, '', baseUrl + '-' + savedConfig);
                //$('.sharer').attr('data-url', location.href); not used
            }
        },

        /**
         * Show modal with full specification.
         *
         * @param $this
         */
        _showSpecsModal: function ($this) {
            $('.jsFullSpecs').html($this._getSpecsHtml($this));
            UIkit.modal($('#full-specs')).show();
        },

        /**
         * Get full specification html.
         *
         * @param $this
         *
         * @returns {string}
         */
        _getSpecsHtml: function ($this) {
            const specsObj = $this._getSpecsObject($this);
            let html = '<table class="uk-table uk-table-divider tm-table-specs tm-table-specs--icons">';

            specsObj.forEach(function (rootGroup) {
                html += '<tr class="tm-table-specs__group-head">' +
                            '<th colspan="2">' +
                                '<span class="uk-h3">' +
                                    rootGroup.title +
                                '</span>' +
                            '</th>' +
                        '</tr>';

                rootGroup.subGroups.forEach(function (subGroup) {
                    const $partsHtml = $(subGroup.parts.replaceAll('uk-link-muted', 'uk-link-text')).filter('li');

                    let itemsHtml = '';
                    $partsHtml.each(function () {
                        const $part = $(this),
                              advantages = $part.data('advantages');

                        if (advantages) {
                            const advantagesHtml = [];
                            advantages.forEach(function (advantage) {
                                advantagesHtml.push('<li>' + advantage + '</li>');
                            });

                            $part.append(
                                '<ul class="uk-list uk-list-collapse uk-text-muted uk-text-small uk-margin-remove">' +
                                    advantagesHtml.join('\n') +
                                '</ul>'
                            )
                        }

                        itemsHtml += '<li>' + $part.html() + '</li>';
                    });

                    html += '<tr>' +
                                '<th class="tm-table-specs__property-name">' +
                                    '<span class="uk-margin-small-right" uk-icon="icon: hp-' + subGroup.alias + '"></span>' +
                                    subGroup.title +
                                '</th>'+
                                '<td>' +
                                    '<ul class="uk-list">' +
                                        itemsHtml +
                                    '</ul>' +
                                '</td>' +
                            '</tr>';
                });

            });

            html += '</table>';

            return html;
        },

        /**
         * Get full specification object.
         *
         * @param $this
         *
         * @returns {Array}
         */
        _getSpecsObject: function ($this) {
            const $specsHtml = $this.$('.hp-configurator-box-groups'),
                  specsObj = [];
            $specsHtml.find('.hp-root-group').each(function () {
                const $group = $(this);
                if ($group.hasClass('uk-hidden')) {
                    return true;
                }

                const subGroups = [];
                $group.find('.hp-sub-group > li').each(function () {
                    const $subGroup = $(this);
                    if ($subGroup.hasClass('uk-hidden')) {
                        return true;
                    }

                    subGroups.push({
                        'title' : $subGroup.find('.hp-content-group-title').text().trim(),
                        'alias' : $subGroup.data('alias'),
                        'parts' : $subGroup.find('.hp-sub-group-items').html().trim()
                    });

                });

                if (subGroups.length) {
                    specsObj.push({
                        'title'     : $group.find('.hp-root-group-title').text(),
                        'subGroups' : subGroups
                    });
                }
            });

            return specsObj;
        },

        /**
         * Handle leave configurator
         * 
         * @param $this
         * @param {string} href
         */
        _handleLeaveConfigurator: function ($this, href) {
            $this._showLeaveDialog($this, $this.getOption('leaveConfirmMsg'), href, $this.getOption('txtLeave'));
        },

        /**
         * Handle change comlectation
         * 
         * @param $this
         * @param {string} href
         */
        _handleChangeComlectation: function ($this, href) {
            $this._showLeaveDialog($this, $this.getOption('changeComlectationMsg'), href);
        },

        /**
         * Show leave dialog
         * 
         * @param $this
         * @param {string} message
         * @param {string} href
         */
        _showLeaveDialog: function ($this, message, href, leaveBtn) {
            const btnCommonClass = 'uk-button uk-button-small uk-button-normal@s',
                  leaveBtnText   = leaveBtn ? leaveBtn : $this.getOption('txtContinue');
            const $dialogHtml =
                $('<div class="jsModalLeaveConfigurator">' +
                    '<div class="uk-modal-dialog">' +
                        '<button class="uk-modal-close-default" type="button" uk-close></button>' +
                        '<div class="uk-modal-body tm-background-gray-5">' +
                            '<p>' + message + '</p>' +
                        '</div>' +
                        '<div class="uk-modal-footer uk-text-right tm-background-gray-5">' +
                            '<span class="' + btnCommonClass + ' uk-button-default jsGoToProduct" data-href="' + href + '">' +
                                leaveBtnText +
                            '</span> ' +
                            '<span class="' + btnCommonClass + ' uk-button-primary jsSaveOnLeave" data-href="' + href + '">' +
                                $this.getOption('txtSave') +
                            '</span>' +
                        '</div>' +
                    '</div>' +
                '</div>');

            window.removeEventListener('beforeunload', $this._onBeforeUnloadCallback);

            UIkit.modal($dialogHtml).show();
            UIkit.util.on('.jsModalLeaveConfigurator', 'hidden', function () {
                window.addEventListener('beforeunload', $this._onBeforeUnloadCallback);
                $this._setCurrentComplectation($this);
                $(this).remove();
            });
        },

        /**
         * Set current complectation.
         *
         * @param $this
         */
        _setCurrentComplectation: function ($this) {
            const $complectationsGroup = $this.$('#complectation');
            if ($complectationsGroup.length) {
                $complectationsGroup
                    .find('.hp-configurator-complectation--current')
                    .find('[type="radio"]').prop('checked', 'checked');
            }
        },

        /**
         * Set configuration unsaved state.
         *
         * @param $this
         * @param {boolean} state
         */
        _setUnsavedState: function ($this, state) {
            if (state === $this.isUnsaved) {
                return;
            }

            if (state === true) {
                $this._allowResetConfig($this);
                $this._allowSaveConfig($this);
                $this._unlockButton($this.$('.jsAddToCart'));
                $this.$('.jsCartButtons').removeClass('hp-element-in-cart');
                $this.$('.jsConfigNumber').addClass('isUnsaved');
            } else if (state === false) {
                $this._disallowResetConfig($this);
                if ($this._getUserId() !== 0) {
                    $this._disallowSaveConfig($this);
                }
                $this.$('.jsConfigNumber').removeClass('isUnsaved');
            }

            $this.isUnsaved = state;

            if (state) {
                window.addEventListener('beforeunload', $this._onBeforeUnloadCallback);
            } else {
                window.removeEventListener('beforeunload', $this._onBeforeUnloadCallback);
            }

            $this._checkAvailability($this);
        },

        /**
         * onBeforeUnload event callback
         *
         * @param e
         */
        _onBeforeUnloadCallback: function(e) {
            e.preventDefault();
            e.returnValue = '';
        },

        /**
         * Get user id.
         * 
         * @returns {number}
         */
        _getUserId: function () {
            return window.user ? window.user.id : 0;
        },

        /**
         * Set message to login modal
         *
         * @param $this
         * @param {string} message
         */
        _setLoginMessage: function ($this, message) {
            if (message.length > 0) {
                $('.jsAuthFirstStepEmail, .jsAuthFirstStepMobile').prepend(
                    '<div class="jsConfigAuthMessage uk-alert uk-alert-primary">' +
                        message +
                    '</div>');
            } else {
                $('.jsConfigAuthMessage').remove();
            }
        },

        /**
         * Handle manual save configuration
         *
         * @param $this
         * @param successCallback
         */
        _handleManualSave: function ($this, successCallback) {
            const supressNotification = false, // show all notifications
                  currentConfigId = $this._getConfigurationId($this);
            if ($this._getUserId() !== 0) {
                if ($this.isInCart && currentConfigId > 0) {
                    $this._handleUpdateConfigInCart($this, function (forceCreate) {
                        $this._saveConfig($this, successCallback, supressNotification, forceCreate)
                    });
                } else {
                    $this._saveConfig($this, successCallback, supressNotification);
                }
            } else {
                $this._setLoginMessage($this, $this.getOption('langLoginBeforeSaveAlert'))
                UIkit.modal('#login-form-modal').show();
                if (!$this._handleManualSave.userLoginHandlerSet) {
                    $this._handleManualSave.userLoginHandlerSet = true;
                    $(document).one('hpuserloggedin', function (e, data) {
                        if ($this.isInCart && currentConfigId > 0) {
                            $this._handleUpdateConfigInCart($this, function (forceCreate) {
                                $this._saveConfig($this, successCallback, supressNotification, forceCreate)
                            });
                        } else {
                            $this._saveConfig($this, successCallback, supressNotification);
                        }
                    });
                }
            }
        },

        /**
         * Handle update incart config
         * 
         * @param $this
         */
        _handleUpdateConfigInCart: function ($this, callback) {
            if ('Promise' in window) {
                const configInCartQuestion = $this._showConfigInCartModal($this);

                configInCartQuestion.then(function (createNew) {
                    if (typeof callback === 'function') {
                        callback(createNew);
                    }
                }, function () {
                    $this._unlockButton($('.jsSaveConfig, .jsSaveOnLeave, .jsAddToCart'));
                });
            } else { // Promises is not supported
                if (typeof callback === 'function') {
                    callback(createNew);
                }
            }
        },

        /**
         * Show configuration in cart modal
         * 
         * @param $this
         * 
         * @returns {Promise}
         */
        _showConfigInCartModal: function ($this) {
            return new Promise(function (resolve, reject) {
                const btnCommonClass = 'uk-button uk-button-small',
                      modalTitle = $this.getOption('langConfigInCartModalTitle').replace(/%s/, $this._getConfigurationId($this) ? $this._getConfigurationNumberText($this, $this._getConfigurationId($this)) : '');
                const $dialogHtml =
                    $('<div class="jsConfigInCartModal" uk-modal="bg-close: false">' +
                        '<div class="uk-modal-dialog tm-card-bordered tm-background-gray-15">' +
                            '<button class="uk-modal-close-default" type="button" uk-close></button>' +
                            '<div class="uk-modal-body">' +
                                '<div>' + modalTitle + '</div>' +
                                '<div class="uk-text-muted">' + 
                                    $this.getOption('langConfigInCartModalSub') +
                                '</div>' +
                            '</div>' +
                            '<div class="uk-modal-footer tm-background-gray-15 tm-modal-footer-controls-full">' +
                                '<span class="' + btnCommonClass + ' jsUpdateCartConfig">' + $this.getOption('langUpdateInCart') + '</span>' +
                                '<span class="' + btnCommonClass + ' jsSaveNewConfig">' + $this.getOption('langSaveNew') + '</span>' +
                            '</div>' +
                        '</div>' +
                    '</div>');

                if ($('.jsConfigInCartModal').length > 0) {
                    UIkit.modal('.jsConfigInCartModal').show();
                } else {
                    UIkit.modal($dialogHtml).show();
                }

                $this.$body.on('click', '.jsUpdateCartConfig, .jsSaveNewConfig', function () {
                    UIkit.modal('.jsConfigInCartModal').hide();

                    const saveNewConfig = $(this).is('.jsSaveNewConfig');
                    resolve(saveNewConfig);
                });

                UIkit.util.on('.jsConfigInCartModal', 'hidden', function () {
                    reject();
                });

            });
        },

        /**
         * Lock button during ajax request in progress.
         *
         * @param $button - jQuery object
         */
        _lockButton: function ($button) {
            $button.attr('disabled', 'disabled')
                   .prepend('<span uk-spinner="ratio: 0.67"></span>')
                   .find('[uk-icon]').attr('hidden', 'hidden')
        },

        /**
         * Unlock button after ajax request is complete.
         *
         * @param $button - jQuery object
         */
        _unlockButton: function ($button) {
            $button.removeAttr('disabled')
                   .find('[uk-icon]').removeAttr('hidden');
            $button.find('[uk-spinner]').remove();
        },

        /**
         * requestIdleCallback polyfill
         *
         * @param {function} callback
         */
        _requestIdleCallback: function (callback) {
            if ('requestIdleCallback' in window) {
                requestIdleCallback(callback);
            } else {
                setTimeout(callback, 1);
            }
        },

        /**
         * Filter group
         *
         * @param $button
         * @param $group
         * @param $parts
         */
        _filterGroup: function ($button, $group, $parts) {
            const filter = $button.attr('uk-filter-control');

            $group = $group || $button.closest('.sub-group-content'),
            $parts = $parts || $group.find('.hp-part-wrapper');

            if (filter === '') {
                $parts.css('display', '');
            } else {
                $parts.filter(filter).css('display', '');
                $parts.not(filter).css('display', 'none');
            }
        },

        /**
         * Check group filters
         *
         * @param $this
         * @param $group
         * @param $parts
         */
        _checkGroupFilters: function ($this, $group, $parts) {
            const $activeFilter = $group.find('[uk-filter-control]').filter('.uk-active');
            if ($activeFilter.length === 1) {
                $this._filterGroup($activeFilter, $group, $parts);
            } else {
                $group.find('.hp-filter-all').addClass('uk-active');
            }
        },

        /**
         * Update simpletype price element
         * 
         * @param $this
         * @param $el
         * @param {number} value
         */
        _updatePriceElement: function ($this, $el, value) {
            $el.find('.simpleType-value')
                .text($this._priceFormat(value))
                .attr('content', value);
        },

        /**
         * Hide excess filter buttons in group
         * 
         * @param $this
         * @param $group
         * @param $parts
         */
         _actuailzeFilterButtons: function ($this, $group, $parts) {
            const $groupFilter = $group.find('.hp-group-filter'),
                  filter = $groupFilter.data('filter');

            if (!filter) {
                return;
            }

            const $filterButtons = $groupFilter.find('.jsFilterButton').attr('hidden', 'hidden'),
                  $availableParts = $parts.not('.hp-part-wrapper--disabled');
            let availableFilters = [];
            $availableParts.each(function () {
                const partValues = $(this).data(filter);
                if (typeof partValues !== 'undefined') {
                    availableFilters = availableFilters.concat(partValues.toString().split(' '));
                }
            });

            availableFilters = availableFilters.filter(function (value, index, self) { // array unique
                return self.indexOf(value) === index;
            });

            $filterButtons.attr('hidden', 'hidden');
            availableFilters.forEach(function (value) {
                $filterButtons.filter('[uk-filter-control*="data-' + filter + '~=\'' + value + '\'"]').removeAttr('hidden');
            });

            const $activeButton = $filterButtons.filter('.uk-active');
            if ($activeButton.length /* && $activeButton.is('[hidden].uk-active') */) { // uncomment if the "show incompatible" option will be made
                const $checkedParts = $availableParts.filter('.hp-part-checked');
                if ($checkedParts.length === 1) {
                    const partValues = $checkedParts.data(filter);
                    if (typeof partValues !== 'undefined') {
                        const $activeButton = $filterButtons.filter('[uk-filter-control*="data-' + filter + '~=\'' + partValues.toString().split(' ')[0] + '\'"]');
                        if ($activeButton.length) {
                            $activeButton.addClass('uk-active').siblings().removeClass('uk-active');
                            $this._filterGroup($activeButton, $group, $parts);
                            return;
                        }
                    }
                }

                const $buttonFilterAll = $groupFilter.find('.hp-filter-all');
                $buttonFilterAll.addClass('uk-active').siblings().removeClass('uk-active');
                $this._filterGroup($buttonFilterAll, $group, $parts);
            }
        },

        /**
         * Get part name from jQuery object
         *
         * @param {object} $part jQuery object
         *
         * @return {string}
         */
        _getPartName: function ($part) {
            return $part.find('.hp-conf-part__name').text();
        },

        /**
         * Get compatibility checkData instance
         *
         * @returns {CompatibilityCheckData}
         */
        _getCompatibilityCheckDataInstance: function () {
            return {
                replacements: new Set(),
                removements: new Set(),
                compatibleParts: new Set(),
                incompatibleParts: new Set(),
                incompatiblePairs: []
            };
        },

        /**
         * Update comptible/incompatible parts state
         *
         * @param $this
         * @param {CompatibilityCheckData} compatibilityData
         */
        _updateCompatibilities: function ($this, compatibilityData) {
            const tooltipParams = {
                offset: 0,
                pos: 'top-left',
                delay: 300
            };

            const incompatibleClass = 'hp-part-incompatible';

            compatibilityData.compatibleParts.forEach((part) => {
                $(part).removeClass(incompatibleClass);
                UIkit.tooltip(part).$destroy();
            });

            compatibilityData.incompatibleParts.forEach((part) => {
                const $part = $(part),
                      incompatibleWith = $part.data('incompatibleWith');
                $part
                    .addClass(incompatibleClass)
                    .attr('data-title', incompatibleWith ?
                        $this.getOption('langCompatibilityIncompatibleWith') + '<br>' + incompatibleWith :
                        $this.getOption('langCompatibilityIncompatibleWithCurrentConfig')
                    );
                UIkit.tooltip(part, tooltipParams);
            });
        },

        /**
         * Replace/remove incompatible parts
         *
         * @param $this
         * @param {CompatibilityCheckData} compatibilityData
         */
        _replaceIncompatible: function ($this, compatibilityData) {
            compatibilityData.removements.forEach((part) => {
                const $part = $(part),
                      $group = $part.closest('.sub-group-content');
                $this._unsetPart($this, $part);
                $this._requestIdleCallback(function () {
                    $this._unsetGroupPartImage($this, $part);
                    $this._updateRightBox($this, $part, 'remove');
                });
                $this._updateGroupPrice($this, $group);
            });

            compatibilityData.replacements.forEach((part) => {
                const $part = $(part),
                      $group = $part.closest('.sub-group-content'),
                      $lastChecked = $group.find('.hp-part-checked');

                $this._unsetPart($this, $lastChecked);

                $this._selectPart($this, $part);
                $this._actuailzeFilterButtons($this, $group, $group.find('.hp-part-wrapper'));

                $this._requestIdleCallback(function () {
                    $this._setGroupPartImage($this, $part);
                    $this._updateRightBox($this, $part, 'set');
                });

                $this._updateGroupPrice($this, $group);
            });
        },

        /**
         * Check part compatibilities
         *
         * @param $this
         * @param $part
         * @param {boolean} silentMode
         *
         * @returns {Promise}
         */
        _checkCompatibilities: function ($this, $part, silentMode) {
            /** @type {CompatibilityCheckData}  */
            const compatibilityData = $this._getPartCompatibilityData($this, $part);

            if (silentMode === true) {
                $this._updateCompatibilities($this, compatibilityData);
                $this._replaceIncompatible($this, compatibilityData);
                compatibilityData.incompatiblePairs.forEach((pair) => {
                    console.warn('Incompatible parts: ' + pair.map((part) => $this._getPartName($(part))).join(' / '));
                });

                return (new Promise((resolve) => resolve()));
            }

            // If no replacements or removements just update compatibles
             if (compatibilityData.replacements.size === 0 && compatibilityData.removements.size === 0) {
                $this._updateCompatibilities($this, compatibilityData);

                return (new Promise((resolve) => resolve()));
            }

            const modalPromise = $this._showCompatibilityModal($this, compatibilityData);
            modalPromise.then(() => {
                $this._updateCompatibilities($this, compatibilityData);
                $this._replaceIncompatible($this, compatibilityData);
            })
            .catch(() => {});

            return modalPromise;
        },

        /**
         * Get part compatibility data
         *
         * @param $this
         * @param $part
         * @param {?CompatibilityCheckData} compatibilityCheckData
         *
         * @returns {CompatibilityCheckData}
         */
        _getPartCompatibilityData: function ($this, $part, compatibilityCheckData) {
            if (typeof compatibilityCheckData === 'undefined') {
                compatibilityCheckData = $this._getCompatibilityCheckDataInstance();
            }

            if (!(typeof $part.data('compatibilities') === 'object')) {
                return compatibilityCheckData;
            }

            const compatibilitiesData = $this.getOption('compatibilitiesData'),
                  groupId = parseInt($part.data('group'));

            for (let key in compatibilitiesData) {
                const compatibility = compatibilitiesData[key];
                if (compatibility.leftGroup === groupId) {
                    const leftCompatibilityValue = $part.data('compatibilities')[compatibility.leftField] || '',
                          $rightGroup = $this.$groups.filter('[data-id="' + compatibility.rightGroup + '"]'),
                          $rightGroupParts = $rightGroup.find('.hp-part-wrapper');

                    if (leftCompatibilityValue === '' || leftCompatibilityValue === 'none') { // reset
                        $rightGroupParts.each(function () {
                            const $rightGroupPart = $(this);
                            compatibilityCheckData.compatibleParts.add($rightGroupPart.get(0));
                            compatibilityCheckData.incompatibleParts.delete($rightGroupPart.get(0));
                        });

                        return compatibilityCheckData;
                    }

                    let $incompatibleParts = null;

                    $rightGroupParts.each(function () {
                        const $rightGroupPart = $(this),
                              partCompatibilities = $rightGroupPart.data('compatibilities') || [],
                              compareValue = partCompatibilities[compatibility.rightField] || '';

                        let result = true;
                        switch (compatibility.type) {
                            case 'string_equal_compatibility':
                                result = leftCompatibilityValue == compareValue;
                                break;
                            case 'numeric_less_or_equal_compatibility':
                                result = (parseInt(leftCompatibilityValue) <= parseInt(compareValue));
                                break;
                            case 'numeric_more_or_equal_compatibility':
                                result = (parseInt(leftCompatibilityValue) >= parseInt(compareValue));
                                break;
                            case 'numeric_equal_compatibility':
                                result = parseInt(leftCompatibilityValue) === parseInt(compareValue);
                                break;
                        }

                        if (!result) {
                            $incompatibleParts = $incompatibleParts ? $incompatibleParts.add($rightGroupPart) : $rightGroupPart;
                        }
                    });

                    const $compatibleParts = $incompatibleParts ? $rightGroupParts.not($incompatibleParts) : $rightGroupParts;
                    $compatibleParts.each(function () {
                        compatibilityCheckData.compatibleParts.add(this);
                    });

                    const replacements = new Set();

                    $incompatibleParts && $incompatibleParts.each(function () {
                        const $incompatiblePart = $(this);

                        $incompatiblePart.data('incompatibleWith', $this._getPartName($part));
                        compatibilityCheckData.incompatibleParts.add($incompatiblePart.get(0));

                        // looking for a replacement for checked part
                        if ($incompatiblePart.is('.hp-part-checked')) {
                            compatibilityCheckData.incompatiblePairs.push([$part.get(0), $incompatiblePart.get(0)]);

                            if ($incompatiblePart.find('[type="checkbox"]').length) { // part input is checkbox
                                compatibilityCheckData.removements.add($incompatiblePart.get(0));
                            } else { // part input is radio
                                let $partsForReplacement = $compatibleParts;
                                if ($part.is('[data-instock]') && $compatibleParts.filter('[data-instock="true"]').length) {
                                    const $compatibleInstock = $compatibleParts.filter('[data-instock="true"]');
                                    if ($compatibleInstock.length) {
                                        $partsForReplacement = $compatibleInstock;
                                    }
                                }

                                if (!$partsForReplacement.length) { // remove if no replacement
                                    compatibilityCheckData.removements.add($incompatiblePart.get(0));
                                }

                                const originalPrice = parseInt($incompatiblePart.data('price'));

                                for (let i = 0; i < $partsForReplacement.length; i++) {
                                    const $partForReplacement = $partsForReplacement.eq(i),
                                          price = parseInt($partForReplacement.data('price'));

                                    if (price === originalPrice) {
                                        replacements.add($partForReplacement);
                                        break;
                                    }

                                    if (price > originalPrice) {
                                        if (i === 0) { // the first element more expensive than the target element
                                            replacements.add($partForReplacement);
                                            break;
                                        }

                                        const currentPriceDiff = price - originalPrice,
                                              $prevPart = $partsForReplacement.eq(i - 1),
                                              prevPriceDiff = originalPrice - parseInt($prevPart.data('price'));

                                        if (currentPriceDiff <= prevPriceDiff) {
                                            replacements.add($partForReplacement);
                                        } else {
                                            replacements.add($prevPart);
                                        }

                                        break;
                                    } else if (i === $partsForReplacement.length - 1) { // the last element
                                        replacements.add($partForReplacement);
                                    }
                                }
                            }
                        }
                    });

                    replacements.forEach(($part) => {
                        compatibilityCheckData.replacements.add($part.get(0));
                        $this._getPartCompatibilityData($this, $part, compatibilityCheckData);
                    });
                }
            }

            return compatibilityCheckData;
        },

        /**
         * Show compatibility modal
         *
         * @param $this
         * @param {CompatibilityCheckData} compatibilityData
         *
         * @returns {Promise}
         */
        _showCompatibilityModal: function ($this, compatibilityData) {
            const btnCommonClass = 'uk-button uk-button-small jsCompatibilityButton',
                  listCommonClass = 'uk-list tm-list-small uk-text-muted',
                  modalTitle = $this.getOption('langCompatibilityModalTitle');

            let text = '';

            if (compatibilityData.incompatiblePairs.length) {
                text += '<div class="uk-margin-small">' + $this.getOption('langCompatibilityIncompatibleTitle') + ':</div>';
                text += '<ul class="' + listCommonClass + ' hp-list-incompatible uk-margin-small uk-margin-bottom">' +
                            compatibilityData.incompatiblePairs[0].map((part) => '<li>' + $this._getPartName($(part)) + '</li>').join('') +
                        '</ul>';
            }

            if (compatibilityData.replacements.size > 0) {
                text += '<div class="uk-margin uk-margin-small-bottom">' + $this.getOption('langCompatibilityAutoreplaceTitle') + ':</div>';
                text += '<ul class="' + listCommonClass + ' uk-margin-remove">' +
                            Array.from(compatibilityData.replacements).map((part) => '<li>- ' + $this._getPartName($(part)) + '</li>').join('') +
                        '</ul>';
            }

            if (compatibilityData.removements.size > 0) {
                text += '<div class="uk-margin uk-margin-small-bottom">' + $this.getOption('langCompatibilityAutoremoveTitle') + ':</div>';
                text += '<ul class="' + listCommonClass + ' uk-margin-remove">' +
                            Array.from(compatibilityData.removements).map((part) => '<li>- ' + $this._getPartName($(part)) + '</li>').join('') +
                        '</ul>';
            }

            const $dialogHtml =
                    $('<div class="jsCompatibilityModal" uk-modal="bg-close: false">' +
                        '<div class="uk-modal-dialog tm-card-bordered tm-background-gray-15">' +
                            '<button class="uk-modal-close-default" type="button" uk-close></button>' +
                            '<div class="uk-modal-body">' +
                                '<div class="uk-h5">' + modalTitle + '</div>' +
                                '<div>' + 
                                    text +
                                '</div>' +
                            '</div>' +
                            '<div class="uk-modal-footer tm-background-gray-15 tm-modal-footer-controls-full">' +
                                '<span class="' + btnCommonClass + ' uk-button-primary jsCompatibilityConfirm">' + $this.getOption('langCompatibilitySubmitButton') + '</span>' +
                                '<span class="' + btnCommonClass + '">' + $this.getOption('langCompatibilityCancelButton') + '</span>' +
                            '</div>' +
                        '</div>' +
                    '</div>');

            UIkit.modal($dialogHtml).show();

            return new Promise((resolve, reject) => {
                $this.$body.one('click', '.jsCompatibilityModal .jsCompatibilityButton', function () {
                    UIkit.modal('.jsCompatibilityModal').hide();

                    if ($(this).is('.jsCompatibilityConfirm')) {
                        resolve();
                    }
                });

                UIkit.util.once('.jsCompatibilityModal', 'hidden', function (e) {
                    UIkit.modal(e.target).$destroy(true);
                    reject();
                });
            });
        },

        /**
         * Click on part in configurator.
         *
         * @param e
         * @param $this
         */
        'click .hp-configurator-part': function (e, $this) {
            if ($(e.target).closest('.jsPreventCheck').length) {
                return; // return if .jsPreventCheck element clicked 
            }

            const $part = $(this).closest('.hp-part-wrapper'),
                  $option = $(e.target).closest('.hp-option');

            if ($part.hasClass('hp-part-swatches') && !$option.is('.hp-option')) {
                return; // return if part template is swatches but click target is outside the swatch
            }

            // Only the part option changes
            if ($part.hasClass('hp-part-checked') && $option.is('.hp-option')) {
                if (!$option.hasClass('hp-option-checked')) {
                    $this._selectOption($part, $option);
                    $this._updateGroupPrice($this, $part.closest('.sub-group-content'));
                    $this._updateTotalPrice($this);
                    $this._requestIdleCallback(function () {
                        $this._updateRightBox($this, $part, 'changeOption');
                        $this._setGroupPartImage($this, $part);
                        $this._setUnsavedState($this, true);
                    });
                }
                return;
            }

            const $inputType = $part.find('.jsPartInput').attr('type');

            if ($inputType === 'radio') {
                if ($part.hasClass('hp-part-checked')) return; // do nothing if already checked

                $this._checkCompatibilities($this, $part)
                    .then(() => {
                        const $group = $part.closest('.hp-configurator-parts'),
                              $lastChecked = $group.find('.hp-part-checked');
                        $this._unsetPart($this, $lastChecked);

                        let optionId = 0;
                        if ($option.is('.hp-option')) {
                            optionId = $option.find('[type="radio"]').val();
                        }
                        $this._selectPart($this, $part, optionId);

                        $this._updateGroupPrice($this, $part.closest('.sub-group-content'));
                        $this._updateTotalPrice($this);

                        $this._requestIdleCallback(function () {
                            $this._setGroupPartImage($this, $part);
                            $this._updateRightBox($this, $part, 'set');
                            $this._setUnsavedState($this, true);
                        });
                    })
                    .catch(() => {});
            } else if ($inputType === 'checkbox') {
                let action = 'set';
                if ($part.hasClass('hp-part-checked')) {
                    $this._unsetPart($this, $part);
                    action = 'remove';
                } else {
                    const optionId = $option.find('[type="radio"]').val();
                    $this._selectPart($this, $part, optionId);
                }

                $this._updateGroupPrice($this, $part.closest('.sub-group-content'));
                $this._updateTotalPrice($this);

                $this._requestIdleCallback(function () {
                    action === 'set' ? $this._setGroupPartImage($this, $part) : $this._unsetGroupPartImage($this, $part);
                    $this._updateRightBox($this, $part, action);
                    $this._setUnsavedState($this, true);
                });
            }
        },

        /**
         * On change part quantity.
         *
         * @param e
         * @param $this
         */
        'change .jsPartQuantity': function (e, $this) {
            const $part = $(this).closest('.hp-part-wrapper');

            if ($part.hasClass('hp-part-checked')) {
                $this._updateGroupPrice($this, $part.closest('.sub-group-content'));
                $this._updateTotalPrice($this);
                $this._requestIdleCallback(function () {
                    $this._setUnsavedState($this, true);
                    $this._updateRightBox($this, $part, 'changeQuantity');
                });
            }
        },

        /**
         * On click unset button.
         *
         * @param e
         * @param $this
         */
        'click .jsUnsetPart': function (e, $this) {
            e.stopPropagation();

            const $parentDropdown = $(this).closest('[uk-dropdown]');
            if ($parentDropdown.length > 0) {
                $parentDropdown.removeClass('uk-open').prev().removeClass('uk-open');
                UIkit.dropdown($parentDropdown).hide();
            }

            const $part = $(this).closest('.hp-part-wrapper');
            $this._unsetPart($this, $part);
            $this._unsetGroupPartImage($this, $part);
            $this._updateGroupPrice($this, $part.closest('.sub-group-content'));
            $this._updateTotalPrice($this);
            $this._requestIdleCallback(function () {
                $this._setUnsavedState($this, true);
                $this._updateRightBox($this, $part, 'remove');
            });
        },

        /**
         * On click compare button.
         *
         * @param e
         * @param $this
         */
        'click .jsCompareAdd': function (e, $this) {
            const $parentDropdown = $(this).closest('[uk-dropdown]');
            if ($parentDropdown.length > 0) {
                $(document).one('hpcompareupdated', function () {
                    $parentDropdown.removeClass('uk-open').prev().removeClass('uk-open');
                    UIkit.dropdown($parentDropdown).hide();
                });
            }
        },

        /**
         * On click reset button.
         *
         * @param e
         * @param $this
         */
        'click .jsConfigReset': function (e, $this) {
            e.preventDefault();
            UIkit.modal.confirm($this.getOption('resetConfirmMsg')).then(function () {
                $this._resetConfig($this);
            }, function () {});
        },

        /**
         * Add personal configuration in to the cart.
         *
         * @param e
         * @param $this
         */
        'click .jsAddToCart': function (e, $this) {
            e.preventDefault();
            const $button = $(this);
            $this._lockButton($button);

            const configId = $this._getConfigurationId($this);
            const userId = $this._getUserId();
            if (configId === 0 && !$this.isUnsaved) {
                $this._addToCart($this);
            } else if (userId !== 0 && configId !== 0 && !$this.isUnsaved) {
                $this._addToCart($this);
            } else {
                if ($this.isInCart && configId !== 0) {
                    $this._handleUpdateConfigInCart($this, function (forceCreate) {
                        const addToCart = forceCreate ?
                            function() { $this._addToCart($this) } :
                            function() { $this._unlockButton($button) };
                        $this._saveConfig($this, addToCart, true, forceCreate);
                    });
                } else {
                    $this._saveConfig($this, function () {
                        $this._addToCart($this);
                    }, true);
                }
            }
        },

        /**
         * On click show full specs button
         *
         * @param e
         * @param $this
         */
        'click .jsShowFullSpecs': function (e, $this) {
            e.preventDefault();

            $this._showSpecsModal($this);
        },

        /**
         * Change group view.
         *
         * @param e
         */
        'click .jsChangeView a': function (e) {
            e.preventDefault();
            const $button = $(this);
            $button.addClass('uk-disabled').siblings().removeClass('uk-disabled');

            const $subGroupContent = $button.closest('.sub-group-content'),
                  $groupParts = $subGroupContent.find('.hp-configurator-parts'),
                  thumbsGridAllClasses = 'uk-child-width-1-1 uk-child-width-1-2 uk-child-width-1-2@s uk-child-width-1-3@s uk-child-width-1-2@m uk-child-width-1-4@m uk-child-width-1-3@l uk-child-width-1-3@xl uk-child-width-1-4@xl uk-grid-small',
                  listGridClass = 'uk-child-width-1-1 uk-grid-collapse';

            if ($button.hasClass('jsThumbView')) {
                let thumbsGridClass = "uk-grid-small ";
                if ($button.data('columns') == 4) {
                    thumbsGridClass += "uk-child-width-1-2 uk-child-width-1-3@s uk-child-width-1-4@m uk-child-width-1-3@l uk-child-width-1-4@xl";
                } else if ($button.data('columns') == 2) {
                    thumbsGridClass += "uk-child-width-1-1 uk-child-width-1-2@s";
                } else {
                    thumbsGridClass += "uk-child-width-1-2 uk-child-width-1-3@s uk-child-width-1-2@m uk-child-width-1-3@xl";
                }

                $subGroupContent.addClass('hp-view-thumbnails');

                $groupParts
                    .removeClass(listGridClass)
                    .addClass(thumbsGridClass);

                const $partsImg = $groupParts.find('.hp-conf-part__image');

                if ($partsImg.length > 0) {
                    $partsImg.removeClass('uk-hidden');
                } else {
                    $groupParts.find('.hp-configurator-part').each(function () {
                        const $part  = $(this),
                              imgSrc = $part.data('image');
                        $part.prepend('<div class="hp-conf-part__image"><img src="' + imgSrc + '"></div>');
                    });
                }

            } else if ($button.hasClass('jsListView')) {
                $subGroupContent.removeClass('hp-view-thumbnails');

                $groupParts
                    .removeClass(thumbsGridAllClasses)
                    .addClass(listGridClass);

                $groupParts
                    .find('.hp-conf-part__image').addClass('uk-hidden');
            }

            UIkit.update(type = 'update');
        },

        /**
         * Hide part dropdown when opening info lightbox
         *
         * @param e
         * @param $this
         */
        'click .jsLoadIframe, .jsOptionToggle': function (e, $this) {
            const parentDropdown = $(this).closest('[uk-dropdown]');
            if (parentDropdown.length > 0) {
                parentDropdown.removeClass('uk-open').prev().removeClass('uk-open');
                UIkit.dropdown(parentDropdown).hide();
            }
        },

        /**
         * Hide part price when its options show
         *
         * @param e
         * @param $this
         */
        'show .hp-part-options': function (e, $this) {
            if ($(e.target).hasClass('hp-part-options')) {
                $(this)
                    .closest('.hp-part-wrapper')
                    .find('.hp-configurator-part-price, .hp-conf-part__availability')
                    .addClass('uk-hidden');
            }
        },

        /**
         * Show part price when its options hide
         *
         * @param e
         * @param $this
         */
        'hide .hp-part-options': function (e, $this) {
            if ($(e.target).hasClass('hp-part-options')) {
                $(this)
                    .closest('.hp-part-wrapper')
                    .find('.hp-configurator-part-price, .hp-conf-part__availability')
                    .removeClass('uk-hidden');
            }
        },

        /**
         * On click save button
         *
         * @param e
         * @param $this
         */
        'click {document} .jsSaveConfig': function(e, $this) {
            $this._lockButton($(this));
            $this._handleManualSave($this);
        },

        /**
         * On submit send by email form.
         *
         * @param e
         * @param $this
         */
        'submit {document} .jsSendEmailForm': function (e, $this) {
            e.preventDefault();

            const $form = $(this);
            if ($form.validate) {
                $form.valid();
                if ($form.validate().numberOfInvalids() != 0) {
                    return false;
                }
            }

            const data = {
                'rand'       : JBZoo.rand(100, 999),
                'option'     : 'com_hyperpc',
                'task'       : 'configurator.send_by_email',
                'tmpl'       : 'component',
                'format'     : 'raw',
                'totalPrice' : $this._getTotalPrice($this)
            };

            $form.find('input').each(function () {
                const $input = $(this);
                if ($input.is('[type="checkbox"]')) {
                    data[$input.attr('name')] = $input.prop('checked');
                } else {
                    data[$input.attr('name')] = $input.val();
                }
            });

            const $recaptcha = $form.find('.g-recaptcha');
            if ($recaptcha.length > 0 && window.grecaptcha) {
                const grecaptchaResponse = grecaptcha.getResponse($recaptcha.data('recaptcha-widget-id'));
                data['g-recaptcha-response'] = grecaptchaResponse;
            }

            $form.find('[uk-alert]').remove();
            $form.find('[type="submit"]')
                 .prepend('<span uk-spinner="ratio: 0.7"></span>')
                 .attr('disabled', 'disabled');

            $.ajax({
                'url'       : '/index.php?' + $this.$('.jsConfiguratorForm').serialize(),
                'dataType'  : 'json',
                'type'      : 'POST',
                'timeout'   : 15000,
                'data'      : data
            })
            .done(function (response) {
                if (response.message && response.message !== '') {
                    const alertStyle = response.result ? 'uk-alert-success' : 'uk-alert-danger';
                    $form.prepend(
                        '<div class="uk-alert ' + alertStyle + '" uk-alert>' +
                            '<a class="uk-alert-close" uk-close></a>' +
                            response.message +
                        '</div>');
                }

                if (response.result) {

                    window.dataLayer && window.dataLayer.push({
                        'event'       : 'hpTrackedAction',
                        'hpAction'    : 'sendForm',
                        'gtm.element' : $form.get(0)
                    });

                    setTimeout(function () {
                        UIkit.modal($form.closest('[uk-modal]')).hide();
                        $form.find('[uk-alert]').remove();
                    }, 2500);
                }
            })
            .fail(function (xjr, error) {
                const msg = error.msg || $this.getOption('msgTryAgain');
                $form.prepend(
                    '<div class="uk-alert uk-alert-danger" uk-alert>' +
                        '<a class="uk-alert-close" uk-close></a>' +
                        msg +
                    '</div>');

                $form.find('[type="submit"]').removeAttr('disabled');
                $form.find('[type="submit"] > span').remove();
            })
            .always(function () {
                if ($recaptcha.length > 0 && window.grecaptcha) {
                    grecaptcha.reset($recaptcha.data('recaptcha-widget-id'));
                }
                $form.find('[type="submit"]')
                     .removeAttr('disabled')
                     .find('[uk-spinner]').remove();
            });
        },

        /**
         * On click filter button.
         *
         * @param e
         * @param $this
         */
        'click [uk-filter-control]': function (e, $this) {
            const $button = $(e.target);

            if ($button.hasClass('uk-active')) {
                return false;
            }

            $button.addClass('uk-active').siblings().removeClass('uk-active');
            $this._filterGroup($button);
        },

        /**
         * On change instock checkbox.
         *
         * @param e
         * @param $this
         */
        'change .jsOnlyInstock': function (e, $this) {
            const $checkbox = $(this),
                  checked = $checkbox.prop('checked'),
                  $group = $checkbox.closest('.sub-group-content'),
                  $parts = $group.find('.hp-part-wrapper'),
                  $outOfStockParts = $parts.filter('[data-instock="false"]'),
                  $outOfStockOptions = $parts.not($outOfStockParts).find('.hp-option').filter('[data-instock="false"]'),
                  $showPreorderedWrapper = $group.find('.jsShowAllPreorderedWrapper');

            if (checked) {
                $outOfStockParts.addClass('hp-part-wrapper--disabled');
                $outOfStockOptions.addClass('hp-option--disabled');
                $showPreorderedWrapper.removeAttr('hidden');
            } else {
                $outOfStockParts.removeClass('hp-part-wrapper--disabled');
                $outOfStockOptions.removeClass('hp-option--disabled');
                $showPreorderedWrapper.attr('hidden', 'hidden');
            }

            $this._actuailzeFilterButtons($this, $group, $parts);
        },

        /**
         * On change instock global checkbox.
         *
         * @param e
         * @param $this
         */
        'change .jsOnlyInstockGlobal': function (e, $this) {
            const $checked = $(this).prop('checked');
            $this.$('.jsOnlyInstock').each(function () {
                $(this).prop('checked', $checked)
                       .trigger('change');
            });
        },

        /**
         * On click .jsShowAllPreordered.
         *
         * @param e
         * @param $this
         */
        'click .jsShowAllPreordered': function (e, $this) {
            const $button = $(this),
                  $checkbox = $button.closest('.hp-group-filters').find('.jsOnlyInstock');

            $checkbox.prop('checked', false).trigger('change');
            $button.closest('.jsShowAllPreorderedWrapper').attr('hidden', 'hidden');
        },

        /**
         * On hover availability mark.
         *
         * @param e
         * @param $this
         */
        'mouseenter .hp-conf-part__availability, .hp-option__availability': function (e, $this) {
            const $markEl = $(this);
            if (!$markEl.is('[title]') && (!$this.$body.is('.device-mobile-yes, .device-table-yes'))) {
                const $wrapper = $markEl.closest('[data-instock]'),
                      title = $wrapper.data('instock') ? $this.getOption('instockTooltipText') : $this.getOption('preorderTooltipText');

                UIkit.tooltip($markEl, {title: title, offset: 5}).show();
            }
        }

    });
});
