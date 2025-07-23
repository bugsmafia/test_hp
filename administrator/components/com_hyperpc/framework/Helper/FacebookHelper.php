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

use FacebookAds\Api;
use HYPERPC\Data\JSON;
use Joomla\CMS\Uri\Uri;
use HYPERPC\ORM\Entity\User;
use HYPERPC\Money\Type\Money;
use FacebookAds\Logger\CurlLogger;
use HYPERPC\Joomla\Model\Entity\Order;
use FacebookAds\Object\ServerSide\Event;
use HYPERPC\Joomla\Model\Entity\Position;
use FacebookAds\Object\ServerSide\Content;
use FacebookAds\Object\ServerSide\UserData;
use FacebookAds\Object\ServerSide\CustomData;
use FacebookAds\Object\ServerSide\EventRequest;
use HYPERPC\Joomla\Model\Entity\MoyskladProduct;
use HYPERPC\Joomla\Model\Entity\Interfaces\OptionMarker;

/**
 * Class FacebookHelper
 *
 * @package HYPERPC\Helper
 *
 * @since   2.0
 */
class FacebookHelper extends AppHelper
{
    const ACCESS_TOKEN = 'EAAJ3SpppbvIBAATx9m7TjhEY0EG7tj6gZBedglfAVoAAP6nZCA9Ft7yTB3lekxXBZCrgYXNS6EAux869x4qQivkcZAWnial53DPMaAou0NGBei5vNrMmH2oQZCdDZAZAvCITTF68iMw7wowvgenSDfOufHWcCbsic8pvhhHKOEhqt0L7xr2h5Ar7lOJCjDZCn68ZD';
    const PIXEL_ID = '451450142254961';

    const TEST_MODE = false;
    const TEST_EVENTS_CODE = 'TEST54894';

    const AVAILABLE_HOSTS = ['hyperpc.ru'];

    /**
     * The Facebook click ID value stored in the _fbc browser cookie
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $_fbc;

    /**
     * The Facebook browser ID value stored in the _fbp browser cookie
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $_fbp;

    /**
     * Initialize helper.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function initialize()
    {
        parent::initialize();

        $this->_fbc = $this->hyper['input']->cookie->get('_fbc', '');
        $this->_fbp = $this->hyper['input']->cookie->get('_fbp', '');

        if (self::_isHostAvailable()) {
            $api = Api::init(null, null, self::ACCESS_TOKEN);
            $api->setLogger(new CurlLogger());
        }
    }

    /**
     * Handle add to cart event
     *
     * @param   array $data [
     *      'type' => 'enum part|notebook|product|configuration',
     *      'id' => 'int',
     *      'option' => 'int',
     *      'stock_id' => 'int',
     *      'savedConfiguration' => 'int',
     *      'quantity' => 'int'
     *  ]
     *
     * @throws \Exception
     * @throws \RuntimeException
     * @throws \JBZoo\Utils\Exception
     * @throws \JBZoo\SimpleTypes\Exception
     *
     * @return void
     *
     * @since   2.0
     *
     * @todo    object type for the data
     */
    public function addToCartEvent(array $data)
    {
        if (!self::_isHostAvailable()) {
            return;
        }

        $data = new JSON($data);

        $customData = (new CustomData())
            ->setContentType('product');

        /** @var MoneyHelper */
        $moneyHelper = $this->hyper['helper']['money'];
        $price = $moneyHelper->get(0);
        $currencyCode = $moneyHelper->getCurrencyIsoCode($price);
        $customData->setCurrency($currencyCode);

        /** @var Position $position */
        $position = $this->hyper['helper']['position']->getById($data->get('id'));
        if (!$position->id) {
            return;
        }

        $customData
            ->setContentName($position->name)
            ->setContentIds(['position-' . $position->id]);

        if ($position->isProduct()) {
            $savedConfiguration = $data->get('savedConfiguration');
            if ($savedConfiguration) {
                $positions = $this->hyper['helper']['moyskladStock']->getProductsByConfigurationId($savedConfiguration);
                if (count($positions)) {
                    /** @var MoyskladProduct $position */
                    $position = array_shift($positions);
                }

                $price = $position->getConfigPrice(true);
            } else {
                $price = $position->getListPrice();
            }
        } else {
            $price = $position->getListPrice();

            $optionId = $data->get('option');
            if ($optionId) {
                /** @var OptionMarker */
                $variant = $this->hyper['helper']['moyskladVariant']->getById($optionId);
                if ($variant->id) {
                    $price = $variant->getListPrice();
                }
            }
        }

        $customData->setValue($price->val());

        $userData = $this->_getCommonUserDataObject();

        $event = (new Event())
            ->setEventName('AddToCart')
            ->setEventTime(time())
            ->setUserData($userData)
            ->setCustomData($customData);

        $this->_sendEvents([$event]);
    }

