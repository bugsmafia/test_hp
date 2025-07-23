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

namespace HYPERPC\Delivery;

use Exception;

/**
 * Class DeliveryFactory
 *
 * @package HYPERPC\Delivery
 *
 * @since   2.0
 */
class DeliveryFactory
{

    /**
     * Create delivery instance
     *
     * @param   string $type
     *
     * @return  Delivery
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public static function createDelivery($type = 'Yandex'): Delivery
    {
        $className = 'HYPERPC\\Delivery\\' . $type . 'Delivery';

        if (!class_exists($className)) {
            throw new Exception('Unknown delivery type');
        }

        return new $className;
    }
}