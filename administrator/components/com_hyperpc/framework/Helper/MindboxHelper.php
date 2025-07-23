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
 * @author      Artem Vyshnevskiy
 */

namespace HYPERPC\Helper;

use Mindbox\Mindbox;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Date\Date;
use HYPERPC\Money\Type\Money;
use Joomla\CMS\Filesystem\Path;
use Mindbox\Loggers\MindboxFileLogger;
use HYPERPC\Joomla\Model\Entity\Order;
use Mindbox\Responses\MindboxOrderResponse;
use Mindbox\DTO\V3\OperationDTO as Operation;
use Mindbox\Exceptions\MindboxClientException;
use Mindbox\Exceptions\MindboxConfigException;
use Mindbox\DTO\V3\Requests\LineRequestDTO as Line;
use Mindbox\DTO\V3\Requests\DiscountRequestDTO as Discount;
use Mindbox\DTO\V3\Requests\CustomerRequestDTO as Customer;
use Mindbox\DTO\V3\Requests\OrderRequestDTO as MindboxOrder;
use Mindbox\DTO\V3\Requests\ProductRequestDTO as MindboxProduct;
use Mindbox\DTO\V3\Requests\SubscriptionRequestDTO as Subscription;

/**
 * Class MindboxHelper
 *
 * @package HYPERPC\Helper
 *
 * @since   2.0
 */
class MindboxHelper extends AppHelper
{
    const MINDBOX_ENDPOINT_ID = 'HyperPC.ru';
    const MINDBOX_SECRET_KEY  = 'GCPiIjxI27lDHUYj7CvA';

    const MINDBOX_DOMAIN      = 'hyperpc.mindbox';
    const MINDBOX_DOMAIN_ZONE = 'ru';

    const AVAILABLE_HOSTS    = []; // currently disabled
    const AVAILABLE_CONTEXTS = [HP_CONTEXT_HYPERPC];

    /**
     * Mindbox logger
     *
     * @var     MindboxFileLogger
     *
     * @since   2.0
     */
    protected $_logger;

    /**
     * Application entry point.
     *
     * @var     Mindbox
     *
     * @since   2.0
     */
    protected $_mindbox;

    /**
     * Initialize helper.
     *
     * @return  void
     *
     * @since   2.0
     *
     * @throws  MindboxConfigException
     */
    public function initialize()
    {
        parent::initialize();

        if (self::_isHostAvailable()) {
            $this->_logger = new MindboxFileLogger(
                Path::clean(JPATH_ROOT . '/administrator/logs'),
                MindboxFileLogger::ERROR
            );
            $this->_mindbox = new Mindbox([
                'endpointId' => self::MINDBOX_ENDPOINT_ID,
                'secretKey'  => self::MINDBOX_SECRET_KEY,
                'domain'     => self::MINDBOX_DOMAIN,
                'domainZone' => self::MINDBOX_DOMAIN_ZONE,
                'httpClient' => 'curl'
            ], $this->_logger);
        }
    }

    /**
     * Customer autorized
     *
     * @param   int $id
     *
     * @since   2.0
     */
    public function authorizeCustomer($id)
    {
        if (!$this->_mindbox) {
            return;
        }

        $customer = $this->_getCustomer($id);

        try {
            $this->_mindbox->customer()
                ->authorize(
                    $customer,
                    'Website.AuthorizeCustomer'
                )->sendRequest();
        } catch (MindboxClientException $e) {
            return;
        }
    }

    /**
     * Create authorized order
     *
     * @param   Order $order
     *
     * @since   2.0
     *
     * @throws JBZoo\Utils\Exception
     * @throws JBZoo\SimpleTypes\Exception
     */
    public function createAuthorizedOrder(Order $order)
    {
        if (!$this->_mindbox) {
            return;
        }

        $this->_createOrder($order, true);
    }

    /**
     * Create unauthorized order
     *
     * @param   Order $order
     *
     * @since   2.0
     *
     * @throws JBZoo\Utils\Exception
     * @throws JBZoo\SimpleTypes\Exception
     */
    public function createUnauthorizedOrder(Order $order)
    {
        if (!$this->_mindbox) {
            return;
        }

        $this->_createOrder($order, false);
    }

