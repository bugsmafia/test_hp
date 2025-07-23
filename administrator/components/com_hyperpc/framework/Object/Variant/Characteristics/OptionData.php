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

namespace HYPERPC\Object\Variant\Characteristics;

use Spatie\DataTransferObject\DataTransferObject;

class OptionData extends DataTransferObject
{
    /**
     * Option value.
     */
    public string $value;

    /**
     * Variant id.
     */
    public ?int $variant_id;

    /**
     * Active flag.
     */
    public bool $is_active;
}