    /**
     * Send data on initiate checkout event
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function initiateCheckoutEvent()
    {
        if (!self::_isHostAvailable()) {
            return;
        }

        $userData = $this->_getCommonUserDataObject();

        /** @var CartHelper */
        $cartHelper = $this->hyper['helper']['cart'];

        $event = (new Event())
            ->setEventName('InitiateCheckout')
            ->setEventTime(time())
            ->setEventSourceUrl(Uri::root() . ltrim($cartHelper->getUrl(), '/'))
            ->setUserData($userData);

        $this->_sendEvents([$event]);
    }

    /**
     * Send data on purchase event
     *
     * @param   Order $order
     *
     * @throws  \Exception
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function purchaseEvent(Order $order)
    {
        if (!self::_isHostAvailable()) {
            return;
        }

        $userData = $this->_getOrderUserData($order);
        $contents = $this->_getContents($order);

        $orderTotal = $order->getTotal();

        /** @var MoneyHelper */
        $moneyHelper = $this->hyper['helper']['money'];
        $currencyCode = $moneyHelper->getCurrencyIsoCode($orderTotal);

        /** @todo method to get order id for ecommerce events */
        $orderId = $order->isCredit() ? 'K' . $order->id : (string) $order->id;

        $customData = (new CustomData())
            ->setOrderId($orderId)
            ->setContents($contents)
            ->setCurrency($currencyCode)
            ->setValue($orderTotal->val())
            ->setContentType('product')
            ->setNumItems(count($contents))
            ->setContentIds(array_map(function ($content) {
                /** @var Content $content */
                return $content->getProductId();
            }, $contents));

        /** @var CartHelper */
        $cartHelper = $this->hyper['helper']['cart'];

        $event = (new Event())
            ->setEventId($orderId)
            ->setEventName('Purchase')
            ->setEventTime(time())
            ->setEventSourceUrl(Uri::root() . ltrim($cartHelper->getUrl(), '/'))
            ->setUserData($userData)
            ->setCustomData($customData);

        $this->_sendEvents([$event]);
    }

    /**
     * Get UserData object with base information
     *
     * @return  UserData
     *
     * @since   2.0
     */
    protected function _getCommonUserDataObject()
    {
        $userData = (new UserData())
            ->setClientIpAddress($_SERVER['REMOTE_ADDR'])
            ->setClientUserAgent($_SERVER['HTTP_USER_AGENT'])
            ->setFbc($this->_fbc)
            ->setFbp($this->_fbp);

        /** @var User */
        $user = $this->hyper['user'];
        if ($user->id) {
            $isAutoEmail = $this->hyper['helper']['string']->isAutoEmail($user->email);
            if (!$isAutoEmail) {
                $userData->setEmail($user->email);
            }

            $phone = str_replace(['+', ' ', '-', '(', ')'], '', $user->getPhone());
            if (!empty($phone)) {
                $userData->setPhone($phone);
            }
        }

        return $userData;
    }

    /**
     * Get order items info
     *
     * @param   Order $order
     *
     * @return  Content[]
     *
     * @since   2.0
     */
    protected function _getContents(Order $order)
    {
        $contents = [];

        $orderItems = $order->getPositions();
        foreach ($orderItems as $item) {
            $itemKey = $item->getItemKey();
            if ($item->isProduct()) {
                $itemKey = 'position-' . $item->id;
            }

            $line = (new Content())
                ->setProductId($itemKey)
                ->setQuantity((int) $item->quantity)
                ->setItemPrice($this->_getItemPrice($item));

            $contents[] = $line;
        }

        return $contents;
    }

    /**
     * Get normalized price value
     *
     * @param   Money $price
     *
     * @return  float
     *
     * @since   2.0
     *
     * @throws \JBZoo\SimpleTypes\Exception
     */
    protected function _getNormalizedPrice(Money $price)
    {
        return round($price->val(), 2);
    }

    /**
     * Get item price
     *
     * @param   Position $item
     *
     * @return  float
     *
     * @throws  \JBZoo\SimpleTypes\Exception
     */
    protected function _getItemPrice($item)
    {
        /** @todo products probably need configuration price */
        return $this->_getNormalizedPrice($item->getPrice());
    }

    /**
     * Get UserData object from order
     *
     * @param   Order $order
     *
     * @return  UserData
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    protected function _getOrderUserData(Order $order)
    {
        $userData = $this->_getCommonUserDataObject();
        $userData
            ->setEmail($order->getBuyerEmail())
            ->setPhone(ltrim($order->getBuyerPhone(true), '+'));

        return $userData;
    }

    /**
     * Execute an api request
     *
     * @param   Event[] $events
     *
     * @since   2.0
     */
    protected function _sendEvents(array $events)
    {
        $request = (new EventRequest(self::PIXEL_ID))
            ->setEvents($events);

        if (self::TEST_MODE === true) {
            $request->setTestEventCode(self::TEST_EVENTS_CODE);
        }

        $request->execute();
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
     * Is current host available to send facebook requests
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
