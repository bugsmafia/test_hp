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

namespace HYPERPC\Object\Compatibility;

use HYPERPC\ORM\Entity\Compatibility;
use Spatie\DataTransferObject\DataTransferObjectCollection;

class CompatibilityDataCollection extends DataTransferObjectCollection
{
    public function current(): CompatibilityData
    {
        return parent::current();
    }

    /**
     * @param   Compatibility[] $data
     */
    public static function fromCompatibilitiesArray(array $data): self
    {
        return new static(
            array_map(function ($compatibility) {
                return CompatibilityData::fromEntity($compatibility);
            }, $data)
        );
    }
}
