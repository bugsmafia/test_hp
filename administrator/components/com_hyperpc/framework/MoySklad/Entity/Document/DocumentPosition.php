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

use MoySklad\Entity\MetaEntity;
use JMS\Serializer\Annotation\Type;
use MoySklad\Util\Object\Annotation\Generator;

/**
 * Class DocumentPosition
 *
 * @package HYPERPC\MoySklad\Entity\Document
 *
 * @since 2.0
 */
class DocumentPosition extends MetaEntity
{
    /**
     * @Type("HYPERPC\MoySklad\Entity\Product\Product")
     * @Generator(type="object")
     *
     * @todo change type to productMarker
     */
    public $assortment;

    /**
     * @Type("MoySklad\Entity\Pack")
     */
    public $pack;

    /**
     * @Type("int")
     */
    public $price;

    /**
     * @Type("int")
     */
    public $quantity;
}
