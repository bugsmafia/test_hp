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
use MoySklad\Entity\MetaEntity;
use MoySklad\Util\Object\Annotation\Generator;
use HYPERPC\MoySklad\Entity\Document\DocumentEntity;

/**
 * Class SalesReturn
 *
 * @package HYPERPC\MoySklad\Entity\Document
 *
 * @since 2.0
 *
 * @todo implement support for payments and files fields;
 */
class SalesReturn extends DocumentEntity
{
    /**
     * @Type("MoySklad\Entity\Agent\Counterparty")
     * @Generator(type="object")
     */
    public $agent;

    /**
     * @Type("DateTime<'Y-m-d H:i:s.v'>")
     */
    public $created;

    /**
     * @Type("MoySklad\Entity\Document\RetailDemand")
     */
    public $demand;

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
     * @Type("float")
     */
    public $payedSum;

    /**
     * @Type("HYPERPC\MoySklad\Entity\Document\Position\SalesReturnDocumentPositions")
     */
    public $positions;

    /**
     * @Type("MoySklad\Entity\Rate")
     * @Generator(type="object")
     */
    public $rate;

    /**
     * @Type("MoySklad\Entity\Store\Store")
     * @Generator(type="object")
     */
    public $store;

    /**
     * @Type("MoySklad\Entity\State")
     */
    public $state;

    /**
     * @Type("bool")
     */
    public $vatIncluded;

    /**
     * @Type("bool")
     * @Generator()
     */
    public $vatEnabled;

    /**
     * @Type("int")
     */
    public $vatSum;

    /**
     * @Type("string")
     */
    public $syncId;

    /**
     * @Type("DateTime<'Y-m-d H:i:s.v'>")
     */
    public $deleted;

    /**
     * @Type("MoySklad\Entity\Contract")
     */
    public $contract;

    /**
     * @Type("MoySklad\Entity\Project")
     * @Generator(type="object")
     */
    public $project;

    /**
     * @Type("MoySklad\Entity\Account")
     */
    public $organizationAccount;

    /**
     * @Type("MoySklad\Entity\Account")
     */
    public $agentAccount;

    /**
     * @Type("array<MoySklad\Entity\Attribute>")
     */
    public $attributes = [];

    /**
     * @Type("array<HYPERPC\MoySklad\Entity\Document\Loss>")
     */
    public $losses = [];
}
