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

namespace HYPERPC\Object\MiniConfigurator;

use HYPERPC\Money\Type\Money;
use Spatie\DataTransferObject\DataTransferObject;

class ProductPartData extends DataTransferObject
{
    /**
     * Part itemkey
     */
    public string $itemKey;

    /**
     * Part images
     */
    public array $image;

    /**
     * Part name
     */
    public string $name;

    /**
     * Part is default
     */
    public bool $isDefault;

    /**
     * Part fields
     */
    public array $fields;

    /**
     * Part js data
     */
    public ProductPartJsData $jsData;

    /**
     * Part advantages
     */
    public array $advantages;

    /**
     * Part content is override
     */
    public bool $isContentOverriden;

    /**
     * Price difference with installed part
     */
    public Money $priceDifference;
}
