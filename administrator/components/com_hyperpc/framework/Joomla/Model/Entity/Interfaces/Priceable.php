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

use HYPERPC\Money\Type\Money;

/**
 * Interface Priceable
 *
 * @package HYPERPC\Joomla\Model\Entity\Interfaces
 *
 * @since   2.0
 */
interface Priceable
{

    /**
     * Get list price
     *
     * @return mixed
     *
     * @since 2.0
     */
    public function getListPrice();

    /**
     * Get sale price
     *
     * @return mixed
     *
     * @since 2.0
     */
    public function getSalePrice();

    /**
     * Set list price
     *
     * @param   Money $price
     *
     * @since   2.0
     */
    public function setListPrice(Money $price);

    /**
     * Set sale price
     *
     * @param   Money $price
     *
     * @since   2.0
     */
    public function setSalePrice(Money $price);

    /**
     * Get entity price by quantity.
     *
     * @param   bool $checkRate
     *
     * @return  Money
     *
     * @since   2.0
     */
    public function getQuantityPrice($checkRate = true);
}
