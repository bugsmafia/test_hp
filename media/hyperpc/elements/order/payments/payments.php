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
 * @author      Sergey Kalistratov <kalistratov.s.m@gmail.com>
 */

defined('_JEXEC') or die('Restricted access');

use HYPERPC\Elements\Manager;
use HYPERPC\Elements\Element;
use HYPERPC\Joomla\Model\Entity\Order;

/**
 * Class ElementOrderPayments
 *
 * @since   2.0
 */
class ElementOrderPayments extends Element
{

    /**
     * Get CRM value.
     *
     * @return  Element|mixed|null
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function getCrmValue()
    {
        if ($this->getMethod() ) {
            return $this->getMethod()->getConfig('name');
        }

        return null;
    }

    /**
     * Get current payment method.
     *
     * @return  Element|mixed|null
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function getMethod()
    {
        $methods = $this->getMethods();

        /** @var Element $method */
        foreach ($methods as $method) {
            if ($method->getType() === $this->_config->find('data.value')) {
                return $method;
            }
        }

        return null;
    }

    /**
     * Allowed payment methods.
     *
     * @return  array
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getMethods()
    {
        static $payments;

        $position = Manager::ELEMENT_TYPE_PAYMENT;
        if (!isset($payments[$position])) {
            $payments[$position] = $this->getManager()->getByPosition($position);
        }

        return $payments[$position];
    }

    /**
     * Get order object.
     *
     * @return  Order
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function getOrder()
    {
        if ($this->getConfig('data.order') instanceof Order) {
            return $this->getConfig('data.order');
        }

        return new Order();
    }
}
