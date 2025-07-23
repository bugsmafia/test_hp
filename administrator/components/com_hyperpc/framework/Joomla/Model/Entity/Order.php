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
 * @author      Roman Evsyukov
 */

namespace HYPERPC\Joomla\Model\Entity;

use JBZoo\Data\JSON;
use JBZoo\Utils\Str;
use Joomla\CMS\Uri\Uri;
use JBZoo\Utils\Filter;
use Joomla\CMS\Date\Date;
use JBZoo\Utils\Exception;
use Cake\Utility\Inflector;
use HYPERPC\Elements\Manager;
use HYPERPC\Elements\Element;
use HYPERPC\Money\Type\Money;
use Joomla\CMS\Language\Text;
use HYPERPC\Helper\MoneyHelper;
use HYPERPC\Helper\OrderHelper;
use HYPERPC\Helper\WorkerHelper;
use HYPERPC\Elements\ElementCredit;
use HYPERPC\Object\Date\DatesRange;
use HYPERPC\Joomla\Model\ModelAdmin;
use HYPERPC\Object\Order\PositionData;
use HYPERPC\Render\Order as RenderOrder;
use HYPERPC\Helper\MoyskladVariantHelper;
use HYPERPC\ORM\Entity\Traits\AmoCrmLeadTrait;
use HYPERPC\Helper\MoyskladCustomerOrderHelper;
use HYPERPC\Object\Processingplan\PlanItemData;
use HYPERPC\Object\Order\PositionDataCollection;
use HYPERPC\MoySklad\Entity\Document\CustomerOrder;
use HYPERPC\Joomla\Model\Entity\Interfaces\Stockable;
use JBZoo\SimpleTypes\Exception as SimpleTypesException;
use HYPERPC\Joomla\Model\Entity\Interfaces\ProductMarker;
use HYPERPC\Object\Processingplan\PlanItemDataCollection;
use HYPERPC\Object\SavedConfiguration\PartDataCollection;

/**
 * Class Order
 *
 * @method      RenderOrder render()
 *
 * @package     \HYPERPC\Joomla\Model\Entity
 *
 * @since       2.0
 */
class Order extends Entity
{

    use AmoCrmLeadTrait;

    private const PARAM_YM_COUNTER = 'ym_counter';
    private const PARAM_YM_UID     = 'ym_uid';

    const ORDER_TYPE_UPGRADE     = 'upgrade';
    const ORDER_TYPE_NOTEBOOKS   = 'notebooks';
    const ORDER_TYPE_PRODUCTS    = 'products';
    const ORDER_TYPE_ACCESSORIES = 'accessories';

    const BUYER_TYPE_INDIVIDUAL   = 0;
    const BUYER_TYPE_LEGAL        = 1;
    const BUYER_TYPE_ENTREPRENEUR = 2;

    /**
     * Google id.
     *
     * @var     string
     *
     * @since   2.0
     */
    public $cid;

    /**
     * Flag to load to 1c.
     *
     * @var     bool
     *
     * @since   2.0
     */
    public $to_1c;

    /**
     * Order context.
     *
     * @var     string
     *
     * @since   2.0
     */
    public $context;

    /**
     * Created order date time.
     *
     * @var     Date
     *
     * @since   2.0
     */
    public $created_time;

    /**
     * Created user id.
     *
     * @var     int
     *
     * @since   2.0
     */
    public $created_user_id;

    /**
     * Delivery type.
     *
     * @var     string
     *
     * @since   2.0
     */
    public $delivery_type;

    /**
     * Order elements.
     *
     * @var     JSON
     *
     * @since   2.0
     */
    public $elements;

    /**
     * Is company order.
     *
     * @var     int
     *
     * @since   2.0
     */
    public $form = 0;

    /**
     * Order helper object.
     *
     * @var     OrderHelper
     *
     * @since   2.0
     */
    public $helper;

    /**
     * Order id.
     *
     * @var     int
     *
     * @since   2.0
     */
    public $id;

    /**
     * Modified date time.
     *
     * @var     Date
     *
     * @since   2.0
     */
    public $modified_time;

    /**
     * Modified user id.
     *
     * @var     int
     *
     * @since   2.0
     */
    public $modified_user_id;

    /**
     * Order history status.
     *
     * @var     JSON
     *
     * @since   2.0
     */
    public $status_history;

    /**
     * Order parts.
     *
     * @var     JSON
     *
     * @since   2.0
     */
    public $parts;

    /**
     * Payment type.
     *
     * @var     string
     *
     * @since   2.0
     */
    public $payment_type;

