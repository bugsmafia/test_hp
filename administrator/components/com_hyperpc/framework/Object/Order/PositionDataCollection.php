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

namespace HYPERPC\Object\Order;

use HYPERPC\Object\Order\PositionData;
use Spatie\DataTransferObject\DataTransferObjectCollection;

class PositionDataCollection extends DataTransferObjectCollection
{
    public function current(): PositionData
    {
        return parent::current();
    }

    public static function create(array $data): PositionDataCollection
    {
        array_walk($data, function (&$position) {
            $position['price'] = (float) ($position['price'] ?? 0.0);
            $position['discount'] = (float) ($position['discount'] ?? 0.0);
        });

        return new static(PositionData::arrayOf($data));
    }
}
