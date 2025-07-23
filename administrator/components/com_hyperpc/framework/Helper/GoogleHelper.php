<?php
/**
 * HYPERPC - The shop of powerful computers.
 *
 * This file is part of the HYPERPC package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package     HYPERPC
 * @license     Proprietary
 * @copyright   Proprietary https://hyperpc.ru/license
 * @link        https://github.com/HYPER-PC/HYPERPC".
 *
 * @author      Sergey Kalistratov <kalistratov.s.m@gmail.com>
 * @author      Artem Vyshnevskiy
 */

namespace HYPERPC\Helper;

use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use HYPERPC\Object\Ecommerce\ItemData;
use HYPERPC\Joomla\Model\Entity\Order;
use HYPERPC\Joomla\Model\Entity\Entity;
use HYPERPC\Joomla\Model\Entity\MoyskladService;
use HYPERPC\Joomla\Model\Entity\Interfaces\PartMarker;
use HYPERPC\Joomla\Model\Entity\Interfaces\OptionMarker;
use HYPERPC\Joomla\Model\Entity\Interfaces\ProductMarker;

/**
 * Class GoogleHelper
 *
 * @package HYPERPC\Helper
 *
 * @since   2.0
 */
class GoogleHelper extends AppHelper
{

    const ELECTRONICS_CATEGORY_ID = 222;

    /**
     * Hold js item list.
     *
     * @var     array
     *
     * @since   2.0
     */
    protected $_jsItems = [];

    /**
     * Js function 'add to cart' name.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $_jsFunctionAddToCart = 'gtmProductAddToCart';

    /**
     * Js function 'remove from cart' name.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $_jsFunctionRemoveFromCart = 'gtmProductRemoveFromCart';

    /**
     * Get js item list.
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getJsItems()
    {
        return $this->_jsItems;
    }

    /**
     * Get js function 'add to cart' name.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getJsFunctionAddToCartName()
    {
        return $this->_jsFunctionAddToCart;
    }

    /**
     * Get js function 'remove from cart' name.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getJsFunctionRemoveFromCartName()
    {
        return $this->_jsFunctionRemoveFromCart;
    }

    /**
     * Get google client id.
     *
     * @return  string
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getCID()
    {
        $cookie = $this->hyper['input']->cookie;
        $ga = $cookie->get('_ga');
        if ($ga !== null) {
            list($version, $domainDepth, $cid1, $cid2) = preg_split('[\.]', $ga, 4);
            $contents = [
                'version'     => $version,
                'domainDepth' => $domainDepth,
                'cid'         => $cid1 . '.' . $cid2
            ];

            return $contents['cid'];
        }

        return '0.0';
    }

    /**
     * Setup product view list for google dataLayer.
     *
     * @param   array $items
     * @param   ?string $listName
     * @param   ?string $listId
     *
     * @return  $this
     *
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function setDataLayerViewProductList(array $items = [], $listName = null, $listId = null)
    {
        if ($this->enabledGtm() && count($items)) {
            $ga4Data = [
                'event' => 'view_item_list',
                'ecommerce' => [
                    'currency' => $this->hyper['helper']['money']->getCurrencyIsoCode(),
                    'items' => []
                ]
            ];

            $i = 0;
            $maxListLength = 50;

            $itemsData = [];
            /** @var Entity $item */
            foreach ($items as $item) {
                if (method_exists($item, 'isForRetailSale') && !$item->isForRetailSale()) {
                    continue;
                }

                $i++;
                if ($i > $maxListLength) {
                    break;
                }

                $itemData = $this->_collectEntityData($item, [
                    'index'     => $i,
                    'list_name' => $listName ?? Text::_('COM_HYPERPC_ECOMMERCE_ITEM_LIST_NAME_CATEGORY_PAGE'),
                    'list_id'   => $listId ?? 'category_page',
                ]);

                $itemsData[] = $itemData;

                $ga4Data['ecommerce']['items'][] = $itemData->toArrayGA4();
            }

