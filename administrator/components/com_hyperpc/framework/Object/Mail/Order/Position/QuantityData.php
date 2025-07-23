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

namespace HYPERPC\Object\Mail\Order\Position;

use Spatie\DataTransferObject\DataTransferObject;

class QuantityData extends DataTransferObject
{
    /**
     * Quantity value
     */
    public string $value;

    /**
     * Price per item
     */
    public string $unitPrice;
}
