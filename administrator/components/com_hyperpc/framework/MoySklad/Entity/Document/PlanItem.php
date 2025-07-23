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

namespace HYPERPC\MoySklad\Entity\Document;

use JMS\Serializer\Annotation\Type;
use MoySklad\Util\Object\Annotation\Generator;

/**
 * Class PlanItem
 *
 * @package HYPERPC\MoySklad\Entity\Document
 *
 * @since 2.0
 */
class PlanItem
{

    /**
     * @Type("string")
     */
    public $accountId;

    /**
     * @Type("MoySklad\Entity\Assortment")
     * @Generator(type="object")
     */
    public $assortment;

    /**
     * @Type("string")
     */
    public $id;

    /**
     * @Type("HYPERPC\MoySklad\Entity\Product\Product")
     * @Generator(type="object")
     */
    public $product;

    /**
     * @Type("float")
     */
    public $quantity;
}
