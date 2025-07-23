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

namespace HYPERPC\Object\SavedConfiguration;

use Spatie\DataTransferObject\DataTransferObject;

class PartData extends DataTransferObject
{
    /**
     * Position id
     */
    public int $id;

    /**
     * Variant id
     */
    public ?int $option_id;

    /**
     * Position product folder id
     */
    public int $group_id;

    /**
     * Position price
     */
    public int $price;

    /**
     * Position quantity
     */
    public int $quantity = 1;
}
