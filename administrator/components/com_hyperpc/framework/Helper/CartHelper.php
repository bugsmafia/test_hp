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

use JBZoo\Data\Data;
use JBZoo\Utils\Arr;
use JBZoo\Utils\Str;
use Cake\Utility\Hash;
use HYPERPC\Data\JSON;
use JBZoo\Image\Image;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Date\Date;
use HYPERPC\Money\Type\Money;
use HYPERPC\ORM\Entity\Store;
use Joomla\Registry\Registry;
use Joomla\CMS\Language\Text;
use Joomla\String\StringHelper;
use HYPERPC\Joomla\Model\ModelList;
use HYPERPC\Object\Date\DatesRange;
use HYPERPC\Joomla\Model\Entity\Entity;
use HYPERPC\Joomla\Model\Entity\Position;
use HYPERPC\Joomla\Model\Entity\PromoCode;
use HYPERPC\Object\Delivery\MeasurementsData;
use HYPERPC\Joomla\Model\Entity\MoyskladPart;
use HYPERPC\Joomla\Model\Entity\ProductFolder;
use HYPERPC\Joomla\Model\Entity\MoyskladProduct;
use HYPERPC\Joomla\Model\Entity\MoyskladService;
use HYPERPC\Joomla\Model\Entity\MoyskladVariant;
use HYPERPC\Joomla\Model\Entity\Interfaces\Stockable;
use HYPERPC\Joomla\Model\Entity\Interfaces\PartMarker;
use HYPERPC\Joomla\Model\Entity\Interfaces\OptionMarker;
use HYPERPC\Joomla\Model\Entity\Interfaces\ProductMarker;

/**
 * Class CartHelper
 *
 * @package HYPERPC\Helper
 *
 * @since   2.0
 */
class CartHelper extends AppHelper
{

    const ADD_ITEM_TYPE_REPLACE = 'item-replace';
    const ADD_ITEM_TYPE_SUM     = 'item-sum';
    const SESSION_CART_NAME     = 'hpcart';
    const SESSION_ITEMS_KEY     = 'items';
    const SESSION_SERVICE_KEY   = 'service';
    const TYPE_CONFIGURATION    = 'configuration';
    const TYPE_NOTEBOOK         = 'notebook';
    const TYPE_PART             = 'part';
    const TYPE_PRODUCT          = 'product';
    const TYPE_POSITION         = 'position';

    /**
     * Hold SessionHelper object.
     *
     * @var     SessionHelper
     *
     * @since   2.0
     */
    protected $_session;

    /**
     * @var     DateHelper
     *
     * @since   2.0
     */
    protected $_dateHelper;

    /**
     * Initialize helper.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function initialize()
    {
        $this->_session = clone $this->hyper['helper']['session'];

        $this->_session
            ->setType(SessionHelper::TYPE_COOKIE)
            ->setNamespace(self::SESSION_CART_NAME);

        $this->_dateHelper = $this->hyper['helper']['date'];
    }

    /**
     * Add item into the cart.
     *
     * @param   mixed   $data
     * @param   string  $mode
     *
     * @return  void
     *
     * @throws  \JBZoo\Utils\Exception
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function addItem($data, $mode = self::ADD_ITEM_TYPE_SUM)
    {
        if (is_array($data)) {
            $data = new Data($data);
        }

        $items = $this->getSessionItems();
        $_key  = $data->get('id');
        $type  = $data->get('type');

        if ($type !== self::TYPE_PRODUCT && $data->get('option') !== null) {
            $_key .= '-' . $data->get('option');
        }

        if ($data->get('type') === self::TYPE_CONFIGURATION) {
            $_key .= '-' . $data->get('savedConfiguration');
            $type = self::TYPE_POSITION;
        }

        $itemKey = $this->getItemKey($_key, $type);

        if (array_key_exists($itemKey, (array) $items)) {
            if ($mode === self::ADD_ITEM_TYPE_SUM) {
                $items[$itemKey]['quantity'] += $data->get('quantity');
            }
            if ($mode === self::ADD_ITEM_TYPE_REPLACE) {
                $items[$itemKey]['quantity'] = $data->get('quantity');
            }
        } else {
            $items[$itemKey] = $data->getArrayCopy();
        }

        if (in_array($type, [self::TYPE_POSITION])) {
            $productHelper = $this->hyper['helper']['moyskladProduct'];

            /** @var ProductMarker $product */
            $product = clone $productHelper->findById($data->get('id'));

