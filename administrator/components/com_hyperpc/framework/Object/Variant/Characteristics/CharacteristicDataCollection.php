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

use Spatie\DataTransferObject\DataTransferObjectCollection;

class CharacteristicDataCollection extends DataTransferObjectCollection
{
    public function current(): CharacteristicData
    {
        return parent::current();
    }

    public static function create(array $data): CharacteristicDataCollection
    {
        return new static(CharacteristicData::arrayOf($data));
    }
}
