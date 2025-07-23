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

namespace HYPERPC\Object\Compare\CategoryTree;

use Spatie\DataTransferObject\DataTransferObject;

class CategoryProductData extends DataTransferObject
{
    public int $id;

    public string $type;

    public string $name;

    public string $price;

    public string $image;

    public string $itemKey;

    public bool $isInCompare;

    public string $description;

    public ?int $stockId;

    public ?int $optionId;
}
