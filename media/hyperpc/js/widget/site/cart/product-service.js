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
 *
 * @author     Sergey Kalistratov <kalistratov.s.m@gmail.com>
 * @author     Artem Vyshnevskiy
 */

jQuery(function ($) {

    JBZoo.widget('HyperPC.CartProductService', {
        group_id    : 0,
        product_id  : 0,
        item_key    : 0,
        config_id   : 0,
        def_part    : 0
    }, {

        /**
         * Initialize widget.
         *
         * @param $this
         */
        init: function ($this) {
            UIkit.util.on('.jsChangeGroupPart', 'itemshow', function (e) {
                const eventDetails = e.detail[0],
                      firstActive = eventDetails.index + 1,
                      lastActive = eventDetails.index + (eventDetails.length - eventDetails.maxIndex);

                $this.$('.jsSliderNavActiveRange').text(
                    firstActive === lastActive ? firstActive : firstActive + ' - ' + lastActive
                );
            });

            UIkit.slider('.jsChangeGroupPart', {finite: true, velocity: 10});
        },

        /**
         * Show add service link.
         *
         * @param $serviceWrap jQuery object
         */
        _showAddService: function ($serviceWrap) {
            $serviceWrap.find('.hp-cart-item-service__link').removeAttr('hidden');
            $serviceWrap.find('.hp-cart-item-service__label').attr('hidden', 'hidden');
            $serviceWrap.find('.hp-cart-item-service__edit').attr('hidden', 'hidden');
        },

        /**
         * Show picked service.
         *
         * @param $serviceWrap jQuery object
         */
        _showPickedService: function ($serviceWrap) {
            $serviceWrap.find('.hp-cart-item-service__link').attr('hidden', 'hidden');
            $serviceWrap.find('.hp-cart-item-service__label').removeAttr('hidden');
            $serviceWrap.find('.hp-cart-item-service__edit').removeAttr('hidden');
        },

        /**
         * Close lightbox
         */
        _closeLightbox: function () {
            $(window.parent.document)
                .find('.uk-lightbox')
                .filter('.uk-open')
                .find('[uk-close], [data-uk-close]')
                .eq(0)
                .trigger('click');
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
         * Choose item.
         *
         * @param e
         * @param $this
         */
        'click .jsItemChoose': function (e, $this) {
            const item           = $(this),
                  serviceId      = item.data('itemkey'),
                  servicePrice   = item.data('price'),
                  overrideParams = item.data('overrideParams'),
                  groupId        = $this.getOption('group_id'),
                  configId       = $this.getOption('config_id'),
                  itemKey        = $this.getOption('item_key'),
                  controller     = itemKey.indexOf('position-') === 0 ? 'moysklad_product' : 'product';

            const $doc         = $(window.parent.document),
                  $serviceWrap = $doc.find('#hp-service-' + itemKey + '-' + groupId);

            $.ajax({
                'url'      : '/index.php',
                'method'   : 'POST',
                'dataType' : 'json',
                'data'     : {
                    'option'     : 'com_hyperpc',
                    'format'     : null,
                    'tmpl'       : 'component',
                    'task'       : controller + '.service-save-session',
                    'product-id' : $this.getOption('product_id'),
                    'price'      : servicePrice,
                    'service-id' : serviceId,
                    'group-id'   : groupId,
                    'config-id'  : configId,
                    'item-key'   : itemKey
                }
            })
            .done(function (data) {
                if (data.result) {
                    if ($serviceWrap.length) {
                        let href = data.url;
                        href = href.replace('component/hyperpc/', 'cart');

                        let   rate         = typeof data.discount !== "undefined" ? data.discount : 0;
                        const $itemRow     = $serviceWrap.closest('.jsCartItemRow'),
                              promoType    = data.promoType,
                              unitPrice    = rate === 0 ? data.price_quantity : data.price_quantity - (data.price_quantity * (rate / 100)),
                              servicePrice = rate === 0 || promoType !== 1 ? data.price : data.price - (data.price * (rate / 100)),
                              priceFormat  = $this._priceFormat(servicePrice);

                        $serviceWrap.attr('data-override-params', JSON.stringify(overrideParams));
                        $serviceWrap.find('.jsServiceEdit').attr('href', href);
                        $serviceWrap.find('.jsServiceName').text(data.name);
                        $serviceWrap.find('.jsServicePrice').attr('data-original-price', data.price).attr('data-promo-type', data.promo_type);
                        $serviceWrap.find('.jsServicePrice .simpleType-value').text(priceFormat).attr('content', servicePrice);

                        if (serviceId == $serviceWrap.data('default')) {
                            $this._showAddService($serviceWrap);
                        } else {
                            $this._showPickedService($serviceWrap);
                        }

                        $itemRow.find('.jsPriceForOne').val(unitPrice);
                        parent.$('.hp-item-' + itemKey).trigger('hpitemupdated', {
                            'promoType'   : promoType,
                            'percentRate' : true,
                            'rate'        : rate
                        });
                    }
                } else {
                    alert(data.message);
                }

                $this._closeLightbox();
            })
            .fail(function (xjr, error) {
                const msg = error.msg || 'Connection error';
                UIkit.notification(msg, 'danger');
                $this._closeLightbox();
            });
        }

    });

});
