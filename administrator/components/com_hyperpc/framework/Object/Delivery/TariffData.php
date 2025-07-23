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

namespace HYPERPC\Object\Delivery;

use Spatie\DataTransferObject\DataTransferObject;

class TariffData extends DataTransferObject
{
    /**
     * Name of delivery company
     */
    public string $companyName;

    /**
     * Shipping cost
     */
    public float $cost;

    /**
     * Max shipping days
     */
    public int $maxDays;

    /**
     * Min shipping days
     */
    public int $minDays;

    /**
     * Pickup point ids
     */
    public ?array $pickupPointIds;

    /**
     * Default pickup point id
     */
    public ?PointData $defaultPickupPoint;
}
