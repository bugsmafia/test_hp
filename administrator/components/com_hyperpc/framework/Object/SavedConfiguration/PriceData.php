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

namespace HYPERPC\Object\SavedConfiguration;

use HYPERPC\Money\Type\Money;
use Spatie\DataTransferObject\DataTransferObject;

class PriceData extends DataTransferObject
{
    /**
     * Product cost without any external parts and services
     */
    public Money $product;

    /**
     * Services cost
     */
    public Money $services;

    /**
     * Total cost of the configuration
     */
    public Money $total;
}
