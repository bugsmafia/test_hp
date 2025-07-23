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

namespace HYPERPC\MoySklad\Entity\Product;

use JMS\Serializer\Annotation\Type;
use MoySklad\Entity\Product\Product as BaseProduct;

class Product extends BaseProduct
{
    /**
     * @Type("array<HYPERPC\MoySklad\Entity\Attribute>")
     */
    public $attributes = [];

    /**
     * @Type("string")
     */
    public $ppeType;

    /**
     * @Type("bool")
     */
    public $partialDisposal;

    /**
     * @Type("bool")
     */
    public $onTap;

    /**
     * @Type("bool")
     */
    public $discountProhibited;
}
