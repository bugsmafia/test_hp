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

    JBZoo.widget('HyperPC.SiteMisc', {
        'waitMsg': 'Building configuration in progress. Please wait...'
    }, {

        /**
         * Initialize widget.
         *
         * @param $this
         */
        init: function ($this) {
            $(document).on('goToConfigurator', function () {
                $this._showWaitDialog($this);
            });

            // Hide waiting dialog on iOs when back button is used
            $(window).on('pageshow', function(e) {
                if (e.originalEvent.persisted && $('.uk-modal.uk-open').length) {
                    window.location.reload();
                }
            });

            if (typeof $().raty === 'function') {
                $this.$('.jsRatingStars').raty({
                    starType: 'i',
                    readOnly: true
                });
            }

            // Push dataLayer purchase event
            const $dataLayerPurchase = $('.jsDataLayerPurchase');
            if ($dataLayerPurchase.length) {
                let eventData = $dataLayerPurchase.html();

                try {
                    eventData = JSON.parse(eventData);
                } catch (error) {
                    console.warn(error);
                    eventData = '';
                }

                window.dataLayer = window.dataLayer || [];
                if (typeof eventData === 'object') {
                    if (Array.isArray(eventData)) {
                        eventData.forEach(function(event) {
                            window.dataLayer.push(event);
                        });
                    } else {
                        window.dataLayer.push(eventData);
                    }
                }
            }

            const productClicked = localStorage.getItem('hp_product_click');
            if (productClicked) {
                window.dataLayer = window.dataLayer || [];

                const product = JSON.parse(productClicked);

                const ga4Item = {
                    'item_name'      : product.name,
                    'item_id'        : product.id,
                    'price'          : product.price,
                    'item_brand'     : product.brand || '',
                    'item_list_name' : product.list_name || '',
                    'item_list_id'   : product.list_id || '',
                    'quantity'       : product.quantity
                };

                const categories = product.categories.slice().reverse();
                for (let i = 0; i < categories.length; i++) {
                    const propKey = 'item_category' + (i > 0 ? (i + 1) : '');
                    ga4Item[propKey] = categories[i];
                }

                const dataGA4 = {
                    'event': 'select_item',
                    'ecommerce' : {
                        'currency': product.currency,
                        'items': [ga4Item]
                    }
                };

                dataLayer.push({ecommerce: null});
                dataLayer.push(dataGA4);

                localStorage.removeItem('hp_product_click');
            }
        },

        /**
         * Show wait dialog
         *
         * @param $this
         */
        _showWaitDialog: function ($this) {
            const dialogHtml = '<div class="uk-modal-body"><span class="uk-align-left uk-text-primary uk-margin-right" uk-spinner></span> ' + $this.getOption('waitMsg') + '</div>';
            UIkit.modal.dialog(dialogHtml, { bgClose: false, escClose: false });
        },

        /**
         * On click configurator button
         *
         * @param e
         * @param $this
         */
        'click .jsGoToConfigurator': function (e, $this) {
            $this._showWaitDialog($this);
        }

    });
});
