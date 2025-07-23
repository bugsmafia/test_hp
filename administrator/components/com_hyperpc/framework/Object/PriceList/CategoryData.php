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

namespace HYPERPC\Object\PriceList;

use Spatie\DataTransferObject\DataTransferObject;

class CategoryData extends DataTransferObject
{
    /**
     * Category id
     */
    public int $id;

    /**
     * Parent category id
     */
    public ?int $parentId;

    /**
     * Category title
     */
    public string $title;
}
