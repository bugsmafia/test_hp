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
 * @todo implement support for files field;
 */
class Loss extends DocumentEntity
{
    /**
     * @Type("MoySklad\Entity\Agent\Counterparty")
     * @Generator(type="object")
     */
    public $agent;

    /**
     * @Type("array<MoySklad\Entity\Attribute>")
     */
    public $attributes;

    /**
     * @Type("DateTime<'Y-m-d H:i:s.v'>")
     */
    public $created;

    /**
     * @Type("MoySklad\Entity\Contract")
     */
    public $contract;

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
     * @Type("HYPERPC\MoySklad\Entity\Document\Position\LossDocumentPositions")
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
     * @Type("MoySklad\Entity\State")
     */
    public $state;

    /**
     * @Type("MoySklad\Entity\Store\Store")
     * @Generator(type="object")
     */
    public $store;

    /**
     * @Type("string")
     */
    public $syncId;

    /**
     * @Type("MoySklad\Entity\Document\SalesReturn")
     */
    public $salesReturn;

    /**
     * @Type("bool")
     */
    public $vatEnabled;

    /**
     * @Type("bool")
     */
    public $vatIncluded;
}
