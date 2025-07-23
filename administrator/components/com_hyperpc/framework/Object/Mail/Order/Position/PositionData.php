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
use HYPERPC\Object\Mail\Order\Position\QuantityData;

class PositionData extends DataTransferObject
{
    /**
     * Position title
     */
    public string $title;

    /**
     * Category or type
     */
    public string $category;

    /**
     * Image link
     */
    public string $image;

    /**
     * Position price
     */
    public string $price;

    /**
     * Position quantity
     *
     * @var QuantityData[]
     */
    public array $quantity = [];
}
