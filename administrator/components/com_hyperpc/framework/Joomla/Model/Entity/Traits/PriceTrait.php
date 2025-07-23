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

defined('_JEXEC') or die('Restricted access');

use HYPERPC\Money\Type\Money;

/**
 * Trait Entity price
 *
 * @package     HYPERPC\Joomla\Model\Entity\Traits
 *
 * @since       2.0
 */
trait PriceTrait
{
    /**
     * Get list price
     *
     * @return mixed
     *
     * @since 2.0
     */
    public function getListPrice()
    {
        return $this->list_price;
    }

    /**
     * Get sale price
     *
     * @return mixed
     *
     * @since 2.0
     */
    public function getSalePrice()
    {
        return $this->sale_price;
    }

    /**
     * Set list price
     *
     * @param   Money $price
     *
     * @since   2.0
     */
    public function setListPrice(Money $price)
    {
        $this->list_price = $price->getClone();
    }

    /**
     * Set list price
     *
     * @param   Money $price
     *
     * @since   2.0
     */
    public function setSalePrice(Money $price)
    {
        $this->sale_price = $price->getClone();
    }

    /**
     * Get part price by quantity for order.
     *
     * @param   bool $checkRate
     *
     * @return  \JBZoo\SimpleTypes\Type\Money
     *
     * @since   2.0
     */
    public function getQuantityPrice($checkRate = true)
    {
        $price = $this->getPrice($checkRate);
        if (isset($this->quantity) && $this->quantity) {
            $price->multiply($this->quantity);
        }

        return $price;
    }

    /**
     * Get part price.
     *
     * @param   bool $checkRate
     *
     * @return  \JBZoo\SimpleTypes\Type\Money
     *
     * @since   2.0
     */
    public function getPrice($checkRate = true)
    {
        if (!$checkRate) {
            return clone $this->getListPrice();
        }

        return clone $this->getSalePrice();
    }
}