    /**
     * Responsible user.
     *
     * @var     int
     *
     * @since   2.0
     */
    public $worker_id = WorkerHelper::DEFAULT_WORKER_ID;

    /**
     * Order positions.
     *
     * @var     JSON
     *
     * @since   2.0
     */
    public $positions;

    /**
     * Order products.
     *
     * @var     JSON
     *
     * @since   2.0
     */
    public $products;

    /**
     * Order promo code.
     *
     * @var     JSON
     *
     * @since   2.0
     */
    public $promo_code;

    /**
     * Order status.
     *
     * @var     int
     *
     * @since   2.0
     */
    public $status;

    /**
     * Total order price.
     *
     * @var     Money
     *
     * @since   2.0
     */
    public $total;

    /**
     * Params.
     *
     * @var     JSON
     *
     * @since   2.0
     */
    public $params;

    /**
     * Hold status extra text
     *
     * @var     array
     *
     * @since   2.0
     */
    protected static $_statusExtraText = [];

    /**
     * Calculate order total price.
     *
     * @param   bool $includeDelivery
     *
     * @return  Money
     *
     * @throws  Exception
     * @throws  SimpleTypesException
     *
     * @since   2.0
     */
    public function calculateTotal($includeDelivery = false)
    {
        $positions = PositionDataCollection::create((array) $this->positions);
        /** @var Money */
        $total = $this->hyper['helper']['money']->get(0);
        foreach ($positions as $positionData) {
            $price = $positionData->price * (1 - $positionData->discount / 100) * $positionData->quantity;
            $total->add($price);
        }

        if ($includeDelivery) {
            $deliveryPrice = $this->getCustomDeliveryPrice();
            if (!$deliveryPrice->isEmpty() && $deliveryPrice->val() > 0) {
                $total->add($deliveryPrice);
            }
        }

        $items = array_merge((array) $this->parts, (array) $this->products);
        foreach ($items as $item) {
            $price = $item['priceWithRate'] * $item['quantity'];
            $total->add(round($price));
        }

        return $total;
    }

    /**
     * Get company name
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getCompanyName()
    {
        return (string) $this->elements->find('company.name', '', 'strip');
    }

    /**
     * Get company inn
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getCompanyInn()
    {
        return (string) $this->elements->find('company.value', '', 'strip');
    }

    /**
     * Get current total price.
     *
     * @return  Money
     *
     * @throws  Exception
     * @throws  SimpleTypesException
     *
     * @since   2.0
     */
    public function getTotal()
    {
        $total = clone $this->total;
        $deliveryPrice = $this->getCustomDeliveryPrice();
        if (!$deliveryPrice->isEmpty() && $deliveryPrice->val() > 0) {
            $total->add($deliveryPrice);
        }

        return $total;
    }

    /**
     * Get total discount price.
     *
     * @return  Money
     *
     * @throws  SimpleTypesException
     *
     * @since   2.0
     */
    public function getDiscountPrice()
    {
        $discount = new Money();
        $items    = $this->getItems();
        $products = (array) $items['products'];
        $parts    = (array) $items['parts'];

        if (count($products)) {
            /** @var Product $product */
            foreach ($products as $product) {
                $hasPromo   = $product->get('rate');
                $unitPrice  = clone $product->price;
                $promoPrice = clone $unitPrice;

                if ($hasPromo) {
                    $promoPrice->add('-' . $product->get('rate') . '%');
                }

                $discountProduct = clone $unitPrice;
                $discountProduct->add(-$promoPrice->val())->multiply($product->quantity);

                $discount->add($discountProduct);
            }
        }

        if (count($parts)) {
            /** @var Product $part */
            foreach ($parts as $part) {
                $hasPromo   = $part->get('rate');
                $unitPrice  = clone $part->price;
                $promoPrice = clone $unitPrice;

                if ($hasPromo) {
                    $promoPrice->add('-' . $part->get('rate') . '%');
                }

                $discountPart = clone $unitPrice;
                $discountPart->add(-$promoPrice->val())->multiply($part->quantity);
                $discount->add($discountPart);
            }
        }

        return $discount;
    }

    /**
     * Get Moysklad edit url.
     *
     * @return  string|null
     *
     * @since   2.0
     */
    public function getMoyskladEditUrl()
    {
        $uuid = $this->getUuid();
        if (!empty($uuid)) {
            return $this->hyper['helper']['moysklad']->getAppPath('customerorder') . "/edit?id={$uuid}";
        }

        return null;
    }

