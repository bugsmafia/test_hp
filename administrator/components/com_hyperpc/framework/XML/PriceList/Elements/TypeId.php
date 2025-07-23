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
 * @author      Artem vyshnevskiy
 */

namespace HYPERPC\XML\PriceList\Elements;

/**
 * Interface TypeId
 *
 * @package HYPERPC\XML\PriceList
 *
 * @since   2.0
 */
interface TypeId
{
    const PRODUCTS_TYPE_ID = 1;
    const PARTS_TYPE_ID    = 2;
    const SERVICES_TYPE_ID = 3;

    const PRODUCTS_TYPE_PC_ID          = 1;
    const PRODUCTS_TYPE_NOTEBOOK_ID    = 2;
    const PRODUCTS_TYPE_WORKSTATION_ID = 3;
    const PRODUCTS_TYPE_CONCEPT_ID     = 4;
    const PRODUCTS_TYPE_SERVER_ID      = 5;
    const PRODUCTS_TYPE_STATION_ID     = 6;
}