    /**
     * Customer edit
     *
     * @param   int $id
     * @param   string|null $name
     * @param   string|null $email
     * @param   string|null $phone
     * @param   array       $changedFields
     *
     * @since   2.0
     */
    public function editCustomer($id, $name = null, $email = null, $phone = null, $changedFields = [])
    {
        if (!$this->_mindbox) {
            return;
        }

        $customer = $this->_getCustomer($id, $name, $email, $phone);

        $subscriptions = [];
        foreach ($changedFields as $fieldName) {
            switch ($fieldName) {
                case 'email':
                    $subscription = new Subscription();
                    $subscription->setPointOfContact('Email');
                    $subscriptions[] = $subscription;
                    break;
                case 'phone':
                    $subscription = new Subscription();
                    $subscription->setPointOfContact('SMS');
                    $subscriptions[] = $subscription;
                    break;
            }
        }

        if (!empty($subscriptions)) {
            $customer->setSubscriptions($subscriptions);
        }

        try {
            $this->_mindbox->customer()
                ->edit(
                    $customer,
                    'Website.EditCustomer'
                )->sendRequest();
        } catch (MindboxClientException $e) {
            return;
        }
    }

    /**
     * Customer registered
     *
     * @param   int $id
     * @param   string|null $name
     * @param   string|null $email
     * @param   string|null $phone
     *
     * @since   2.0
     */
    public function fillCustomerProfile($id, $name = null, $email = null, $phone = null)
    {
        if (!$this->_mindbox) {
            return;
        }

        $customer = $this->_getCustomer($id, $name, $email, $phone);

        try {
            $this->_mindbox->customer()
                ->fill(
                    $customer,
                    'Website.FillCustomerProfile'
                )->sendRequest();
        } catch (MindboxClientException $e) {
            return;
        }
    }

    /**
     * Customer registered
     *
     * @param   int $id
     * @param   string|null $name
     * @param   string|null $email
     * @param   string|null $phone
     *
     * @since   2.0
     */
    public function registerCustomer($id, $name = null, $email = null, $phone = null)
    {
        if (!$this->_mindbox) {
            return;
        }

        $customer = $this->_getCustomer($id, $name, $email, $phone);

        try {
            $this->_mindbox->customer()
                ->register(
                    $customer,
                    'Website.RegisterCustomer'
                )->sendRequest();
        } catch (MindboxClientException $e) {
            return;
        }
    }

    /**
     * Update order
     *
     * @param   Order $order
     *
     * @since   2.0
     *
     * @throws JBZoo\Utils\Exception
     * @throws JBZoo\SimpleTypes\Exception
     */
    public function updateOrder(Order $order)
    {
        if (!$this->_mindbox || !in_array($order->context, self::AVAILABLE_CONTEXTS)) {
            return;
        }

        $operation    = new Operation();
        $customer     = $this->_getCustomer($order->created_user_id);
        $mindboxOrder = $this->_getMindboxOrderUpdate($order);

        $lines = $mindboxOrder->getLines();
        foreach ($lines as $line) {
            $line->setStatus($order->status);
        }

        $operation->setCustomer($customer);
        $operation->setField('order', $mindboxOrder);
        $operation->setField(
            'executionDateTimeUtc',
            $this->_getUtcDate($order->modified_time)
        );

        try {
            $this->_mindbox->getClientV3()
                ->prepareRequest(
                    'POST',
                    'Website.UpdateOrder',
                    $operation,
                    'update-order',
                    [],
                    false,
                    false
                )->sendRequest();
        } catch (MindboxClientException $e) {
            return;
        }
    }