    /**
     * Get moysklad uuid
     *
     * @return  string|null
     *
     * @since   2.0
     */
    public function getUuid()
    {
        return $this->params->get('moysklad_uuid', '', 'strip');
    }

    /**
     * Get client YandexMetrica counter id
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getYmCounter()
    {
        return $this->params->get(self::PARAM_YM_COUNTER, '', 'strip');
    }

    /**
     * Get client YandexMetrica uid
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getYmUid()
    {
        return $this->params->get(self::PARAM_YM_UID, '', 'strip');
    }

    /**
     * Check delivery type by alias.
     *
     * @param   string $type
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function deliveryIs($type)
    {
        return ($this->delivery_type === Str::low($type));
    }

    /**
     * Get custom delivery price.
     *
     * @return  Money
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public function getCustomDeliveryPrice()
    {
        $value = $this->elements->find('yandex_delivery.shipping_cost', 0, 'float');
        return $this->hyper['helper']['money']->get($value);
    }

    /**
     * Get account allowed status list.
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getAllowedStatusList()
    {
        $apiStatusList = $this->hyper['helper']['crm']->getEventsByOrder($this);

        $allowedStatusList = [];
        $allowedStatuses   = (array) $this->hyper['params']->get('account_allowed_status');
        $statusList        = $this->status_history->getArrayCopy();

        if (count($apiStatusList)) {
            /** @var Status $status */
            foreach ($apiStatusList as $status) {
                if (in_array((string) $status->id, $allowedStatuses)) {
                    $allowedStatusList[] = [
                        'timestamp' => (int) $status->params->get('timestamp'),
                        'statusId'  => $status->id
                    ];
                }
            }
        } else {
            foreach ($statusList as $status) {
                $status = new JSON($status);
                if (in_array((string) $status->get('statusId'), $allowedStatuses)) {
                    $allowedStatusList[] = $status->getArrayCopy();
                }
            }
        }

        return $allowedStatusList;
    }

    /**
     * Get order user name.
     *
     * @return  mixed
     *
     * @since   2.0
     */
    public function getBuyer()
    {
        return Inflector::humanize($this->elements->find('username.value', '', 'strip'));
    }

    /**
     * Get order user type.
     *
     * @return  int
     *
     * @since   2.0
     */
    public function getBuyerOrderType()
    {
        if ($this->isCredit()) {
            return self::BUYER_TYPE_INDIVIDUAL;
        }

        return $this->getBuyerOrderMethod();
    }

    /**
     * Get order user email.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getBuyerEmail()
    {
        return $this->elements->find('email.value', '', 'strip');
    }

    /**
     * Has order products from stock
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function hasInstockProducts()
    {
        $productsData = $this->products->getArrayCopy();
        foreach (array_keys($productsData) as $itemKey) {
            if (strpos($itemKey, 'in-stock') !== false) {
                return true;
            }
        }

        $positionsData = PositionDataCollection::create((array) $this->positions);
        /** @var PositionData $data */
        foreach ($positionsData as $itemKey => $data) {
            if ($data->type !== 'productvariant') {
                continue;
            }

            $stocks = $this->hyper['helper']['moyskladStock']->getItems([
                'itemIds'   => [$data->id],
                'optionIds' => [$data->option_id]
            ]);

            if (!empty($stocks)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check is credit form.
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function isCredit()
    {
        $result = $this->form === HP_ORDER_FORM_CREDIT;

        if (!$result && $this->payment_type === HP_PAYMENT_TYPE_CREDIT) {
            return true;
        }

        return $result;
    }

    /**
     * Get order user phone.
     *
     * @param   bool  $clear
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getBuyerPhone($clear = false)
    {
        $number = $this->elements->findString('phone.value');

        if (empty($number)) {
            return $number;
        }

        if ($clear) {
            $number = str_replace([' ', '-', '(', ')'], '', $number);
        }

        return trim(strip_tags($number));
    }

    /**
     * Get delivery element.
     *
     * @return  \ElementOrderYandexDelivery|Element
     *
     * @throws  \Exception
     * @throws  Exception
     *
     * @since   2.0
     */
    public function getDelivery()
    {
        return Manager::getInstance()->create('yandex_delivery', 'order', [
            'data' => (array) $this->elements->get('yandex_delivery')
        ]);
    }

    /**
     * Get delivery type.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getDeliveryType()
    {
        $service = $this->elements->find('yandex_delivery.delivery_service', '', 'strip');
        if ($service) {
            return $service;
        }

        return Text::_('COM_HYPERPC_ORDER_DELIVERY_TYPE_NOT_SETUP');
    }

    /**
     * Get order worker.
     *
     * @return  Worker
     *
     * @since   2.0
     */
    public function getWorker()
    {
        return $this->hyper['helper']['worker']->findById($this->worker_id);
    }

    /**
     * Get edit order link url.
     *
     * @return  string
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getEditUrl()
    {
        $query = [
            'id'     => $this->id,
            'view'   => 'order',
            'layout' => 'edit'
        ];

        $view = $this->hyper['input']->get('view');
        if ($view !== null) {
            $query['from_view'] = $view;
        }

        return $this->hyper['helper']['route']->url($query, false);
    }

    /**
     * Get order elements.
     *
     * @return  Element[]
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getElements()
    {
        static $elements = [];

        if (!count($elements)) {
            $manager     = Manager::getInstance();
            $position    = 'order_form'; //((int) $this->form === HP_ORDER_FORM_CREDIT) ? 'credit_form' : 'order_form';
            $elementList = (array) $manager->getByPosition($position);

            /** @var Element $element */
            foreach ($elementList as $element) {
                $data = (array) $this->elements->find($element->getIdentifier(), []);
                if ($element->getType() === 'methods') {
                    $data['company'] = $this->elements->find('company.value', '', 'strip');
                }

                $element->setConfig(['data' => $data]);
                $elements[] = $element;
            }
        }

        return $elements;
    }

    /**
     * Get order items (parts and products).
     *
     * @param   string|mixed    $type           Type of parts or products
     * @param   bool            $actualPrice    If true - get actual price on today. If false - get price from day when
     *                                          saved order.
     * @param   string          $order          Query part and product order.
     *
     * @return  array
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getItems($type = null, $actualPrice = false, $order = 'a.id ASC')
    {
        $items  = [
            'positions' => $this->getPositions($actualPrice, $order, true),
            'products'  => $this->getProducts($actualPrice, $order, true),
            'parts'     => $this->getParts($actualPrice, $order)
        ];

        if ($type === 'parts') {
            return $items['parts'];
        } elseif ($type === 'products') {
            return $items['products'];
        } elseif ($type === 'positions') {
            return $items['positions'];
        }

        return $items;
    }

    /**
     * Get credit method.
     *
     * @return  mixed
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public function getCreditMethod()
    {
        return $this->elements->find('credits.value', '', 'strip');
    }

    /**
     * Get order name.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getName()
    {
        return $this->helper->getName($this->id);
    }

    /**
     * Get AmoCRM lead url.
     *
     * @deprecated  Use $this->getAmoLeadUrl()
     *
     * @return      string|null
     *
     * @since       2.0
     */
    public function getAmoUrl()
    {
        return $this->getAmoLeadUrl();
    }

    /**
     * Get product for review
     *
     * @return  ProductMarker|null
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getReviewProduct()
    {
        if ($this->isSold() && !$this->hasReview()) {
            $positions = $this->getPositions();
            $productPositions = array_filter($positions, function ($position) {
                return $position instanceof MoyskladProduct;
            });

            if (count($productPositions) === 1) {
                return current($productPositions);
            }
        }

        return null;
    }

    /**
     * Checks if the order has any positions that are not in stock.
     *
     * @return  bool
     */
    public function hasZeroStockItems(): bool
    {
        $positions = $this->getPositions();
        foreach ($positions as $itemKey => $position) {
            if (($position instanceof Stockable) && !$position->hasBalance()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check has product.
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function hasProducts()
    {
        $positionsData = PositionDataCollection::create((array) $this->positions);
        $productPositions = array_filter($positionsData->items(), function ($positionData) {
            /** @var PositionData $positionData */
            return $positionData->type === 'productvariant';
        });

        if (count($productPositions)) {
            return true;
        }

        $products = $this->products->getArrayCopy();
        if (count((array) $products)) {
            return true;
        }

        return false;
    }

    /**
     * Check has notebook.
     *
     * @return  bool
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function hasNotebooks()
    {
        $positions = $this->getPositions();
        $products = $this->getProducts();
        foreach (array_merge($positions, $products) as $item) {
            if ($item instanceof ProductMarker && $item->getFolder()->getItemsType() === 'notebook') {
                return true;
            }
        }

        return false;
    }

    /**
     * Check game in order.
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function hasGame()
    {
        $parts     = (array) $this->parts->getArrayCopy();
        $gameGroup = (int) $this->hyper['params']->get('game_group');

        foreach ($parts as $part) {
            $part = new JSON($part);
            if ($gameGroup === (int) $part->get('group_id')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check only accessories in order.
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function hasOnlyAccessories()
    {
        $products = $this->products->getArrayCopy();
        if (count($products)) {
            return false;
        }

        // Check positions
        $positionsData = PositionDataCollection::create((array) $this->positions);
        $productPositions = array_filter($positionsData->items(), function ($positionData) {
            /** @var PositionData $positionData */
            return $positionData->type === 'productvariant';
        });

        if (count($productPositions) > 0) {
            return false;
        }

        // Check parts
        $parts = $this->parts->getArrayCopy();
        if (count($parts)) {
            $hasNotebook = false;
            $notebookGroup = (array) $this->hyper['params']->get('notebook_groups', []);
            foreach ($parts as $part) {
                $part = new JSON($part);
                if (in_array((string) $part->get('group_id'), $notebookGroup)) {
                    $hasNotebook = true;
                    break;
                }
            }

            if ($hasNotebook) {
                return false;
            }
        }

        return true;
    }

    /**
     * Checks if there are upgrade parts in the order.
     *
     * @return  bool
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function hasUpgradeAccessories()
    {
        // Check positions
        $positions = $this->getPositions();
        foreach ($positions as $position) {
            /** @todo check if only parts may use for upgrade */
            if ($position instanceof MoyskladPart && $position->isOnlyForUpgrade()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get order type
     *
     * @return  string
     *
     * @throws  \Exception
     *
     * @since 2.0
     */
    public function getOrderType()
    {
        if ($this->hasUpgradeAccessories()) {
            return self::ORDER_TYPE_UPGRADE;
        }

        if ($this->hasNotebooks()) {
            return self::ORDER_TYPE_NOTEBOOKS;
        }

        if ($this->hasProducts()) {
            return self::ORDER_TYPE_PRODUCTS;
        }

        return self::ORDER_TYPE_ACCESSORIES;
    }

    /**
     * Is the buyer a company
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function isBuyerACompany()
    {
        if (in_array($this->getBuyerOrderMethod(), [self::BUYER_TYPE_LEGAL, self::BUYER_TYPE_ENTREPRENEUR])) {
            return true;
        }

        return false;
    }

    /**
     * Check if upgrade type order
     *
     * @return bool
     *
     * @since  2.0
     */
    public function isUpgradeOrder()
    {
        if ($this->getOrderType() === self::ORDER_TYPE_UPGRADE) {
            return true;
        }

        return false;
    }

    /**
     * Get order parts.
     *
     * @param   bool    $actualPrice    If true - get actual price on today. If false - get price from day when saved
     *                                  order.
     * @param   string  $order          Query part order.
     *
     * @return  Part[]
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getParts($actualPrice = false, $order = 'a.id ASC')
    {
        return $this->parts->getArrayCopy();
    }

    /**
     * Get order positions.
     *
     * @param   bool    $actualPrice    If true - get actual price on today. If false - get price from day when saved
     *                                  order.
     * @param   string  $order          Query position order.
     * @param   bool    $setPartItems   Flag of load configuration parts from order data.
     *
     * @return  Position[]
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getPositions($actualPrice = false, $order = 'a.id ASC', $setPartsToProduct = false)
    {
        static $positions = [];

        $hash = implode('///', [$this->id, $actualPrice, $order]);
        if (!array_key_exists($hash, $positions)) {
            $positionsData = new PositionDataCollection();
            foreach ($this->positions as $itemKey => $data) {
                if (isset($data['rate'])) { // data from cart
                    $data['id'] = (int) $data['id'];
                    $data['price'] = (int) $data['price'];
                    $data['discount'] = 0.0;
                    $data['quantity'] = (int) $data['quantity'];
                    $data['name'] = '';
                    $data['vat'] = 20;
                    $data['type'] = '';

                    if (isset($data['saved_configuration']) && !(empty($data['saved_configuration']))) {
                        $data['option_id'] = (int) $data['saved_configuration'];
                    } else {
                        $data['option_id'] = empty($data['option_id']) ? null : (int) $data['option_id'];
                    }
                }

                $data['discount'] = (float) $data['discount'];

                unset($data['saved_configuration']);
                unset($data['rate']);

                $positionsData->offsetSet($itemKey, new PositionData($data));
            }

            if (count($positionsData)) {
                $positionsIds = array_map(function (PositionData $positionData) {
                    return $positionData->id;
                }, $positionsData->items());

                /** @var \HyperPcModelPosition */
                $positionModel = ModelAdmin::getInstance('position');
                $_positions = [];
                foreach (array_unique($positionsIds) as $id) {
                    $_positions[$id] = $positionModel->getItem($id);
                }

                foreach ($positionsData->items() as $itemKey => $positionData) {
                    $id = $positionData->id;
                    if (array_key_exists($id, $_positions)) {
                        /** @var Position $position */
                        $position = clone $_positions[$id];
                        $position->set('quantity', $positionData->quantity);

                        if ($positionData->option_id) {
                            if ($position instanceof MoyskladPart) {
                                /** @var MoyskladVariant */
                                $option = $this->hyper['helper']['moyskladVariant']->findById($positionData->option_id, ['a.*'], [], false);
                                $position->set('option', $option);
                                $position->setListPrice($option->getListPrice());
                            } elseif ($position instanceof MoyskladProduct) {
                                $position->set('saved_configuration', $positionData->option_id);

                                if ($setPartsToProduct && $positionData->option_id) {
                                    $this->_setMoyskladProductParts($position, $positionData->option_id);
                                }
                            }
                        }

                        if ($actualPrice === false) {
                            $position->setListPrice($this->hyper['helper']['money']->get($positionData->price));
                        }

                        $position->setSalePrice(
                            $position->list_price->multiply((100 - $positionData->discount) / 100, true)->round(0)
                        );

                        $positions[$hash][$itemKey] = $position;
                    }
                }
            } else {
                $positions[$hash] = [];
            }
        }

        if (isset($positions[$hash])) {
            return $positions[$hash];
        }

        return [];
    }

    /**
     * Get buyer order method.
     *
     * @return  int
     *
     * @since   2.0
     */
    public function getBuyerOrderMethod()
    {
        return $this->elements->find('methods.value', self::BUYER_TYPE_INDIVIDUAL, 'int');
    }

    /**
     * Get buyer type as string.
     *
     * @return  string|null
     *
     * @since   2.0
     */
    public function getBuyerType()
    {
        $value = $this->getBuyerOrderMethod();

        try {
            /** @var \ElementOrderMethods|null $element */
            $element = Manager::getInstance()->create('methods', 'order');
        } catch (\Throwable $th) {
            return null;
        }

        return $element?->getMethodTitle($value) ?? null;
    }

    /**
     * Get payment type.
     *
     * @return  Element|null
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public function getPayment()
    {
        $payments = (array) $this->elements->get('payments');

        //  Legacy for Credits.
        if (!count($payments) && $this->isCredit()) {
            $payments = (array) $this->elements->get('credits');
        }

        /** @var \ElementOrderPayments $element */
        $element = Manager::getInstance()->create('payments', 'order', [
            'data' => array_merge($payments, [
                'order' => $this
            ])
        ]);

        return $element->getMethod();
    }

    /**
     * Get order products.
     *
     * @param   bool    $actualPrice        If true - get actual price on today. If false - get price from day when
     *                                      saved order.
     * @param   string  $order              Query part order.
     * @param   bool    $setPartItems       Flag of load configuration parts from order data.
     *
     * @return  array
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getProducts($actualPrice = false, $order = 'a.id ASC', $setPartItems = false)
    {
        return $this->products->getArrayCopy();
    }

    /**
     * Get order ready dates
     *
     * @return  DatesRange
     *
     * @since   2.0
     */
    public function getReadyDates(): DatesRange
    {
        $dates = (string) $this->elements->find('yandex_delivery.store_pickup_dates', '', 'strip');
        if ((bool) $this->elements->find('yandex_delivery.need_shipping', '', 'strip')) {
            $minDateStr = (string) $this->elements->find('yandex_delivery.sending_date_min', '', 'strip');
            $maxDateStr = (string) $this->elements->find('yandex_delivery.sending_date_max', '', 'strip');
            $dates = $minDateStr . ' - ' . $maxDateStr;
        }

        return $this->hyper['helper']['date']->parseString($dates);
    }

    /**
     * Get order status  string.
     *
     * @param   null|int $status
     *
     * @return  Status
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getStatus($status = null)
    {
        if ($status === null) {
            $status = $this->status;
        }

        $statusList = $this->hyper['helper']['status']->getStatusList();

        $status = Filter::int($status);
        if (isset($statusList[$status])) {
            return $statusList[$status];
        }

        return new Status([]);
    }

    /**
     * Get initial order status
     *
     * @return string
     *
     * @throws Exception
     *
     * @since 2.0
     *
     * @deprecated
     */
    public function getInitialStatus()
    {
        if ($this->isCredit()) {
            $pipeline = $this->isUpgradeOrder() ? 'pipeline_credit_upgrade' : 'pipeline_credit';
        } else {
            $pipeline = 'pipeline_default';
        }
        $manager      = Manager::getInstance();
        $amoCrmConfig = $manager->getElement('order_after_save', 'amo_crm')->getConfig();

        return $amoCrmConfig->get($pipeline);
    }

    /**
     * Get initial order status id
     *
     * @return  int
     *
     * @since   2.0
     */
    public function getInitialStatusId()
    {
        $statusDefault       = $this->hyper['params']->get('order_default_status', 1, 'int');
        $statusCredit        = $this->hyper['params']->get('order_default_status_credit', 1, 'int');
        $statusAccessories   = $this->hyper['params']->get('order_default_status_accessories', 1, 'int');
        $statusUpgrade       = $this->hyper['params']->get('order_default_status_accessories_upgrade', 1, 'int');
        $statusUpgradeCredit = $this->hyper['params']->get('order_default_status_credit_upgrade', 1, 'int');

        if ($this->isUpgradeOrder()) {
            if ($this->isCredit()) {
                return $statusUpgradeCredit;
            }

            return $statusUpgrade;
        }

        if ($this->isCredit()) {
            return $statusCredit;
        }

        if ($this->hasOnlyAccessories()) {
            return $statusAccessories;
        }

        return $statusDefault;
    }

    /**
     * Get site view order link.
     *
     * @param   array $query
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getViewUrl(array $query = [])
    {
        return $this->hyper['route']->build([
            'view'  => 'order',
            'id'    => $this->id,
            'token' => $this->getToken()
        ]);
    }

    /**
     * Get site current view order link.
     *
     * @param   bool    $isFullUrl
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getCurrentViewUrl($isFullUrl = false)
    {
        $return = $this->getViewUrl();

        if ($this->created_user_id) {
            $return = $this->getAccountViewUrl();
        }

        return ($isFullUrl) ? Uri::base() . trim($return, '/') : $return;
    }

    /**
     * Get account view order url.
     *
     * @param   bool  $isFullUrl
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getAccountViewUrl($isFullUrl = false)
    {
        return $this->hyper['route']->build([
            'view' => 'profile_order',
            'id'   => $this->id,
        ], $isFullUrl);
    }

    /**
     * Get unique order token.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getToken()
    {
        return md5(implode(':', [
            $this->id,
            $this->cid,
            $this->form,
            $this->status,
            $this->created_time->format('d.m.Y')
        ]));
    }

    /**
     * Get order created user.
     *
     * @param   bool $loadFields    Load user custom fields.
     *
     * @return  \HYPERPC\ORM\Entity\User
     *
     * @since   2.0
     */
    public function getCreatedUser($loadFields = true)
    {
        return $this->hyper['helper']['user']->findById($this->created_user_id, ['load_fields' => $loadFields]);
    }

    /**
     * Initialize entity.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function initialize()
    {
        parent::initialize();
        $this->helper = $this->hyper['helper']['order'];
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
        $fields   = parent::_getFieldInt();
        $fields[] = 'worker_id';
        return $fields;
    }

    /**
     * Set status to current order object
     *
     * @param   Status $status
     *
     * @since   2.0
     */
    public function setStatus(Status $status)
    {
        if (!$status->id || $status->id === $this->status) {
            return;
        }

        $this->status = $status->id;

        $this->status_history->append([
            'statusId'  => $status->id,
            'timestamp' => time()
        ]);
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
        return ['params', 'parts', 'products', 'positions', 'elements', 'status_history'];
    }

    /**
     * Setup parts from configuration.
     *
     * @param   MoyskladProduct $product
     * @param   int $configurationId
     *
     * @return  void
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    protected function _setMoyskladProductParts(MoyskladProduct &$product, int $configurationId)
    {
        $db = $this->hyper['db'];

        /** @var MoneyHelper */
        $moneyHelper = $this->hyper['helper']['money'];

        /** @var MoyskladVariantHelper */
        $optionHelper = $this->hyper['helper']['moyskladVariant'];

        $configuration = $this->hyper['helper']['configuration']->findById($configurationId);
        $processingPlan = $this->hyper['helper']['processingplan']->findById($configurationId);

        $partDataCollection = PartDataCollection::create((array) $configuration->parts);
        $planItemDataCollection = PlanItemDataCollection::create((array) $processingPlan->parts);

        $partIds = array_map(function (PlanItemData $planItem) {
            return $planItem->id;
        }, $planItemDataCollection->items());

        $conditions = [];
        if (count($partIds)) {
            $conditions = [
                $db->qn('a.id') . ' IN (' . implode(', ', array_values($partIds)) . ')'
            ];
        }

        /** @var MoyskladPart[] */
        $parts = $this->hyper['helper']['moyskladPart']->findAll([
            'conditions' => $conditions,
            'order' => $db->qn('a.product_folder_id') . ' ASC'
        ]);

        $optionsIds = array_map(function (PlanItemData $planItem) {
            return $planItem->option_id;
        }, $planItemDataCollection->items());

        $assemblyKitId = $product->getAssemblyKitId();

        foreach ($planItemDataCollection as $itemKey => $planItemData) {
            if (!isset($parts[$planItemData->id])) {
                continue;
            }

            if ($planItemData->id === $assemblyKitId) { // unset assembly kit
                unset($parts[$assemblyKitId]);
                continue;
            }

            $part = $parts[$planItemData->id];
            $part->quantity = $planItemData->quantity;

            $currentPrice = $part->getListPrice();

            if (isset($optionsIds[$itemKey]) && $optionsIds[$itemKey]) {
                $option = $optionHelper->findById($optionsIds[$itemKey]);
                $part->option = $option;
                $currentPrice = $option->getListPrice();
            }

            if ($partDataCollection->offsetExists($planItemData->id)) {
                $price = $moneyHelper->get($partDataCollection->offsetGet($planItemData->id)->price);
                $part->setListPrice($price);
            } else {
                $part->setListPrice($currentPrice);
            }

            $part->set('original_price', $currentPrice);
        }

        $product->set('parts', $parts);
    }

    /**
     * Check if order has review
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function hasReview()
    {
        $review = $this->hyper['helper']['review']->findByOrderId($this->id);
        return Filter::bool($review->id);
    }

    /**
     * Checks if order has loan approved flag
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function isLoanApproved()
    {
        return $this->params->get('loan_approved', false, 'bool');
    }

    /**
     * Check for successful order
     *
     * @return  bool
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function isSold()
    {
        $sellStatuses = $this->hyper['params']->get('account_sell_status', []);
        return in_array((string) $this->getStatus()->id, $sellStatuses);
    }

    /**
     * Check for cancelled order
     *
     * @return  bool
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function isCancelled()
    {
        $cancelStatuses = $this->hyper['params']->get('account_cancel_status', []);
        return in_array((string) $this->getStatus()->id, $cancelStatuses);
    }

    /**
     * Get available requests count
     *
     * @return  int
     *
     * @throws  \Exception
     * @throws  SimpleTypesException
     *
     * @since   2.0
     */
    public function availableRequestCount()
    {
        $hasExpired = !$this->hyper['helper']['credit']->checkClearanceDayLimit($this);
        if ($hasExpired) {
            return 0;
        }

        $creditElements = Manager::getInstance()->getByPosition(Manager::ELEMENT_TYPE_CREDIT);
        $count          = 0;
        $orderTotal     = (int) $this->getTotal()->val();

        /** @var ElementCredit $creditElement */
        foreach ($creditElements as $creditElement) {
            if (!$creditElement->isAvailableInOrder($this)) {
                continue;
            }

            $maxPrice = $creditElement->getConfig('max_price', 0, 'int');

            if (!$maxPrice || $orderTotal < $maxPrice) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Get active requests count
     *
     * @return  int
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function sentRequestCount()
    {
        $creditElements = Manager::getInstance()->getByPosition(Manager::ELEMENT_TYPE_CREDIT);
        $alreadySent    = 0;

        /** @var ElementCredit $creditElement */
        foreach ($creditElements as $creditElement) {
            $elementKeyParam = $this->params->find($creditElement->getParamKey());
            if ((bool) $elementKeyParam) {
                ++$alreadySent;
            }
        }

        return $alreadySent;
    }

    /**
     * Set client YandexMetrica counter id
     *
     * @param   string $value
     *
     * @since   2.0
     */
    public function setYmCounter(string $value)
    {
        $this->params->set(self::PARAM_YM_COUNTER, $value);
    }

    /**
     * Set client YandexMetrica uid
     *
     * @param   string $value
     *
     * @since   2.0
     */
    public function setYmUid(string $value)
    {
        $this->params->set(self::PARAM_YM_UID, $value);
    }

    /**
     * Conver order entity to moysklad CustomerOrder
     *
     * @return  CustomerOrder
     *
     * @throws  ApiClientException
     * @throws  \Exception
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function toMoyskladEntity(): CustomerOrder
    {
        /** @var MoyskladCustomerOrderHelper */
        $customerOrderHelper = $this->hyper['helper']['moyskladCustomerOrder'];

        return $customerOrderHelper->orderToCustomerOrder($this);
    }
}
