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
 * Class Move
 *
 * @package HYPERPC\MoySklad\Entity\Document
 *
 * @since 2.0
 *
 * @todo implement support for internalOrder and files fields;
 */
class Move extends DocumentEntity
{
    /**
     * @Type("array<MoySklad\Entity\Attribute>")
     */
    public $attributes;

    /**
     * @Type("DateTime<'Y-m-d H:i:s.v'>")
     */
    public $created;

    /**
     * @Type("DateTime<'Y-m-d H:i:s.v'>")
     */
    public $deleted;

    /**
     * @Type("string")
     * @Generator()
     */
    public $description;

    /**
     * @Type("string")
     * @Generator()
     */
    public $externalCode;

    /**
     * @Type("MoySklad\Entity\Agent\Organization")
     * @Generator(type="object", anyFromExists=true)
     */
    public $organization;

    /**
     * @Type("MoySklad\Entity\Price")
     */
    public $overhead;

    /**
     * @Type("HYPERPC\MoySklad\Entity\Document\Position\MoveDocumentPositions")
     */
    public $positions;

    /**
     * @Type("MoySklad\Entity\Project")
     */
    public $project;

    /**
     * @Type("MoySklad\Entity\Rate")
     * @Generator(type="object")
     */
    public $rate;

    /**
     * @Type("MoySklad\Entity\Store\Store")
     * @Generator(type="object")
     */
    public $sourceStore;

    /**
     * @Type("MoySklad\Entity\State")
     */
    public $state;

    /**
     * @Type("string")
     */
    public $syncId;

    /**
     * @Type("MoySklad\Entity\Store\Store")
     * @Generator(type="object")
     */
    public $targetStore;
}
