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

namespace HYPERPC\Helper\Traits;

use MoySklad\Entity\Barcode;
use MoySklad\Entity\MetaEntity;

/**
 * Trait MoyskladEntityBarcodes
 *
 * @package HYPERPC\Helper\Traits
 *
 * @since   2.0
 */
trait MoyskladEntityBarcodes
{

    /**
     * Get barcodes from Moysklad entity
     *
     * @param   MetaEntity $entity
     *
     * @return  array
     *
     * @since   2.0
     */
    protected function _getBarcodesFromMoyskladEntity(MetaEntity $entity)
    {
        if (property_exists($entity, 'barcodes') && is_array($entity->barcodes)) {
            $barcodes = [];
            /** @var Barcode $barcode */
            foreach ($entity->barcodes as $barcode) {
                $barcodes[] = [
                    $barcode->type => $barcode->value
                ];
            }

            return $barcodes;
        }

        return [];
    }
}
