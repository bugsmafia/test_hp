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
 * @author      Arten Vyshnevskiy
 */

namespace HYPERPC\Joomla\Model\Entity;

use JBZoo\Data\Data;
use HYPERPC\Data\JSON;
use JBZoo\Utils\Filter;
use Joomla\CMS\Date\Date;
use HYPERPC\Money\Type\Money;
use HYPERPC\Helper\CartHelper;
use HYPERPC\Helper\PositionHelper;
use HYPERPC\ORM\Entity\Traits\AmoCrmLeadTrait;
use HYPERPC\Object\SavedConfiguration\PartData;
use HYPERPC\Object\SavedConfiguration\CheckData;
use HYPERPC\Joomla\Model\Entity\Interfaces\Stockable;
use HYPERPC\Joomla\Model\Entity\Interfaces\PartMarker;
use HYPERPC\Joomla\Model\Entity\Interfaces\OptionMarker;
use HYPERPC\Joomla\Model\Entity\Interfaces\ProductMarker;
use HYPERPC\Object\SavedConfiguration\PartDataCollection;

/**
 * Class SaveConfiguration
 *
 * @package HYPERPC\Joomla\Model\Entity
 *
 * @since   2.0
 */
class SaveConfiguration extends Entity
{

    use AmoCrmLeadTrait;

    const CONTEXT_MOYSKLAD = 'moysklad';

    /**
     * Primary key.
     *
     * @var     int
     *
     * @since   2.0
     */
    public $id = 0;

    /**
     * Configuration context.
     *
     * @var     int
     *
     * @since   2.0
     */
    public $context = self::CONTEXT_MOYSKLAD;

    /**
     * Hold order id.
     *
     * @var     int
     *
     * @since   2.0
     */
    public $order_id = 0;

    /**
     * Configururation product.
     *
     * @var     JSON|ProductMarker
     *
     * @since   2.0
     */
    public $product;

    /**
     * Configuration parts
     *
     * @var     JSON
     *
     * @since   2.0
     */
    public $parts;

    /**
     * Params of configuration.
     *
     * @var     JSON
     *
     * @since   2.0
     */
    public $params;

    /**
     * Is deleted for user flag.
     *
     * @var     bool
     *
     * @since   2.0
     */
    public $deleted;

    /**
     * Configure price.
     *
     * @var     Money
     *
     * @since   2.0
     */
    public $price;

    /**
     * Created datetime.
     *
     * @var     Date
     *
     * @since   2.0
     */
    public $created_time;

    /**
     * Created user.
     *
     * @var     int
     *
     * @since   2.0
     */
    public $created_user_id;

    /**
     * Updated datetime.
     *
     * @var     Date
     *
     * @since   2.0
     */
    public $modified_time;

    /**
     * Hold loaded parts.
     *
     * @var     (PartMarker|MoyskladService)[]
     *
     * @since   2.0
     */
    protected static $_parts = [];

    /**
     * Entity initialize.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function initialize()
    {
        $product = $this->getProduct();
        $this->setRender('Configuration', $product);
    }

    /**
     * Get configuration last modified date.
     *
     * @return  Date
     *
     * @todo Get last modified date
     *
     * @since   2.0
     */
    public function getLastModifiedDate()
    {
        $timeDifference = (time() - $this->modified_time->getTimestamp());
        if ($timeDifference > 0 && $timeDifference <= 1) { // less than 1 second. If modified_date is null
            return $this->created_time;
        }

        return $this->modified_time;
    }

    /**
     * Site view part url.
     *
     * @param   array $query
     *
     * @return  string
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getViewUrl(array $query = [])
    {
        $product = $this->getProduct();

        $routeParams = [
            'id'          => $this->id,
            'view'        => 'configurator_moysklad',
            'product_id'  => $product->id
        ];

        $routeParams['product_folder_id'] = $product->getFolderId();

        return $this->hyper['helper']['route']->url(array_replace($query, $routeParams));
    }

    /**
     * Get config product.
     *
     * @return  ProductMarker
     *
     * @since   2.0
     */
    public function getProduct()
    {
        if ($this->product instanceof JSON) {
            $product = $this->hyper['helper']['moyskladProduct']->findById($this->product->get('id'), ['new' => true]);
            $product->set('saved_configuration', $this->id);
            return $product;
        }

        return $this->product;
    }