    /**
     * Update order
     *
     * @param   Order $order
     *
     * @since   2.0
     */
    public function updateOrderStatus(Order $order)
    {
        if (!$this->_mindbox || !in_array($order->context, self::AVAILABLE_CONTEXTS)) {
            return;
        }

        $operation    = new Operation();
        $mindboxOrder = new MindboxOrder();
        $mindboxOrder->setId('websiteID', $order->id);
        $operation->setField('orderLinesStatus', $order->status);
        $operation->setField('order', $mindboxOrder);
        $operation->setField(
            'executionDateTimeUtc',
            $this->_getUtcDate($order->modified_time)
        );

        try {
            $this->_mindbox->getClientV3()
                ->prepareRequest(
                    'POST',
                    'Website.UpdateOrderStatus',
                    $operation,
                    'update-order',
                    [],
                    false,
                    false
                )->sendRequest();
        } catch (MindboxClientException $e) {
            return;
        }
    }

    /**
     * Create order request
     *
     * @param   Order $order
     * @param   bool $isAutorized
     *
     * @since   2.0
     *
     * @throws JBZoo\Utils\Exception
     * @throws JBZoo\SimpleTypes\Exception
     */
    protected function _createOrder(Order $order, $isAutorized = false)
    {
        $mindboxOrder = $this->_getMindboxOrderCreate($order);

        $customer = $isAutorized ?
            $this->_getCustomer($order->created_user_id) :
            $this->_getCustomer(
                $order->created_user_id,
                $order->getBuyer(),
                $order->getBuyerEmail(),
                $order->getBuyerPhone()
            );

        if (!$isAutorized) { // Subscribe new customers to email
            $subscription = new Subscription();
            $subscription->setPointOfContact('Email');

            $customer->setSubscriptions([$subscription]);
        }

        $operation = new Operation();
        $operation->setCustomer($customer);
        $operation->setField('order', $mindboxOrder);
        $operation->setField(
            'executionDateTimeUtc',
            $this->_getUtcDate($order->created_time)
        );

        // $status = $order->status;
        // if (!empty($status)) {
        //     $operation->setField('orderLinesStatus', $status);
        // }

        $client = $this->_mindbox->getClientV3();
        $client->setResponseType(MindboxOrderResponse::class);
        try {
            $client
                ->prepareRequest(
                    'POST',
                    'Website.Create' . ($isAutorized ? 'Authorized' : 'Unauthorized') . 'Order',
                    $operation,
                    'create',
                    [],
                    false,
                    true
                )->sendRequest();
        } catch (MindboxClientException $e) {
            return;
        }
    }

    /**
     * Get Mindbox customer object
     *
     * @param   int $id
     * @param   string|null $name
     * @param   string|null $email
     * @param   string|null $phone
     *
     * @return  Customer
     */
    protected function _getCustomer($id, $name = null, $email = null, $phone = null)
    {
        $customer = new Customer();
        $customer->setId('websiteID', $id);

        if ($name !== null) {
            $customer->setFullName($name);
        }

        if ($phone !== null) {
            $customer->setMobilePhone($phone);
        }

        if ($email !== null) {
            $customer->setEmail($email);
        }

        return $customer;
    }

    /**
     * Get current host name
     *
     * @return  string
     *
     * @since   2.0
     */
    protected static function _getHostName()
    {
        $uri = Uri::getInstance();
        return $uri->getHost();
    }

    /**
     * Get price in ####,## format
     *
     * @param   Money $price
     *
     * @return  string
     *
     * @since   2.0
     *
     * @throws \JBZoo\SimpleTypes\Exception
     */
    protected function _getFormattedPrice(Money $price)
    {
        return $price->getClone()->changeRule('rub', [
            'num_decimals' => 2,
            'thousands_sep' => '',
            'symbol' => ''
        ])->text();
    }