            if (count($itemsData)) {
                $this->hyper['doc']->addScriptDeclaration(
                    'dataLayer.push({ ecommerce: null });' . PHP_EOL .
                    'dataLayer.push(' . json_encode($ga4Data, JSON_UNESCAPED_UNICODE) . ');'
                );
            }
        }

        return $this;
    }

    /**
     * Setup dataLayer product view.
     *
     * @param   ProductMarker|PartMarker|MoyskladService $product
     * @param   ?string $listName
     * @param   ?string $listId
     *
     * @return  $this
     *
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function setDataLayerViewProduct($product, $listName = null, $listId = null)
    {
        if ($this->enabledGtm()) {
            $entityData = $this->_collectEntityData($product, [
                'list_name' => $listName ?? Text::_('COM_HYPERPC_ECOMMERCE_ITEM_LIST_NAME_PRODUCT_PAGE'),
                'list_id' => $listId ?? 'product_page'
            ]);

            $ga4Data = [
                'event' => 'view_item',
                'ecommerce' => [
                    'currency' => $this->hyper['helper']['money']->getCurrencyIsoCode(),
                    'items' => [
                        $entityData->toArrayGA4()
                    ]
                ]
            ];

            $this->hyper['doc']->addScriptDeclaration(
                'dataLayer.push({ ecommerce: null });' . PHP_EOL .
                'dataLayer.push(' . json_encode($ga4Data, JSON_UNESCAPED_UNICODE) . ');'
            );
        }

        return $this;
    }

    /**
     * Set js function for set product click gtm event.
     *
     * @return  $this
     *
     * @since   2.0
     */
    public function setDataLayerProductClickFunc()
    {
        if ($this->enabledGtm()) {
            $this->hyper['doc']->addScriptDeclaration("
                document.addEventListener('DOMContentLoaded', function() {
                    (function($){
                        $('body').on('click', '[data-gtm-click]', function (e) {
                            e.preventDefault();

                            const el = $(this),
                                  sliderClases = el.closest('.uk-slider-items').attr('class');

                            if (sliderClases && sliderClases.includes('uk-transition')) {
                                return;
                            }

                            window.items = window.items || {};
                            const product = window.items[el.data('gtmClick')];

                            if (typeof product !== 'undefined') {
                                localStorage.setItem('hp_product_click', JSON.stringify(product));
                            }

                            document.location = this.href;
                        });
                    })(jQuery);
                });
            ");
        }

        return $this;
    }

    /**
     * Setup dataLayer add to cart.
     *
     * @return  $this
     *
     * @since   2.0
     */
    public function setDataLayerAddToCart()
    {
        static $addToCartFuncCalled;
        if ($this->enabledGtm() && !$addToCartFuncCalled) {
            $addToCartFuncCalled = true;
            $this->hyper['doc']->addScriptDeclaration("function " . $this->getJsFunctionAddToCartName() . "(itemKey, quantity) {
                window.items = window.items || [];
                window.cartItems = window.cartItems || [];
                const product = window.items[itemKey] || window.cartItems[itemKey];
                if (typeof product !== 'undefined') {
                    window.cartItems[itemKey] = JSON.parse(JSON.stringify(product));
                    const categories = product.categories.slice().reverse();

                    const ga4Item = {
                        'item_name'      : product.name,
                        'item_id'        : product.id,
                        'price'          : product.price,
                        'item_brand'     : product.brand || '',
                        'item_list_name' : product.list_name || '',
                        'item_list_id'   : product.list_id || '',
                        'quantity'       : quantity || product.quantity
                    };

                    for (let i = 0; i < categories.length; i++) {
                        const propKey = 'item_category' + (i > 0 ? (i + 1) : '');
                        ga4Item[propKey] = categories[i];
                    }

                    const dataGA4 = {
                        'event': 'add_to_cart',
                        'ecommerce' : {
                            'currency': product.currency,
                            'items': [ga4Item]
                        }
                    };

                    dataLayer.push({'ecommerce': null});
                    dataLayer.push(dataGA4);
                }
            }");
        }

        return $this;
    }

    /**
     * Setup dataLayer remove from cart.
     *
     * @return  $this
     *
     * @since   2.0
     */
    public function setDataLayerRemoveFromCart()
    {
        static $removeFromCartFuncCalled;
        if ($this->enabledGtm() && !$removeFromCartFuncCalled) {
            $removeFromCartFuncCalled = true;
            $this->hyper['doc']->addScriptDeclaration("function " . $this->getJsFunctionRemoveFromCartName() . "(itemKey, quantity) {
                window.cartItems = window.cartItems || {};
                const product = window.cartItems[itemKey];
                if (typeof product !== 'undefined') {
                    window.cartItems[itemKey] = JSON.parse(JSON.stringify(product));
                    const categories = product.categories.slice().reverse();

                    const ga4Item = {
                        'item_name'      : product.name,
                        'item_id'        : product.id,
                        'price'          : product.price,
                        'item_brand'     : product.brand || '',
                        'item_list_name' : product.list_name || '',
                        'item_list_id'   : product.list_id || '',
                        'quantity'       : quantity || product.quantity
                    };

                    for (let i = 0; i < categories.length; i++) {
                        const propKey = 'item_category' + (i > 0 ? (i + 1) : '');
                        ga4Item[propKey] = categories[i];
                    }

                    const dataGA4 = {
                        'event': 'remove_from_cart',
                        'ecommerce' : {
                            'currency': product.currency,
                            'items': [ga4Item]
                        }
                    };

                    dataLayer.push({'ecommerce': null});
                    dataLayer.push(dataGA4);
                }
            }");
        }

        return $this;
    }

    /**
     * Get dataLayer Purchase event data.
     *
     * @param   Order $order
     *
     * @return  array of events
     *
     * @throws  \Exception
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function getDataLayerPurchaseEventData(Order $order)
    {
        if ($this->enabledGtm()) {
            $orderItems = $order->getItems();
            $items = array_merge(
                (array) $orderItems['positions'],
                (array) $orderItems['products'],
                (array) $orderItems['parts']
            );

            $i = 0;
            $dataItems = [];
            foreach ($items as $item) {
                $i++;
                $dataItems[] = $this->_collectEntityData($item, [
                    'index' => $i,
                    'quantity' => max((int) $item->quantity, 1)
                ]);
            }

            $orderTotal   = $order->getTotal();
            $shippingCost = max(intval($order->elements->find('yandex_delivery.shipping_cost', 0)), 0);

            $dataGA4 = [
                'event' => 'purchase',
                'ecommerce' => [
                    'transaction_id' => $order->id,
                    'affiliation'    => Uri::getInstance()->getHost(),
                    'value'          => $orderTotal->val(),
                    'shipping'       => $shippingCost,
                    'currency'       => $this->hyper['helper']['money']->getCurrencyIsoCode($orderTotal),
                    'coupon'         => $order->promo_code,
                    'items'          => array_map(function ($itemData) {
                        return $itemData->toArrayGA4();
                    }, $dataItems)
                ]
            ];

            $dataHP = [
                'event'    => 'hpTrackedAction',
                'hpAction' => $order->isCredit() ? 'orderCedit' : 'orderPayment'
            ];

            return [
                ['ecommerce' => null],
                $dataGA4,
                $dataHP
            ];
        }

        return [];
    }

    /**
     * Setup window js view items.
     *
     * @param   array $items
     * @param   bool $loadPartListOptions
     * @param   ?string $listName
     * @param   ?string $listId
     *
     * @return  $this
     *
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function setJsViewItems(array $items = [], $loadPartListOptions = false, $listName = null, $listId = null)
    {
        if ($this->enabledGtm()) {
            $jsItems = $this->_getTagItems($items, $loadPartListOptions, $listName, $listId);
            $this->hyper['doc']->addScriptDeclaration('window.items=' . json_encode($jsItems, JSON_UNESCAPED_UNICODE));
        }

        return $this;
    }

    /**
     * Setup window js cart items.
     *
     * @param   array $items
     * @param   ?string $listName
     * @param   ?string $listId
     *
     * @return  $this
     *
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function setJsCartItems(array $items = [], $listName = null, $listId = null)
    {
        if ($this->enabledGtm()) {
            $jsItems = $this->_getTagItems($items, false, $listName, $listId);
            $this->hyper['doc']->addScriptDeclaration('window.cartItems=' . json_encode($jsItems, JSON_UNESCAPED_UNICODE));
        }

        return $this;
    }

    /**
     * Get tag items.
     *
     * @param   array $items
     * @param   bool $loadPartListOptions
     * @param   ?string $listName
     * @param   ?string $listId
     *
     * @return  array
     *
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    private function _getTagItems(array $items = [], $loadPartListOptions = false, $listName = null, $listId = null)
    {
        if (!$this->enabledGtm()) {
            return [];
        }

        $i = 0;
        $jsItems = [];
        /** @var ProductMarker|PartMarker|MoyskladService $item */
        foreach ($items as $item) {
            if (method_exists($item, 'isForRetailSale') && !$item->isForRetailSale()) {
                continue;
            }

            $i++;

            if ($item instanceof PartMarker && $loadPartListOptions) {
                $_item = clone $item;
                $options = $_item->getOptions(false, false);
                foreach ($options as $option) {
                    $optionItemKey = $option->getItemKey();

                    $_item->set('option', $option);
                    $_item->setSalePrice($option->getSalePrice());

                    $itemData = $this->_collectEntityData($_item, [
                        'index' => $i,
                        'list_name' => $listName,
                        'list_id' => $listId
                    ]);

                    $jsItems[$optionItemKey] = $itemData->toArray();

                    $i++;
                }
            } else {
                $itemKey = $item->getItemKey();

                $itemData = $this->_collectEntityData($item, [
                    'index' => $i,
                    'quantity' => max((int) $item->quantity, 1),
                    'list_name' => $listName,
                    'list_id' => $listId
                ]);

                $jsItems[$itemKey] = $itemData->toArray();
            }
        }

        $this->_jsItems = $jsItems;

        return $jsItems;
    }

    /**
     * Enable Google TagManager events.
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function enabledGtm()
    {
        $gtmContainerID = $this->getGtmContainerId();
        return $this->hyper['params']->get('enable_gtm', true, 'bool') && !empty($gtmContainerID);
    }

    /**
     * Get GTM Container ID param.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getGtmContainerId()
    {
        return trim($this->hyper['params']->get('gtm_container_id', ''));
    }

    /**
     * Collect entity data.
     *
     * @param   ProductMarker|PartMarker|MoyskladService $entity
     * @param   array $params [
     *      'index' => 1,
     *      'quantity' => 1,
     *      'item_list_name' => null,
     *      'item_list_id' => null,
     *  ]
     *
     * @return  ItemData
     *
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    private function _collectEntityData($entity, $params = [])
    {
        $params = array_replace([
            'index' => 1,
            'quantity' => 1,
            'list_name' => null,
            'list_id' => null,
        ], $params);

        $itemKey = $entity->getItemKey();

        static $itemsData = [];
        if (key_exists($itemKey, $itemsData)) {
            return new ItemData(array_merge($itemsData[$itemKey], $params));
        }

        $entityId    = $itemKey;
        $entityName  = $entity->name;
        $type        = '';
        $entityPrice = $entity->getSalePrice();
        $brand       = null; /** @todo brand for parts */

        if ($entity instanceof ProductMarker) {
            $type  = 'product';
            $brand = strtoupper($this->hyper['params']->get('site_context', 'HYPERPC'));

            $entityPrice = $entity->getConfigPrice();

            // remove configuration id
            $entityId = preg_replace('/^([a-z]+-\d+)($|-).*/', '$1', $entityId);
        } elseif ($entity instanceof PartMarker) {
            $type = 'part';

            $option = $entity->option;
            if (!($option instanceof OptionMarker) || !$option->id) {
                $option = $entity->getDefaultOption();
            }

            if ($option->id) {
                $entityName  = str_replace(' ' . $option->name, '', $entityName);
                $entityName .= ' ' . $option->name;
                $entityPrice = $option->getSalePrice();
            }
        } elseif ($entity instanceof MoyskladService) {
            $type  = 'service';
            $brand = strtoupper($this->hyper['params']->get('site_context', 'HYPERPC'));
        }

        $categories = [];
        $category = $entity->getFolder();
        while ($category->alias !== 'root') {
            $categories[] = $category->title;
            $category = $category->getParent();
        }

        $itemData = [
            'id' => $entityId,
            'name' => $entityName,
            'price' => $entityPrice->val(),
            'currency' => $this->hyper['helper']['money']->getCurrencyIsoCode($entityPrice),
            'type' => $type,
            'brand' => $brand,
            'categories' => $categories
        ];

        $itemsData[$itemKey] = $itemData;

        return new ItemData(array_merge($itemData, $params));
    }
}