    /**
     * Prepare default product configuration to custom saved configuration.
     *
     * @param   ProductMarker|null $product
     *
     * @return  ProductMarker
     *
     * @since   2.0
     */
    public function prepareProductConfiguration($product = null)
    {
        if (!$product instanceof ProductMarker) {
            $product = $this->getProduct();
        }

        $defaultParts   = [];
        $defaultOptions = [];
        $quantityList   = [];

        foreach ((array) $this->parts as $part) {
            $part = new Data($part);
            if ($part->get('option_id') === null) {
                $defaultParts[] = (string) $part->get('id');
            } else {
                $defaultOptions[] = new Data([
                    'part_id'   => $part->get('id'),
                    'option_id' => $part->get('option_id')
                ]);
            }

            $quantityList[$part->get('id')] = $part->get('quantity');
        }

        $product->configuration->set('quantity', $quantityList);

        $defaultOptionsVal = [];
        if (count($defaultOptions)) {
            foreach ($defaultOptions as $option) {
                $defaultOptionsVal[$option->get('part_id')] = Filter::int($option->get('option_id'));
            }
        }

        if (count($defaultParts)) {
            $product->configuration->set('default', $defaultParts);
        }

        $defaultPartOptions = (array) $product->configuration->get('part_options', []);
        foreach ($defaultOptionsVal as $partId => $optionId) {
            if (!array_key_exists($optionId, $defaultPartOptions)) {
                $defaultPartOptions[$optionId] = [
                    'is_default' => true,
                    'part_id'    => $partId
                ];
            }
        }

        if (count($defaultOptionsVal)) {
            $newPartOptionsData = [];
            foreach ($defaultPartOptions as $optionId => $data) {
                $data = new JSON($data);
                $data->set('is_default', false);
                if (in_array(Filter::int($optionId), $defaultOptionsVal)) {
                    $data->set('is_default', true);
                }

                $newPartOptionsData[$optionId] = $data->write();
            }

            $product->configuration
                ->set('option', $defaultOptionsVal)
                ->set('part_options', $newPartOptionsData);
        }

        $product
            ->set('saved_configuration', $this->id);

        return $product;
    }

    /**
     * Get saved configuration part list.
     *
     * @param   bool  $actualPrice  If true - get actual price on today.
     *                              If false - get price from day when saved order.
     *
     * @param   string  $order      Query part and product order.
     *
     * @return  (PartMarker|MoyskladService)[]
     *
     * @throws  \JBZoo\Utils\Exception
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function getParts($actualPrice = false, $order = 'a.id ASC')
    {
        if (empty((array) $this->parts)) {
            return [];
        }

        $hashKey = [$this->id, $this->context, md5((string) $this->parts), (string) $actualPrice, $order];
        $hashKey = md5(implode('///', $hashKey));

        if (!array_key_exists($hashKey, self::$_parts)) {
            self::$_parts[$hashKey] = $this->_getPositions($actualPrice, $order);
        }

        return self::$_parts[$hashKey];
    }

    /**
     * Get part ids.
     *
     * @return  array
     *
     * @throws  \Exception
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function getPartIds()
    {
        if ($this->parts === null) {
            return [];
        }

        $orderId = $this->get('order_id');

        if ($orderId) {
            /** @var Order $order */
            $order             = $this->hyper['helper']['order']->findById($orderId);
            $orderProductData  = new JSON($order->products->find('product-' . $this->product->get('id') . '-' . $this->id));
            $serviceData       = (array) $orderProductData->get('service');

            $parts = $this->hyper['helper']['moyskladPart']->findById(array_keys($this->parts->getArrayCopy()), [
                'select' => ['id', 'group_id']
            ]);

