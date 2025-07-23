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

namespace HYPERPC\Elements;

use HYPERPC\Joomla\Model\Entity\Order;

/**
 * Class ElementOrderHook
 *
 * @since   2.0
 */
abstract class ElementOrderHook extends ElementHook
{

    /**
     * Get order.
     *
     * @return  Order
     *
     * @since   2.0
     */
    protected function _getOrder()
    {
        return $this->hyper['helper']['order']->findById($this->_config->get('table')->id, ['new' => true]);
    }
}
