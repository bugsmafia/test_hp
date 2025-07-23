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

namespace HYPERPC\MoySklad\Entity;

use JMS\Serializer\Annotation\Type;

/**
 * Class Meta
 *
 * @package HYPERPC\MoySklad\Entity
 *
 * @since   2.0
 */
class StockCurrentItem
{
    /**
     * @Type("string")
     */
    public $assortmentId;

    /**
     * @Type("string")
     */
    public $storeId;

    /**
     * @Type("float")
     */
    public $quantity;

    /**
     * @Type("float")
     */
    public $stock;

    /**
     * @Type("float")
     */
    public $freeStock;
}
