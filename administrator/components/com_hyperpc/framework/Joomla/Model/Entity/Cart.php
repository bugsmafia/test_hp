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
 */

namespace HYPERPC\Joomla\Model\Entity;

use JBZoo\Data\Data;
use HYPERPC\Money\Type\Money;
use HYPERPC\Helper\CartHelper;
use HYPERPC\Helper\SessionHelper;

/**
 * Class Cart
 *
 * @package     HYPERPC\Joomla\Model\Entity
 *
 * @since       2.0
 */
class Cart extends Entity
{

    /**
     * Hold cart helper object.
     *
     * @var     CartHelper
     *
     * @since   2.0
     */
    protected $_helper;

    /**
     * Get count of cart items.
     *
     * @return  int
     *
     * @since   2.0
     */
    public function getCount()
    {
        return count($this->_helper->getSessionItems());
    }

    /**
     * Get cart total price.
     *
     * @return  Money
     *
     * @since   2.0
     */
    public function getTotalPrice()
    {
        return $this->_helper->getCartTotal();
    }

    /**
     * Get site view category link.
     *
     * @param   array $query
     * @return  null|string
     *
     * @since   2.0
     */
    public function getViewUrl(array $query = [])
    {
        return null;
    }

    /**
     * Check minimal credit sum.
     *
     * @return  bool
     *
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function enableCredit()
    {
        $totalValue     = $this->getTotalPrice();
        $minCreditPrice = $this->getCreditMinSum();

        return $totalValue->compare($minCreditPrice, '>=');
    }

    /**
     * Get credit max sum.
     *
     * @return  Money
     *
     * @since   2.0
     */
    public function getCreditMaxSum()
    {
        $maxCreditPrice = $this->hyper['helper']['credit']->getMaxPrice();
        return $this->hyper['helper']['money']->get($maxCreditPrice);
    }

    /**
     * Get credit min sum.
     *
     * @return  Money
     *
     * @since   2.0
     */
    public function getCreditMinSum()
    {
        $minCreditPrice = $this->hyper['params']->get('credit_min_price', 10000);
        return $this->hyper['helper']['money']->get($minCreditPrice);
    }

    /**
     * Get order min sum.
     *
     * @return  Money
     *
     * @since   2.0
     */
    public function getOrderMinSum()
    {
        $minCreditPrice = $this->hyper['params']->get('order_min_price', 3000);
        return $this->hyper['helper']['money']->get($minCreditPrice);
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
        return $this->_helper->getSession();
    }

    /**
     * Initialize entity.
     *
     * @return  void
     *
     * @throws  \RuntimeException
     * @throws  \JBZoo\Utils\Exception
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function initialize()
    {
        $this->_helper = $this->hyper['helper']['cart'];
    }
}
