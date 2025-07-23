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
 * @author      Artem Vyshnevskiy
 */

namespace HYPERPC\Object\Delivery;

use Spatie\DataTransferObject\DataTransferObjectCollection;

class TariffDataCollection extends DataTransferObjectCollection
{
    public function current(): TariffData
    {
        return parent::current();
    }

    public static function create(array $data): TariffDataCollection
    {
        usort($data, function ($tariff1, $tariff2) {
            return ($tariff1['cost'] <=> $tariff2['cost']);
        });

        return new static(TariffData::arrayOf($data));
    }
}
