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
 * @author      Roman Evsyukov
 */

namespace HYPERPC\Joomla\Model\Entity\Traits;

use HYPERPC\Data\JSON;
use HYPERPC\Helper\CartHelper;
use HYPERPC\Helper\MoyskladStockHelper;

defined('_JEXEC') or die('Restricted access');

/**
 * Trait Entity availability
 *
 * @package     HYPERPC\Joomla\Model\Entity\Traits
 *
 * @since       2.0
 */
trait AvailabilityTrait
{
    /**
     * Get picking dates
     *
     * @return  JSON
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getPickingDates()
    {
        $orderPickingDates = $this->_getOrderPickingDates();

        return new JSON($orderPickingDates->get('stores', []));
    }

    /**
     * Get sending dates
     *
     * @return  JSON
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getSendingDates()
    {
        $orderPickingDates = $this->_getOrderPickingDates();

        return new JSON($orderPickingDates->get('shippingReady', []));
    }

    /**
     * Check has balance.
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function hasBalance()
    {
        static $balances = [];

        if (!array_key_exists($this->id, $balances)) {
            /** @var MoyskladStockHelper */
            $stockHelper = $this->hyper['helper']['moyskladStock'];
            $stocks = $stockHelper->getItems([
                'itemIds' => [$this->id]
            ]);

            $balance = 0;
            foreach ($stocks as $stock) {
                $balance += $stock->balance;
            }

            $balances[$this->id] = $balance > 0;
        }

        return $balances[$this->id];
    }

    /**
     * Check is discontinued.
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function isDiscontinued()
    {
        return ($this->getAvailability() === self::AVAILABILITY_DISCONTINUED);
    }

    /**
     * Check is in stock.
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function isInStock()
    {
        return ($this->getAvailability() === self::AVAILABILITY_INSTOCK);
    }

    /**
     * Check is out of stock.
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function isOutOfStock()
    {
        return ($this->getAvailability() === self::AVAILABILITY_OUTOFSTOCK);
    }

    /**
     * Check is pre order.
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function isPreOrdered()
    {
        return ($this->getAvailability() === self::AVAILABILITY_PREORDER);
    }

    /**
     * Get order ready dates.
     *
     * @return  JSON
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    protected function _getOrderPickingDates()
    {
        /** @var CartHelper */
        $cartHelper = $this->hyper['helper']['cart'];

        $itemKey = $this->getItemKey();
        $itemData = [
            $itemKey => $this
        ];

        $quantityData = [
            $itemKey => [
                'quantity' => 1
            ]
        ];

        return $cartHelper->getOrderPickingDates($itemData, $quantityData);
    }
}
