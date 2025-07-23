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
 * Class ProcessingPlan
 *
 * @package HYPERPC\MoySklad\Entity\Document
 *
 * @since 2.0
 */
class ProcessingPlan extends DocumentEntity
{

    /**
     * @Type("int")
     */
    public $cost;

    /**
     * @Type("string")
     */
    public $externalCode;

    /**
     * @Type("HYPERPC\MoySklad\Entity\Document\PlanItems")
     */
    public $materials;

    /**
     * @Type("string")
     */
    public $pathName;

    /**
     * @Type("HYPERPC\MoySklad\Entity\Document\PlanItems")
     */
    public $products;

    /**
     * @Type("DateTime<'Y-m-d H:i:s.v'>")
     */
    public $deleted;

    /**
     * @Type("MoySklad\Entity\Group")
     * @Generator(type="object", anyFromExists=true)
     */
    public $parent;

    /**
     * @Type("array<HYPERPC\MoySklad\Entity\Attribute>")
     */
    public $attributes = [];
}
