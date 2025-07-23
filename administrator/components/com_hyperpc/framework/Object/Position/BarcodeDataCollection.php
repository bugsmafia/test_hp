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

namespace HYPERPC\Object\Position;

use Spatie\DataTransferObject\DataTransferObjectCollection;

class BarcodeDataCollection extends DataTransferObjectCollection
{
    public function current(): BarcodeData
    {
        return parent::current();
    }

    public static function fromPositionBarcodes(array $data): self
    {
        return new static(
            array_map(function ($barcode) {
                $type = array_key_first($barcode);
                return new BarcodeData([
                    'type' => $type,
                    'value' => $barcode[$type]
                ]);
            }, $data)
        );
    }
}