            /** @var Part $part */
            foreach ($parts as $part) {
                if ($part->isService()) {
                    foreach ($serviceData as $groupId => $serviceItem) {
                        if ($part->group_id === Filter::int($groupId)) {
                            unset($parts[$part->id]);
                            $parts[$serviceItem['id']] = new Part([
                                'group_id' => $groupId,
                                'id'       => $serviceItem['id']
                            ]);
                        }
                    }
                }
            }

            return array_keys($parts);
        }

        return array_keys($this->parts->getArrayCopy());
    }

    /**
     * Get configuration name.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getName()
    {
        return (string) $this->id;
    }

    /**
     * Get data for check configuration actuality.
     *
     * @return  CheckData|null
     *
     * @throws  \Exception
     * @throws  \RuntimeException
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function getConfigurationCheckData()
    {
        if (!$this->id) {
            return null;
        }

        $product = $this->getProduct();
        $groups = $this->hyper['helper']['productFolder']->getList(false);
        $allParts = $product->getAllConfigParts();

        $unavalableParts = [];

        $isInCart = $this->isInCart();

        $sumFromData = 0;
        $actualSum = 0;

        $partsData = PartDataCollection::create($this->parts->getArrayCopy());
        $parts = $this->getParts(true);

        foreach ($partsData as $partData) {
            $itemName = [];
            if (array_key_exists($partData->group_id, $groups)) {
                $group = $groups[$partData->group_id];
                $itemName[] = $group->title;
            }

            if (!array_key_exists($partData->id, $parts)) {
                if (!empty($itemName)) {
                    $unavalableParts[] = implode(' ', $itemName);
                }
                continue;
            }

            /** @var PartMarker|MoyskladService $part */
            $part = $parts[$partData->id];

            $unavailable = $part instanceof Stockable && ($part->isOutOfStock() || $part->isDiscontinued());

            if ($part instanceof PartMarker && $partData->option_id) {
                if (!$part->option->id) {
                    $unavailable = true;
                } else {
                    $part->setListPrice($part->option->getListPrice());
                    $isOptionInProduct = $this->hyper['helper']['configurator']->isOptionInConfigurator($product, $part->option);

                    $unavailable = $unavailable || !$isOptionInProduct;
                }
            }

            $isPartInProduct = array_key_exists($partData->id, $allParts);
            if (!$unavailable && $isPartInProduct) {
                $dataPrice = $partData->price * $partData->quantity;
                $sumFromData += $dataPrice;
                $actualSum += $part->getQuantityPrice(false)->val();
                continue;
            }

            $itemName[] = $part->getConfiguratorName($product->id);
            if (!$part->isReloadContentForProduct($product->id) && $part instanceof PartMarker && $part->option instanceof OptionMarker && $part->option->id) {
                $itemName[] = $part->option->getConfigurationName();
            }

            $unavalableParts[] = implode(' ', $itemName);
        }

        $priceDifference = $this->hyper['helper']['money']->get($actualSum - $sumFromData);

        $hasWarnings = false;
        if (!empty($unavalableParts) || ((int) $priceDifference->val() !== 0 && !$this->order_id)) {
            $hasWarnings = true;
        }

        $result = new CheckData([
            'unavalableParts'  => $unavalableParts,
            'lastModifiedDate' => $this->getLastModifiedDate(),
            'priceDifference'  => $priceDifference,
            'isInCart'         => $isInCart,
            'hasWarnings'      => $hasWarnings
        ]);

        return $result;
    }

    /**
     * Get configuration context
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Get discounted price.
     *
     * @return  Money
     *
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     *
     * @todo save discount data in configuration
     */
    public function getDiscountedPrice()
    {
        /** @todo calculate discount for moysklad positions */

        return clone $this->price;
    }

    /**
     * Get order entity.
     *
     * @return  Order
     *
     * @since   2.0
     */
    public function getOrder()
    {
        return $this->hyper['helper']['order']->findById($this->getOrderId());
    }

    /**
     * Get configuration order id.
     *
     * @return  int|null
     *
     * @since   2.0
     */
    public function getOrderId()
    {
        if ($this->order_id) {
            return $this->order_id;
        }

        if ($this->created_user_id) {
            $orders = $this->hyper['helper']['order']->findByCreatedUserId([$this->created_user_id]);
            /** @var Order $order */
            foreach ($orders as $order) {
                foreach ($order->products->getArrayCopy() as $productData) {
                    $productData = new JSON($productData);
                    if ($productData->get('saved_configuration', 0, 'int') === $this->id) {
                        return $order->id;
                    }
                }
            }
        }

        return null;
    }

    /**
     * Check configuration belongs to order.
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function isBelongsToOrder()
    {
        return (bool) $this->getOrderId();
    }

    /**
     * Is configuration in cart
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function isInCart()
    {
        /** @var CartHelper */
        $cartHelper = $this->hyper['helper']['cart'];

        $product = $this->getProduct();
        $itemKey = $product->getItemKey();
        $cartItems = $cartHelper->getSessionItems();

        return array_key_exists($itemKey, $cartItems);
    }

    /**
     * Checks if the configuration product exists
     *
     * @return  bool
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function isProductExists()
    {
        if (empty($this->product)) {
            return false;
        } elseif ($this->product instanceof ProductMarker) {
            return !empty($this->product->id);
        }

        $productId = $this->product->get('id', 0);
        $product   = $this->hyper['helper']['moyskladProduct']->findById($productId, ['select' => ['a.id']]);

        return !empty($product->id);
    }

    /**
     * Checks if the configuration is locked to update
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function isReadonly()
    {
        return $this->isBelongsToOrder() || $this->params->get('readonly', false, 'bool');
    }

    /**
     * Lock configuration from updating
     *
     * @since   2.0
     */
    public function setReadonly()
    {
        $this->params->set('readonly', true);
    }

    /**
     * Get saved configuration position list.
     *
     * @param   bool  $actualPrice  If true - get the current price.
     *                              If false - get the price at the time of saving the order.
     *
     * @param   string  $order      Query position order.
     *
     * @return  (PartMarker|MoyskladService)[]
     *
     * @throws  \Exception
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    protected function _getPositions($actualPrice = false, $order = 'a.id ASC')
    {
        $positionsData = PartDataCollection::create($this->parts->getArrayCopy());

        $positionIds = array_keys($positionsData->items());

        $order = preg_replace('/group_id/', 'product_folder_id', $order);

        /** @var PositionHelper */
        $positionHelper = $this->hyper['helper']['position'];

        /** @var Position[] */
        $positions = $positionHelper->getByIds($positionIds, ['a.*'], $order, 'id', [], false);
        $configPart = [];
        foreach ($positions as $id => $position) {
            $configPart[$id] = clone $positionHelper->expandToSubtype($position);
        }

        /** @var MoyskladPart|MoyskladService $position */
        foreach ($configPart as $position) {
            /** @var PartData */
            $partData = $positionsData->offsetGet($position->id);
            $position->quantity = $partData->quantity;

            if ($partData->option_id) {
                $option = $this->hyper['helper']['moyskladVariant']->findById($partData->option_id);
                $position->set('option', $option);
                $position->list_price = clone $option->list_price;
                $position->sale_price = clone $option->sale_price;
            }

            if (!$actualPrice) {
                $position->setListPrice(
                    $this->hyper['helper']['money']->get($partData->price)
                );
            }
        }

        return $configPart;
    }

    /**
     * Fields of integer data.
     *
     * @return  array
     *
     * @since   2.0
     */
    protected function _getFieldInt()
    {
        return ['id', 'order_id', 'created_user_id'];
    }

    /**
     * Fields of JSON data.
     *
     * @return  array
     *
     * @since   2.0
     */
    protected function _getFieldJsonData()
    {
        return ['params', 'product', 'parts'];
    }
}
