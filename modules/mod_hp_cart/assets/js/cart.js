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

    JBZoo.widget('HyperPC.CartModuleCart', {
        lang: 'en-GB',
        cartItemsLimit: 3,
        langItemForms: 'item,items'
    }, {

        /**
         * Initialize widget.
         *
         * @param $this
         */
        init: function ($this) {
            $this._setStorageCount($this._getCurrentCartItemsCount($this));

            $(window).on('storage', function (e) {
                switch (e.key) {
                    case 'hp_cart_items':
                        $this._updateCartItemsList($this);
                        break;
                    case 'hp_cart_items_count':
                        $this._setCartItemsCount($this);
                        break;
                }
            });

            document.addEventListener('hpcartupdated', (e) => {
                $this._onCartUpdated($this, e.detail || {});
            });
        },

        /**
         * On cart updated.
         *
         * @param $this
         * @param data
         */
        _onCartUpdated: function($this, data) {
            if (typeof data !== 'undefined' && typeof data.count !== 'undefined' && typeof data.items !== 'undefined') {
                localStorage.setItem('hp_cart_items', JSON.stringify(data.items));
                $this._updateCartItemsList($this, data.items);

                $this._setCartItemsCount($this, data.count);
            } else { 
                const xhr = $.ajax({
                    'url'       : '/index.php',
                    'dataType'  : 'json',
                    'type'      : 'POST',
                    'data'      : {
                        'option' : 'com_hyperpc',
                        'task'   : 'cart.get-items-count',
                        'format' : 'raw'
                    }
                });

                xhr.done(function (response) {
                    $this._setStorageCount(response.count);
                    $this._setCartItemsCount($this, response.count);
                });
            }

            window.dataLayer && window.dataLayer.push({'event' : 'hpCartUpdated'});
        },

        /**
         * Update cart items list
         * 
         * @param $this
         * @param {array} [items]
         */
        _updateCartItemsList: function ($this, items) {
            items = items || JSON.parse(localStorage.getItem('hp_cart_items')) || [];

            if (items.length > 0) {
                $this.$('.jsNavbarUserCartItemsWrapper').removeAttr('hidden');
                let cartIemsHtml = '';

                for (let i = 0; i < items.length; i++) {
                    const item = items[i];
                    const html = '<li class="jsNavbarUserCartItem">' +
                                    (item.url.length ? '<a href="' + item.url + '" class="uk-link-reset" target="_blank">' : '') +
                                        '<span class="uk-flex uk-flex-middle">' +
                                            '<span class="uk-flex-none hp-mod-cart-item__image">' +
                                                '<img src="' + item.image + '" alt="" class="uk-responsive-height">' +
                                            '</span>' +
                                            '<span>' +
                                                '<span class="uk-text-muted">' + item.category + '</span>' +
                                                '<span class="uk-display-block">' + item.name + '</span>' +
                                            '</span>' +
                                        '</span>' +
                                    (item.url.length ? '</a>' : '') +
                                '</li>';

                    cartIemsHtml += html;
                }

                $this.$('.jsNavbarUserCartItems').html(cartIemsHtml);

                if (items.length > $this.getOption('cartItemsLimit') && $this.$('[data-uk-dropdown]').hasClass('uk-open')) {
                    $this._checkCartItems($this);
                } else {
                    $this.$('.jsHiddenItemsCount').parent().attr('hidden', 'hidden');
                }
            } else {
                $this.$('.jsNavbarUserCartLink').removeAttr('hidden');
                $this.$('.jsNavbarUserCartItemsWrapper').attr('hidden', 'hidden')
                        .find('.jsNavbarUserCartItems').html('');
            }
        },

        /**
         * Set cart items count.
         *
         * @param $this
         * @param {number} [itemsCount]
         */
        _setCartItemsCount: function ($this, itemsCount) {
            const count = itemsCount || localStorage.getItem('hp_cart_items_count') || 0,
                  $badge = $this.$('.jsCartModuleUserBadge');

            $badge.html(count);
            if (count == 0) {
                $badge.attr('hidden', 'hidden');
            } else {
                $badge.removeAttr('hidden');
            }
        },

        /**
         * Get current cart items count.
         *
         * @param $this
         * 
         * @returns {number}
         */
        _getCurrentCartItemsCount: function ($this) {
            return parseInt($this.$('.jsCartModuleUserBadge').text().trim().replace(/[()]/g, '')) || 0;
        },

        /**
         * Check and hide excess cart items.
         *
         * @param $this
         */
        _checkCartItems: function ($this) {
            const $cartItems = $this.$('.jsNavbarUserCartItem');

            if ($cartItems.length === 0) {
                return false;
            }

            const limit = Math.min($this.getOption('cartItemsLimit'), $cartItems.length),
                  hiddenItems = $cartItems.length - limit;
            if (hiddenItems > 0) {
                $cartItems.removeAttr('hidden');
                $cartItems.slice(limit).attr('hidden', 'hidden');
                $this.$('.jsHiddenItemsCount')
                    .html($this._pluralize($this, hiddenItems, $this.getOption('langItemForms').split(',')))
                    .parent().removeAttr('hidden');
            } else {
                $this.$('.jsHiddenItemsCount').parent().attr('hidden', 'hidden');
            }

            $this.$('.jsNavbarUserCartItemsWrapper').removeAttr('hidden');
            $this.$('.jsNavbarUserCartLink').attr('hidden', 'hidden');

            const $drop = $this.$('.hp-mod-cart-drop');
            let heightDiff = $(window).height() - ($drop.outerHeight() + $drop.position().top);
            if ($drop.position().top < 0 || heightDiff < 0) { // Not works properly
                if (Math.abs(heightDiff) >= $this.$('.jsNavbarUserCartItems').outerHeight()) {
                    $this.$('.jsNavbarUserCartItemsWrapper').attr('hidden', 'hidden');
                    $this.$('.jsNavbarUserCartLink').removeAttr('hidden');
                } else {
                    const $visibleItems = $cartItems.not('[hidden]');
                    $($visibleItems.get().reverse()).each(function () {
                        const $item = $(this);
                        let itemHeight = $item.outerHeight() + parseInt($item.css('marginTop'));
                        $item.attr('hidden', 'hidden');
                        heightDiff += itemHeight;
                        if (heightDiff >= 0) {
                            return false;
                        }
                    });

                    const $actualVisible = $cartItems.not('[hidden]');

                    $this.$('.jsHiddenItemsCount')
                            .html($this._pluralize($this, $cartItems.length - $actualVisible.length, $this.getOption('langItemForms').split(',')))
                            .parent().removeAttr('hidden');

                    if (heightDiff < 0 || $actualVisible.length === 0) {
                        $this.$('.jsNavbarUserCartItemsWrapper').attr('hidden', 'hidden');
                        $this.$('.jsNavbarUserCartLink').removeAttr('hidden');
                    }
                }
            }
        },

        /**
         * Get text count of hidden items.
         *
         * @param $this
         * @param {number} number
         * @param {Array} forms
         * @returns {string}
         */
        _pluralize: function ($this, number, forms) {
            let index = 0;
            switch ($this.getOption('lang')) {
                case 'en-GB':
                    index = number === 1 ? 0 : 1;
                    break;
                case 'ru-RU':
                    const cases = [2, 0, 1, 1, 1, 2];
                    index = (number%100 > 4 && number%100 < 20) ? 2 : cases[(number%10 < 5) ? number%10 : 5];
                    break;
            }

            if (!forms[index]) {
                index = forms.length - 1;
            }

            return number + ' ' + forms[index];
        },

        /**
         * Set cart items count to browser storage
         *
         * @param {number} count
         */
        _setStorageCount: function (count) {
            localStorage.setItem('hp_cart_items_count', parseInt(count));
        },

        /**
         * On show action drop.
         *
         * @param e
         * @param $this
         */
        'show .hp-mod-cart-drop': function (e, $this) {
            $this._checkCartItems($this);
        },

    });
});