            if (!empty($product->id)) {
                $savedConfiguration = $data->get('savedConfiguration');
                if ($savedConfiguration !== null) {
                    $product->set('saved_configuration', (int) $savedConfiguration);
                }

                $partFromConfig = ($data->get('type') === self::TYPE_CONFIGURATION);
                $allowArchive = $product->isInStock();

                $productGroupKey = 'a.product_folder_id ASC';

                $parts = $product->getConfigParts(
                    false,
                    $productGroupKey,
                    true,
                    $partFromConfig,
                    $allowArchive
                );

                //  Get parts from personal configuration.
                $configuration = $product->getConfiguration();

                $detachedParts = [];

                /** @var PartMarker|MoyskladService $part */
                foreach ($parts as $part) {
                    if ($part->isDetached()) {
                        $type    = self::TYPE_POSITION;
                        $partKey = $this->getItemKey($part->id, $type);

                        $optionId = null;
                        if ($configuration->parts !== null) {
                            $optionId = $configuration->parts->find($part->id . '.option_id');
                        } else {
                            $optionId = $product->configuration->find('option.' . $part->id);
                        }

                        if ($optionId !== null) {
                            $partKey .= '-' . $optionId;
                        }

                        $items[$partKey] = [
                            'option'   => $optionId,
                            'id'       => $part->id,
                            'quantity' => $part->quantity * $items[$itemKey]['quantity'],
                            'type'     => $type,
                            'related'  => $itemKey
                        ];

                        $detachedParts[] = $partKey;
                    }
                }

                $items[$itemKey]['single-parts'] = $detachedParts;
            }
        }

        $this->_session->set(self::SESSION_ITEMS_KEY, (array) $items);
    }

    /**
     * Add service item to cart.
     *
     * @param   string  $productKey
     * @param   string  $serviceGroupId
     * @param   string  $serviceType
     * @param   string  $servicePrice
     * @param   string  $serviceId
     *
     * @throws  \Exception
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function addServiceItem($productKey, $serviceGroupId, $serviceType, $servicePrice, $serviceId)
    {
        $items = $this->getServiceItems()->getArrayCopy();

        //  Check allowed service type.
        $items[$productKey][$serviceGroupId] = [
            'id'           => $serviceId,
            'service_type' => $serviceType,
            'price'        => $servicePrice
        ];

        $this->_session->set(self::SESSION_SERVICE_KEY, (array) $items);

        return true;
    }

    /**
     * Clear cart session.
     *
     * @return  void
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function clearSession()
    {
        $this->_session->set(CartHelper::SESSION_ITEMS_KEY, []);
        $this->_session->set(CartHelper::SESSION_SERVICE_KEY, []);
    }

    /**
     * Get cart total
     *
     * @return  Money
     *
     * @throws  \Exception
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function getCartTotal()
    {
        $items = $this->getItems();
        /** @var Money $total */
        $total = $this->hyper['helper']['money']->get(0);

        foreach ($items as $item) {
            /** @var Money $itemPrice */
            $itemPrice = $item->getQuantityPrice(false);
            if ($item instanceof Position) {
                $rate = $this->getPositionRate($item);

                if ($item instanceof MoyskladProduct) {
                    $configPrice = $item->getConfigPrice()->getClone();

                    $item->setListPrice($configPrice);
                }

                $itemPrice->add('-' . $rate);
            }

            $total->add($itemPrice->val());
        }

        return $total;
    }

    /**
     * Get service folders for moysklad products in cart
     *
     * @return  ProductFolder[]
     *
     * @since   2.0
     */
    public function getProductServiceFolders()
    {
        $folderIds = (array) $this->hyper['params']->get('product_cart_service_folders', []);

        if (!count($folderIds)) {
            return [];
        }

        return $this->hyper['helper']['productFolder']->findById($folderIds, ['order' => 'lft']);
    }

    /**
     * Get cart item image
     *
     * @param   Entity  $item
     * @param   int     $imageMaxHeight
     * @param   int     $imageMaxWidth
     *
     * @return  string
     *
     * @throws \JBZoo\Utils\Exception
     * @throws \JBZoo\Image\Exception
     * @throws \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function getItemImage($item, $imageMaxWidth = 120, $imageMaxHeight = 67)
    {
        if (!empty($item->id)) {
            if ($item instanceof MoyskladProduct) {
                return $item->getConfigurationImagePath($imageMaxWidth, $imageMaxHeight);
            } elseif ($item instanceof Position) {
                $image = $item->getItemImage($imageMaxWidth, $imageMaxHeight);
                if (array_key_exists('thumb', $image)) {
                    /** @var Image $cacheImg */
                    $cacheImg = $image['thumb'];

                    return Uri::getInstance($cacheImg->getUrl())->getPath();
                }
            }
        }

        return $this->hyper['helper']['image']->getPlaceholderPath($imageMaxWidth, $imageMaxHeight);
    }

    /**
     * Get session item key.
     *
     * @param   string|int  $itemId
     * @param   string      $type
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getItemKey($itemId, $type)
    {
        return Str::clean($type . '-' . $itemId);
    }

    /**
     * Get item price val object by type.
     *
     * @param   Position   $item
     * @param   string     $type
     * @param   boolean    $checkRate
     *
     * @return  mixed
     *
     * @throws  \Exception
     * @throws  \RuntimeException
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function getItemPriceVal($item, $type, $checkRate = false)
    {
        if ($type === CartHelper::TYPE_POSITION && !$item instanceof MoyskladProduct) {
            $price = clone $item->getPrice(false);
            if ($checkRate) {
                $rate = $this->getPositionRate($item);
                $price->add('-' . $rate);
            }

            return $price;
        }

        $price = clone $item->getConfigPrice();
        if ($checkRate) {
            $item->setListPrice($price);

            $rate = $this->getPositionRate($item);
            $price->add('-' . $rate);
        }

        return $price;
    }

    /**
     * Get session items.
     *
     * @return  Position[]
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getItems()
    {
        /** @var \HyperPcModelPosition $positionModel */
        $positionModel = ModelList::getInstance('Position');

        $result       = [];
        $sessionItems = $this->getSessionItems();

        foreach ($sessionItems as $dataArray) {
            $data     = new Data($dataArray);
            $itemType = $data->get('type', '');
            $quantity = intval($data->get('quantity', HP_QUANTITY_MIN_VAL));

            switch ($itemType) {

                case self::TYPE_POSITION:
                    /** @var Position $position */
                    $position = clone $positionModel->getItem((int) $data->get('id'));
                    if ($position->state === HP_STATUS_UNPUBLISHED) {
                        break;
                    }

                    if ($position instanceof MoyskladPart) {
                        $variant = $this->hyper['helper']['moyskladVariant']->findById((int) $data->get('option'));
                        if ($variant instanceof MoyskladVariant && $variant->id) {
                            $position->set('option', $variant);
                            $position->setListPrice($variant->getListPrice());
                            $position->setSalePrice($variant->getSalePrice());
                        }
                    }

                    $position->set('quantity', $quantity);

                    $itemKey = $position->getItemKey();
                    $result[$itemKey] = $position;
                    break;

                case self::TYPE_CONFIGURATION:
                    /** @var MoyskladProduct $product */
                    $product = clone $positionModel->getItem((int) $data->get('id'));

                    if ($product->id === null) {
                        break;
                    }

                    $savedConfigurationId = (int) $data->get('savedConfiguration', 0);
                    if ($savedConfigurationId > 0) {
                        //  Avoiding affect product params from the custom config.
                        $product->set('params', clone $product->params);
                        $product->set('saved_configuration', $savedConfigurationId);
                    }

                    $product->set('quantity', $quantity);

                    $itemKey = $product->getItemKey();
                    $result[$itemKey] = $product;
                    break;
            }
        }

        return $result;
    }

    /**
     * Get cart items data for render.
     *
     * @return  Data
     *
     * @throws  \Exception
     * @throws  \RuntimeException
     * @throws  \JBZoo\Utils\Exception
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function getItemsDataForRender()
    {
        $sessionItems = $this->getSessionItems();
        $items = $this->getItems();

        $itemsData = new Data();
        foreach ($items as $itemKey => $item) {
            $data           = new Data($sessionItems[$itemKey]);
            $type           = $data->get('type');
            $productType    = $type;
            $onlyForUpgrade = false;

            if ($item instanceof Stockable) {
                $availability = $item->getAvailability();
            }

            $unitPrice  = $this->getItemPriceVal($item, $type);

            $item->setListPrice($unitPrice);
            $item->setSalePrice($unitPrice);

            $promoPrice = $this->getItemPriceVal($item, $type, true);
            $totalPrice = $item->getQuantityPrice(false);
            $promoRate  = $item->get('rate');

            if ($item instanceof Position) {
                $productType = $item->getType();
                $promoRate = $this->getPositionRate($item);
            }

            if ($item instanceof PartMarker) {
                $onlyForUpgrade = $item->isOnlyForUpgrade();

                if ($item->option instanceof OptionMarker && $item->option->id) {
                    $availability = $item->option->getAvailability();
                    $item->name .= " ({$item->option->name})";
                }
            }

            $totalPrice->add('-' . (is_integer($promoRate) ? $promoRate * $item->quantity : $promoRate));

            $itemsData[$itemKey] = new Data([
                'onlyUpgrade'  => $onlyForUpgrade,
                'data'         => $data,
                'item'         => $item,
                'type'         => $type,
                'productType'  => $productType,
                'option'       => $data->get('option'),
                'quantity'     => $item->quantity,
                'itemHash'     => $itemKey,
                'unitPrice'    => $unitPrice,
                'promoPrice'   => $promoPrice,
                'totalPrice'   => $totalPrice,
                'promoRate'    => $promoRate,
                'availability' => $item instanceof Stockable ? $availability : ''
            ]);

            if ($type === self::TYPE_CONFIGURATION) {
                $itemsData[$itemKey]['type'] = self::TYPE_POSITION;
            }

            $itemsData[$itemKey]['savedConfiguration'] = $item->get('saved_configuration', null);

            if (in_array($type, [self::TYPE_CONFIGURATION])) {
                $itemsData[$itemKey]['singleParts'] = (array) $data->get('single-parts', []);
            }

            $itemsData[$itemKey]['dataAttrs'] = new Data([
                'id'                  => $item->id,
                'item-key'            => $itemKey,
                'product-type'        => $itemsData[$itemKey]->get('productType'),
                'type'                => $itemsData[$itemKey]->get('type'),
                'option'              => $itemsData[$itemKey]->get('option'),
                'saved-configuration' => $itemsData[$itemKey]->get('savedConfiguration'),
                'single-parts'        => $itemsData[$itemKey]->get('singleParts')
            ]);

            if ($item instanceof Stockable) {
                if ($item instanceof ProductMarker) {
                    $defaultParts = $item->configuration->get('default', []);
                    $partsBoxesId = $this->hyper['params']->get('product_part_boxes_moysklad', 0);

                    if (in_array($partsBoxesId, $defaultParts)) {
                        if ($item instanceof Position) {
                            $partsBoxesDimensions = new MeasurementsData([
                                'weight' => $this->hyper['params']->get('product_part_boxes_moysklad_weight', 1.0, 'float'),
                                'dimensions' => [
                                    'length' => $this->hyper['params']->get('product_part_boxes_moysklad_length', 50, 'int'),
                                    'width'  => $this->hyper['params']->get('product_part_boxes_moysklad_width', 50, 'int'),
                                    'height' => $this->hyper['params']->get('product_part_boxes_moysklad_height', 10, 'int'),
                                ]
                            ]);
                        }

                        $itemsData[$itemKey]['dataAttrs']['additional-weight']     = $partsBoxesDimensions->weight;
                        $itemsData[$itemKey]['dataAttrs']['additional-dimensions'] = json_encode($partsBoxesDimensions->dimensions);
                    }
                }

                $itemDimensions = $item->getDimensions();

                $itemsData[$itemKey]['dataAttrs']['weight']         = $itemDimensions->weight;
                $itemsData[$itemKey]['dataAttrs']['dimensions']     = json_encode($itemDimensions->dimensions);
                $itemsData[$itemKey]['dataAttrs']['availability']   = $availability;
            }

            if ($onlyForUpgrade) {
                $itemsData[$itemKey]['dataAttrs']->set('only-upgrade', $onlyForUpgrade);
            }

            $sortItemsData[] = [
                'id'    => $itemKey,
                'price' => $totalPrice->val()
            ];
        }

        $return   = new Data();
        $sortList = Hash::sort($sortItemsData, '{n}.price', 'desc');
        foreach ($sortList as $listItem) {
            $sItem = $itemsData->get($listItem['id']);
            if ($sItem) {
                $return->set($listItem['id'], $sItem);
            }
        }

        return $return;
    }

    /**
     * Get short list of cart items.
     *
     * @return  array
     *
     * @throws  \Exception
     * @throws  \JBZoo\Utils\Exception
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function getItemsShortList()
    {
        $sessionItems = $this->getSessionItems();
        $items = $this->getItems();

        $itemsData = [];
        foreach ($items as $itemKey => $item) {
            $data = new Data($sessionItems[$itemKey]);

            $quantityPrice = $item->getQuantityPrice()->val();
            $quanity = max($data->get('quantity', 1, 'int'), 1);

            $productFolder = $item->getFolder();
            $categoryTitle = $item instanceof ProductMarker ? /** @todo create method getTypedTitle */
                Text::_('COM_HYPERPC_PRODUCT_TYPE_' . strtoupper($productFolder->getItemsType())) :
                $productFolder->title; /** @todo get a specific title for each languages */

            $itemData = [
                'name'     => $item->name,
                'category' => $categoryTitle,
                'key'      => $itemKey,
                'image'    => $this->getItemImage($item),
                'price'    => (int) ($quantityPrice / $quanity),
                'url'      => $item->getViewUrl(),
                'quantity' => $data->get('quantity', 1, 'int')
            ];

            if ($item instanceof ProductMarker && $item->get('saved_configuration')) {
                $configId = $item->get('saved_configuration');

                $itemData['url'] = $item->getConfigUrl($configId);
                $itemData['specification'] = $configId;
            }

            $itemsData[] = $itemData;
        }

        usort($itemsData, function ($item1, $item2) {
            return ($item2['price'] <=> $item1['price']);
        });

        return $itemsData;
    }

    /**
     * Get min picking date
     *
     * @param   array $pickingDates
     *
     * @return  array  [
     *     'raw'        => string,
     *     'value'      => string,
     *     'isToday'    => bool,
     *     'isTomorrow' => bool
     * ]
     *
     * @since   2.0
     */
    public function getMinPickingDate($pickingDates)
    {
        $result = [
            'raw'        => '',
            'value'      => '',
            'isToday'    => false,
            'isTomorrow' => false
        ];

        $minDate = false;

        foreach ($pickingDates as $data) {
            $dates = $data['pickup']['raw'];
            $storeMinDate = $this->_dateHelper->parseString($dates)->min;
            if (!empty($storeMinDate)) {
                if ($minDate === false || $storeMinDate->toUnix() < $minDate->toUnix()) {
                    $minDate = $storeMinDate;
                }
            }
        }

        if ($minDate === false) {
            $result['raw'] = false;
            return $result;
        }

        $result['raw'] = $minDate->format(...$this->_dateHelper::INTERNAL_FORMAT_ARGS);

        if ($this->_dateHelper->isToday($minDate)) {
            $result['value'] = Text::_('COM_HYPERPC_TODAY');
            $result['isToday'] = true;
        } elseif ($this->_dateHelper->isTomorrow($minDate)) {
            $result['value'] = Text::_('COM_HYPERPC_TOMORROW');
            $result['isTomorrow'] = true;
        } else {
            $result['value'] = $minDate->format(Text::_('COM_HYPERPC_DATE_FORMAT_LONG_NO_YEAR'), true);
        }

        return $result;
    }

    /**
     * Get order picking dates by store.
     *
     * @param   array $items optional
     * @param   array $sessionItems optional
     *
     * @return  JSON
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getOrderPickingDates($items = [], $sessionItems = [])
    {
        static $_orderPickingDatesCache = [];

        $sessionItems = empty($sessionItems) ? $this->getSessionItems() : $sessionItems;
        $hashData = new Registry($sessionItems);
        $hash = md5($hashData);

        if (array_key_exists($hash, $_orderPickingDatesCache)) {
            return $_orderPickingDatesCache[$hash];
        }

        $result = [
            'stores' => [],
            'shippingReady' => []
        ];

        $minSendingDatesRange = null;

        if (empty($items)) {
            $items = $this->getItems();
        }

        $availabilityInfo = $this->_prepareAvailabilityInfo($items);

        if ($availabilityInfo->get('hasUnavailableItems', false, 'bool')) {
            return new JSON($result);
        }

        $stores = $this->_getStores();
        $processingDate = $this->_getOrderProcessingDate();

        $itemsInPrimary = $availabilityInfo->get('itemsInPrimary', []);

        foreach ($stores as $storeId => $store) {
            $itemsBalance = Arr::clean($availabilityInfo->find('itemsByStore.' . $storeId, []));

            $outOfStoreItemsQuantity = [];
            foreach ($items as $itemKey => $item) {
                $neededQuantity = $sessionItems[$itemKey]['quantity'];
                if (!array_key_exists($itemKey, $itemsBalance)) {
                    $outOfStoreItemsQuantity[$itemKey] = (int) $neededQuantity;
                } elseif ($itemsBalance[$itemKey] < $neededQuantity) {
                    $outOfStoreItemsQuantity[$itemKey] = $neededQuantity - $itemsBalance[$itemKey];
                }
            }

            $outOfStoreItems = array_intersect_key($items, $outOfStoreItemsQuantity);

            if (empty($outOfStoreItemsQuantity)) { // all items in the store
                $storeProcessingDate = $this->_getStoreProcessingDate($store);
                $result['stores'][$storeId] = [
                    'pickup' => [
                        'raw' => $storeProcessingDate->format(...$this->_dateHelper::INTERNAL_FORMAT_ARGS),
                        'value' => $this->_getPickingDateString($storeProcessingDate),
                    ],
                    'availableNow' => true
                ];
            } else { // some items out of store
                $storeReadyDatesRange = new DatesRange();

                if ($store->params->get('primary', 0, 'int')) { // the store is primary
                    $readyDays = $this->_getPreorderReadyDays($outOfStoreItems);
                    $storeReadyDatesRange = $this->_getStoreReadyDatesRange($store, $readyDays['min'], $readyDays['max'], $processingDate);
                } else { // the store is not primary
                    $canBeReplenished = true;
                    foreach ($outOfStoreItemsQuantity as $itemKey => $neededQuantity) {
                        if (!array_key_exists($itemKey, $itemsInPrimary) || $itemsInPrimary[$itemKey] < $neededQuantity) {
                            $canBeReplenished = false;
                            break;
                        }
                    }

                    if ($canBeReplenished) { // relocate from primary store
                        $daysToRelocateOrder = $this->_getDaysToRelocateOrder($processingDate);

                        $storeReadyDatesRange = $this->_getStoreReadyDatesRange(
                            $store,
                            $daysToRelocateOrder,
                            $daysToRelocateOrder,
                            $processingDate
                        );
                    } else { // bring to primary and relocate
                        $preorderedItems = array_diff_key($outOfStoreItems, $itemsInPrimary);

                        $readyDays = $this->_getPreorderReadyDays($preorderedItems);
                        $daysToRelocateOrderMin = $this->_getDaysToRelocateOrder(
                            $this->_dateHelper->addDays($processingDate, $readyDays['min'])
                        );
                        $daysToRelocateOrderMax = $this->_getDaysToRelocateOrder(
                            $this->_dateHelper->addDays($processingDate, $readyDays['max'])
                        );

                        $storeReadyDatesRange = $this->_getStoreReadyDatesRange(
                            $store,
                            $readyDays['min'] + $daysToRelocateOrderMin,
                            $readyDays['max'] + $daysToRelocateOrderMax,
                            $processingDate
                        );
                    }
                }

                $result['stores'][$storeId] = [
                    'pickup' => [
                        'raw' => $this->_dateHelper->datesRangeToRaw($storeReadyDatesRange),
                        'value' => $storeReadyDatesRange->min ? $this->_getPickingDateString($storeReadyDatesRange->min) : ''
                    ],
                    'availableNow' => false
                ];
            }

            $result['stores'][$storeId]['geoId'] = $store->geoid;

            $sendingDates = $this->_dateHelper->parseString($result['stores'][$storeId]['pickup']['raw']);

            $sendingHour = 12; /** @todo get from store params */

            if ($sendingDates->min && $sendingDates->max &&
                    $sendingDates->min->toUnix() === $sendingDates->max->toUnix() &&
                    $this->_dateHelper->isToday($sendingDates->min) &&
                    $this->_dateHelper->getCurrentDateTime()->hour >= $sendingHour - 1) { // today after sending hour (with 1 houre reserve)
                // calculate from tomorrow
                $sendingDates = $this->_getStoreReadyDatesRange($store, 0, 0, $this->_dateHelper->addDays($sendingDates->min, 1));
            }

            $result['stores'][$storeId]['sending'] = [
                'raw' => $this->_dateHelper->datesRangeToRaw($sendingDates),
                'value' => $this->_getSendingDateString($sendingDates)
            ];

            if ($minSendingDatesRange === null ||
                $minSendingDatesRange->min && $sendingDates->min && $sendingDates->min < $minSendingDatesRange->min
            ) {
                $minSendingDatesRange = $sendingDates;
            }
        }

        if ($minSendingDatesRange) {
            $result['shippingReady'] = [
                'raw'   => $this->_dateHelper->datesRangeToRaw($minSendingDatesRange),
                'value' => $this->_getSendingDateString($minSendingDatesRange)
            ];
        }

        $_orderPickingDatesCache[$hash] = new JSON($result);

        return $_orderPickingDatesCache[$hash];
    }

    /**
     * Get position rate in percent
     *
     * @param   Position $position
     *
     * @return  string
     *
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function getPositionRate(Position $position)
    {
        /** @var Data $promoCode */
        $promoCode = $this->hyper['helper']['promocode']->getSessionData();
        $hasPromo  = $promoCode->find('positions.' . $position->id);
        $listPrice = $position->getListPrice()->val();

        $positionDiscount = $position->getDiscount();

        if ($hasPromo && $listPrice > 0) {
            $promoDiscount = $promoCode->get('rate');
            if ($promoCode->get('type') === PromoCode::TYPE_SALE_FIXED) {
                $promoDiscount = $promoDiscount / $listPrice * 100;
            }

            $positionDiscount = min(max($positionDiscount, $promoDiscount), 100);
        }

        return $positionDiscount . '%';
    }

    /**
     * Get session items.
     *
     * @return  JSON
     *
     * @since   2.0
     */
    public function getServiceItems()
    {
        $session = $this->_session->get();
        $items   = $session->get(self::SESSION_SERVICE_KEY, []);
        ksort($items);

        return new JSON($items);
    }

    /**
     * Get cart session.
     *
     * @return  SessionHelper
     *
     * @since   2.0
     */
    public function getSession()
    {
        return $this->_session;
    }

    /**
     * Get session items.
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getSessionItems()
    {
        $session = $this->_session->get();
        $items   = $session->get(self::SESSION_ITEMS_KEY, []);
        ksort($items);

        return $items;
    }

    /**
     * Get cart url.
     *
     * @param   array $query
     *
     * @return  string
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getUrl(array $query = [])
    {
        return $this->hyper['helper']['route']->url(array_replace($query, [
            'view' => 'cart'
        ]));
    }

    /**
     * Get cart credit url.
     *
     * @param   array $query
     *
     * @return  string
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getUrlCredit(array $query = [])
    {
        return $this->hyper['helper']['route']->url(array_replace($query, [
            'view' => 'credit'
        ]));
    }

    /**
     * Check if all the items in the order are Instock.
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function isOrderInStock()
    {
        $items   = $this->getItems();
        $inStock = true;

        foreach ($items as $itemkey => $item) {
            if (strpos($itemkey, '-in-stock-') || (isset($item['related']) && strpos($item['related'], '-in-stock-'))) {
                continue;
            }

            if (in_array($item['type'], [self::TYPE_POSITION, self::TYPE_CONFIGURATION])) {
                $entity = $this->hyper['helper']['moyskladProduct']->getById($item['id']);
                if (isset($item['savedConfiguration'])) {
                    $entity->set('saved_configuration', (int) $item['savedConfiguration']);
                }
            } else {
                if (isset($item['option'])) {
                    $entity = $this->hyper['helper']['moyskladVariant']->getById($item['option']);
                } else {
                    $entity = $this->hyper['helper']['moyskladPart']->getById($item['id']);
                }
            }

            if (!$entity->isInStock()) {
                $inStock = false;
                break;
            }
        }

        return $inStock;
    }

    /**
     * Check stock item in Order
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function isStockInOrder()
    {
        $items = $this->getSessionItems();

        foreach ($items as $item) {
            if (isset($item['stock_id'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Load button assets.
     *
     * @return  void
     *
     * @todo load widgets only when they needed
     *
     * @since   2.0
     */
    public function loadBtnAssets()
    {
        $this->hyper['helper']['assets']
            ->js('js:widget/item-buttons.js')->widget('body', 'HyperPC.SiteItemButtons', [
                'cartUrl'                => $this->getUrl(),
                'msgAlertError'          => Text::_('COM_HYPERPC_ALERT_ERROR'),
                'msgTryAgain'            => Text::_('COM_HYPERPC_ALERT_TRY_AGAIN'),
                'msgWantRemove'          => Text::_('COM_HYPERPC_ALERT_WANT_TO_REMOVE'),
                'langAddedToCart'        => Text::_('COM_HYPERPC_ADDED_TO_CART'),
                'langContinueShopping'   => Text::_('COM_HYPERPC_CONTINUE_SHOPPING'),
                'langGoToCart'           => Text::_('COM_HYPERPC_GO_TO_CART')
            ]);

        $this->hyper['helper']['assets']
            ->js('js:widget/compare-buttons.js')->widget('body', 'HyperPC.SiteCompareButtons', [
                'compareUrl'             => $this->hyper['route']->getCompareUrl(),
                'compareBtn'             => Text::_('COM_HYPERPC_CONFIGURATOR_COMPARE_BTN'),
                'addToCompareTitle'      => Text::_('COM_HYPERPC_CONFIGURATOR_COMPARE_ADD'),
                'removeFromCompareTitle' => Text::_('COM_HYPERPC_CONFIGURATOR_COMPARE_REMOVE'),
                'addToCompareText'       => Text::_('COM_HYPERPC_COMPARE_ADD_BTN_TEXT'),
                'removeFromCompareText'  => Text::_('COM_HYPERPC_COMPARE_REMOVE_BTN_TEXT'),
            ]);
    }

    /**
     * Remove item from the cart.
     *
     * @param   string $itemKey
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function removeItem($itemKey)
    {
        $items = $this->getSessionItems();

        if (isset($items[$itemKey])) {
            unset($items[$itemKey]);
            $this->_session->set(self::SESSION_ITEMS_KEY, $items);

            $services = $this->getServiceItems();
            if (isset($services[$itemKey])) {
                unset($services[$itemKey]);
                $this->_session->set(self::SESSION_SERVICE_KEY, $services);
            }

            return true;
        }

        return false;
    }

    /**
     * Should captcha be shown
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function showCaptcha()
    {
        return !$this->hyper['user']->id && $this->hyper['params']->get('show_captcha', false);
    }

    /**
     * Update configuration in the cart
     *
     * @param   int $configId
     *
     * @return  void
     *
     * @throws  \Exception
     * @throws  \JBZoo\Utils\Exception
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function updateConfiguration($configId)
    {
        /** @var ConfigurationHelper */
        $configurationHelper = $this->hyper['helper']['configuration'];
        $configuration = $configurationHelper->findById($configId, ['new' => true]);
        $product = $configuration->getProduct();
        $itemKey = $product->getItemKey();

        $sessionItems = $this->getSessionItems();
        if (array_key_exists($itemKey, $sessionItems)) {
            $quantity = $sessionItems[$itemKey]['quantity'];
            unset($sessionItems[$itemKey]);
            foreach ($sessionItems as $key => $item) {
                if (isset($item['related']) && $item['related'] === $itemKey) {
                    unset($sessionItems[$key]);
                }
            }

            $this->_session->set(self::SESSION_ITEMS_KEY, $sessionItems);

            $this->addItem([
                'quantity'           => 1, // TODO set quantity for external parts,
                'savedConfiguration' => $configId,
                'id'                 => $product->id,
                'type'               => self::TYPE_CONFIGURATION,
            ]);
        }
    }

    /**
     * Get days to relocate order between stores.
     *
     * @param   ?Date $fromDate
     *
     * @return  int
     *
     * @since   2.0
     */
    protected function _getDaysToRelocateOrder(Date $fromDate = null)
    {
        $fromDate = $fromDate ?: $this->_dateHelper->getCurrentDateTime();

        $currentDayOfWeek = $fromDate->dayofweek;
        if ($currentDayOfWeek <= 4) { // Monday - Thursday
            return 2;
        }

        return 3;
    }

    /**
     * Get order processing date
     *
     * @return  Date
     *
     * @since   2.0
     */
    protected function _getOrderProcessingDate(): Date
    {
        $schedule = new JSON($this->hyper['params']->get('schedule'));

        return $this->_dateHelper->getNearestScheduleDate($schedule);
    }

    /**
     * Get picking date string
     *
     * @param   Date $pickingDate
     *
     * @return  string
     *
     * @since   2.0
     */
    protected function _getPickingDateString($pickingDate)
    {
        if ($this->_dateHelper->isToday($pickingDate)) {
            return Text::sprintf('COM_HYPERPC_PICKUP_ON', StringHelper::strtolower(Text::_('COM_HYPERPC_TODAY')));
        } elseif ($this->_dateHelper->isTomorrow($pickingDate)) {
            return Text::sprintf('COM_HYPERPC_PICKUP_ON', StringHelper::strtolower(Text::_('COM_HYPERPC_TOMORROW')));
        }

        return Text::sprintf(
            'COM_HYPERPC_PICKUP_SINCE',
            $pickingDate->format(Text::_('COM_HYPERPC_DATE_FORMAT_LONG_NO_YEAR'), true)
        );
    }

    /**
     * Get preorder ready days
     *
     * @return array [
     *  'min' => int,
     *  'max' => int
     * ]
     *
     * @since   2.0
     */
    protected function _getPreorderReadyDays($items)
    {
        $readyDaysMin = 0;
        $readyDaysMax = 0;

        foreach ($items as $item) {
            if ($item instanceof ProductMarker) {
                $daysToWarehouse = $item->getDaysToBuild();
            } else {
                $daysToWarehouse = $this->hyper['helper']['moyskladPart']->getDaysToPreorder($item);
            }

            if ($daysToWarehouse['min'] !== null) { // TODO Throw an error. If null item can't be preordered
                $readyDaysMin = max($readyDaysMin, $daysToWarehouse['min']);
            }

            if ($daysToWarehouse['max'] !== null) {
                $readyDaysMax = max($readyDaysMax, $daysToWarehouse['max']);
            }
        }

        return [
            'min' => $readyDaysMin,
            'max' => $readyDaysMax
        ];
    }

    /**
     * Get sending date string
     *
     * @param   DatesRange $sendingDate
     *
     * @return  string
     *
     * @since   2.0
     */
    protected function _getSendingDateString(DatesRange $sendingDate)
    {
        if (!$sendingDate->min || !$sendingDate->max) {
            return '';
        }

        if ($sendingDate->min->toUnix() === $sendingDate->max->toUnix()) {
            if ($this->_dateHelper->isToday($sendingDate->min)) {
                return Text::_('COM_HYPERPC_TODAY');
            } elseif ($this->_dateHelper->isTomorrow($sendingDate->min)) {
                return Text::_('COM_HYPERPC_TOMORROW');
            }
        }

        return $this->_dateHelper->datesRangeToString($sendingDate);
    }

    /**
     * Get store ready DatesRange
     *
     * @param   Store      $store
     * @param   int        $addDaysMin
     * @param   int        $addDaysMax
     * @param   Date|null  $fromDate
     *
     * @return  DatesRange
     */
    protected function _getStoreReadyDatesRange(Store $store, int $addDaysMin, int $addDaysMax, Date $fromDate = null): DatesRange
    {
        if ($fromDate === null) {
            $fromDate = $this->_getStoreProcessingDate($store);
        }

        $storeSchedule = new JSON($store->params->get('schedule', []));

        $minDate = $this->_dateHelper->addDays($fromDate, $addDaysMin);
        $maxDate = $this->_dateHelper->addDays($fromDate, $addDaysMax);

        $minDate = $this->_dateHelper->getNearestScheduleDate($storeSchedule, $minDate);
        $maxDate = $this->_dateHelper->getNearestScheduleDate($storeSchedule, $maxDate);

        return new DatesRange([
            'min' => $minDate,
            'max' => $maxDate
        ]);
    }

    /**
     * Get order picking dates by store
     *
     * @param   Store       $store
     * @param   Date|null   $fromDate
     *
     * @return  Date
     *
     * @since   2.0
     */
    protected function _getStoreProcessingDate(Store $store, Date $fromDate = null)
    {
        $storeSchedule = new JSON($store->params->get('schedule'));

        return $this->_dateHelper->getNearestScheduleDate($storeSchedule, $fromDate);
    }

    /**
     * Get stores
     *
     * @return  Store[]
     *
     * @since   2.0
     */
    protected function _getStores()
    {
        return $this->hyper['helper']['store']->findAll();
    }

    /**
     * Prepare availability info.
     *
     * @param   array $items
     *
     * @return  JSON [
     *     'itemsByStore' => array,
     *     'itemsInPrimary' => array,
     *     'hasUnavailableItems' => bool
     * ]
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    protected function _prepareAvailabilityInfo($items)
    {
        $result = [
            'itemsByStore'        => [],
            'itemsInPrimary'      => [],
            'hasUnavailableItems' => false
        ];

        $stores = $this->_getStores();

        $itemsStoreAvailability = [];
        foreach ($items as $itemKey => $item) {
            if (!$item instanceof Stockable) {
                continue;
            }

            if ($item instanceof PartMarker && $item->option instanceof OptionMarker && $item->option->id) {
                $itemsStoreAvailability[$itemKey] = $item->option->getAvailabilityByStore();
                $availability = $item->option->getAvailability();
            } else {
                $itemsStoreAvailability[$itemKey] = $item->getAvailabilityByStore();
                $availability = $item->getAvailability();
            }

            if (in_array($availability, [Stockable::AVAILABILITY_OUTOFSTOCK, Stockable::AVAILABILITY_DISCONTINUED])) {
                $result['hasUnavailableItems'] = true;
            }
        }

        $availabilityByStore = [];
        $itemsInPrimary = [];
        foreach ($itemsStoreAvailability as $itemKey => $storeAvailability) {
            foreach ($storeAvailability as $storeId => $quantity) {
                if ($quantity['available']) {
                    $availabilityByStore[$storeId][$itemKey] = $quantity['available'];
                    if (isset($stores[$storeId]) && $stores[$storeId]->params->get('primary', 0, 'int')) {
                        if (isset($itemsInPrimary[$itemKey])) {
                            $itemsInPrimary[$itemKey] += $quantity['available'];
                        } else {
                            $itemsInPrimary[$itemKey] = $quantity['available'];
                        }
                    }
                }
            }
        }
        $result['itemsByStore'] = $availabilityByStore;
        $result['itemsInPrimary'] = $itemsInPrimary;

        return new JSON($result);
    }
}
