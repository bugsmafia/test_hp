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

namespace HYPERPC\Object\Mail\Order;

use HYPERPC\Object\Mail\Order\DiscountData;
use Spatie\DataTransferObject\DataTransferObject;
use HYPERPC\Object\Mail\Order\Delivery\PickupData;
use HYPERPC\Object\Mail\Order\Delivery\ShippingData;
use HYPERPC\Object\Mail\Order\Position\PositionData;

class OrderData extends DataTransferObject
{
    /**
     * Order number
     */
    public string $orderNumber;

    /**
     * Order link
     */
    public string $orderLink;

    /**
     * Order date
     */
    public string $orderDate;

    /**
     * Pickup data
     *
     * @var PickupData[]
     */
    public array $pickup = [];

    /**
     * Shipping data
     *
     * @var ShippingData[]
     */
    public array $shipping = [];

    /**
     * Client name
     */
    public string $clientName;

    /**
     * Client type
     */
    public string $clientType;

    /**
     * Client phone
     */
    public string $clientPhone;

    /**
     * Client email
     */
    public string $clientEmail;

    /**
     * Payment method
     */
    public string $payment;

    /**
     * Order positions
     *
     * @var PositionData[]
     */
    public array $positions = [];

    /**
     * Positions count
     */
    public string $positionsCount;

    /**
     * Price of order products
     */
    public string $productsPrice;

    /**
     * Price of order services
     */
    public string $servicesPrice;

    /**
     * Payment method
     *
     * @var DiscountData[]
     */
    public array $discount = [];

    /**
     * OrderTotal
     */
    public string $orderTotal;
}