    /**
     * Return Mindbox order object with set order data
     *
     * @param   MindboxOrder $mindboxOrder
     * @param   Order $order
     *
     * @return  MindboxOrder
     *
     * @since   2.0
     *
     * @throws JBZoo\Utils\Exception
     * @throws JBZoo\SimpleTypes\Exception
     */
    protected function _getMindboxOrder(MindboxOrder $mindboxOrder, Order $order)
    {
        $mindboxOrder->setId('websiteID', $order->id);
        $mindboxOrder->setField('email', $order->getBuyerEmail());
        $mindboxOrder->setField('mobilePhone', $order->getBuyerPhone());

        $mindboxOrder->setField('totalPrice', $this->_getFormattedPrice($order->getTotal()));

        /** @var \ElementOrderYandexDelivery */
        $delivery = $order->getDelivery();

        if ($delivery->isShipping()) {
            $deliveryCost = $delivery->getPrice();
            if ($deliveryCost->val() >= 0) {
                $mindboxOrder->setDeliveryCost($this->_getFormattedPrice($deliveryCost));
            }
        }

        $deliveryType = $delivery->getService();
        $mindboxOrder->setCustomField('deliveryType', $deliveryType);

        $lines = [];
        $totalDiscountAmount = $this->hyper['helper']['money']->get(0);

        $orderItems = $order->getItems();

        foreach ($orderItems as $type => $items) {
            foreach ($items as $item) {
                $line = new Line();
                $line->setQuantity($item->quantity);

                $mindboxProduct = new MindboxProduct();
                $productKey = $type === 'products' ? 'product-' . $item->id : $item->getItemKey(); // Ignore configuration and stock ids
                $mindboxProduct->setId('website', $productKey);

                $line->setProduct($mindboxProduct);

                /** @var Money */
                $basePrice = clone $item->getListPrice();
                $line->setField('basePricePerItem', $this->_getFormattedPrice($basePrice));

                $rate = $item->get('rate', 0);
                if ($rate > 0) {
                    /** @var Money */
                    $discountAmount = clone $basePrice;
                    $discountAmount->multiply(
                        min($rate, 100) / 100
                    )->multiply($item->quantity);
                    $totalDiscountAmount->add($discountAmount->val());

                    // $discount = new Discount();
                    // $discount->setAmount($discountAmountVal);
                    // if (!empty($order->promo_code)) {
                    //     $discount->setType('promoCode');
                    // } else {
                    //     $discount->setType('internalPromoAction');
                    // }

                    // $discounts = [$discount];
                    // $line->setDiscounts($discounts);

                    $line->setField(
                        'discountedPricePerLine',
                        $this->_getFormattedPrice(
                            $basePrice
                                ->multiply($item->quantity)
                                ->subtract($discountAmount->val())
                        )
                    );
                }

                $lines[] = $line;
            }
        }

        $mindboxOrder->setLines($lines);
        // if ($totalDiscountAmount->val() > 0) {
        //     $discount = new Discount();
        //     $discount->setAmount($totalDiscountAmount->val());
        //     if (!empty($order->promo_code)) {
        //         $discount->setType('promoCode');
        //     } else {
        //         $discount->setType('internalPromoAction');
        //     }
        //     $discounts = [$discount];
        //     $mindboxOrder->setDiscounts($discounts);
        // }

        return $mindboxOrder;
    }

    /**
     * Return Mindbox order create object with set order data
     *
     * @param   Order $order
     *
     * @return  MindboxOrder
     *
     * @since   2.0
     *
     * @throws JBZoo\Utils\Exception
     * @throws JBZoo\SimpleTypes\Exception
     */
    protected function _getMindboxOrderCreate(Order $order)
    {
        $mindboxOrder = new MindboxOrder();

        return $this->_getMindboxOrder($mindboxOrder, $order);
    }

    /**
     * Return Mindbox order update object with set order data
     *
     * @param   Order $order
     *
     * @return  MindboxOrder
     *
     * @since   2.0
     *
     * @throws JBZoo\Utils\Exception
     * @throws JBZoo\SimpleTypes\Exception
     */
    protected function _getMindboxOrderUpdate(Order $order)
    {
        $mindboxOrder = new MindboxOrder();

        return $this->_getMindboxOrder($mindboxOrder, $order);
    }

    /**
     * Get Utc date
     *
     * @param   Date $date
     *
     * @return  string
     *
     * @since   2.0
     */
    protected function _getUtcDate(Date $date)
    {
        return $date->format('Y-m-d H:i');
    }

    /**
     * Is current host available to send mindbox requests
     *
     * @return  boolean
     *
     * @since   2.0
     */
    protected static function _isHostAvailable()
    {
        return in_array(self::_getHostName(), self::AVAILABLE_HOSTS);
    }
}
