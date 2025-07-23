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

namespace HYPERPC\Object\Delivery;

use Spatie\DataTransferObject\DataTransferObject;

class DimensionsData extends DataTransferObject
{
    /**
     * Length in cm
     */
    public int $length = 45;

    /**
     * Width in cm
     */
    public int $width = 25;

    /**
     * Height in cm
     */
    public int $height = 55;
}
