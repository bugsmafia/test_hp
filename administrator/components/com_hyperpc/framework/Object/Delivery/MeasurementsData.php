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

use Joomla\CMS\Language\Text;
use Spatie\DataTransferObject\DataTransferObject;

class MeasurementsData extends DataTransferObject
{
    /**
     * Weight in kg
     */
    public float $weight = 1.0;

    /**
     * Dimensions data
     */
    public DimensionsData $dimensions;

    /**
     * Convert to string
     *
     * @return string
     */
    public function __toString()
    {
        $measurments = [
            $this->dimensions->length . Text::_('COM_HYEPRPC_WEIGHT_CM'),
            $this->dimensions->width . Text::_('COM_HYEPRPC_WEIGHT_CM'),
            $this->dimensions->height . Text::_('COM_HYEPRPC_WEIGHT_CM'),
            $this->weight . Text::_('COM_HYEPRPC_WEIGHT_KG')
        ];

        return implode('/', $measurments);
    }

    /**
     * Create from array
     */
    public static function fromArray(array $params)
    {
        return new self($params);
    }
}
