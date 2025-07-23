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

namespace HYPERPC\Object\Mail\Order\Delivery;

use Spatie\DataTransferObject\DataTransferObject;

class PickupData extends DataTransferObject
{
    /**
     * Store address
     */
    public string $storeAddress;

    /**
     * Ready date for pickup an order
     */
    public string $readyDate;
}
