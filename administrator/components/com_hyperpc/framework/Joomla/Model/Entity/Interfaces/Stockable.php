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
 * @author      Artem vyshnevskiy
 */

namespace HYPERPC\Joomla\Model\Entity\Interfaces;

use HYPERPC\Object\Delivery\MeasurementsData;

/**
 * Interface Stockable
 *
 * @package HYPERPC\Joomla\Model\Entity\Interfaces
 *
 * @since   2.0
 */
interface Stockable
{
    const AVAILABILITY_INSTOCK      = 'InStock';
    const AVAILABILITY_OUTOFSTOCK   = 'OutOfStock';
    const AVAILABILITY_PREORDER     = 'PreOrder';
    const AVAILABILITY_DISCONTINUED = 'Discontinued';

    /**
     * Get availability.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getAvailability();

    /**
     * Get available by store.
     *
     * @param   null|int  $storeId
     *
     * @return  JSON|mixed
     *
     * @since   2.0
     */
    public function getAvailabilityByStore($storeId = null);

    /**
     * Get item dimensions and weight
     *
     * @return MeasurementsData
     *
     * @since   2.0
     */
    public function getDimensions(): MeasurementsData;

    /**
     * Get picking dates
     *
     * @return  JSON
     *
     * @since   2.0
     */
    public function getPickingDates();

    /**
     * Get sending dates
     *
     * @return  JSON
     *
     * @since   2.0
     */
    public function getSendingDates();

    /**
     * Check has balance.
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function hasBalance();

    /**
     * Check is discontinued.
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function isDiscontinued();

    /**
     * Check is in stock.
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function isInStock();

    /**
     * Check is out of stock.
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function isOutOfStock();

    /**
     * Check is pre order.
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function isPreOrdered();
}
