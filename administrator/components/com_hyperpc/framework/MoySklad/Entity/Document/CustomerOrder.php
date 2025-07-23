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
 * Class CustomerOrder
 *
 * @package HYPERPC\MoySklad\Entity\Document
 *
 * @since 2.0
 */
class CustomerOrder extends DocumentEntity
{
    /**
     * @Type("HYPERPC\MoySklad\Entity\Agent\Counterparty")
     * @Generator(type="object")
     */
    public $agent;

    /**
     * @Type("MoySklad\Entity\Contract")
     */
    public $contract;

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
     * @Type("int")
     */
    public $invoicedSum;

    /**
     * @Type("MoySklad\Entity\Agent\Organization")
     * @Generator(type="object", anyFromExists=true)
     */
    public $organization;

    /**
     * @Type("int")
     */
    public $payedSum;

    /**
     * @Type("HYPERPC\MoySklad\Entity\Document\Position\CustomerOrderDocumentPositions")
     */
    public $positions;

    /**
     * @Type("MoySklad\Entity\Rate")
     * @Generator(type="object")
     */
    public $rate;

    /**
     * @Type("int")
     */
    public $reservedSum;

    /**
     * @Type("int")
     */
    public $shippedSum;

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
     * @Type("MoySklad\Entity\Account")
     */
    public $organizationAccount;

    /**
     * @Type("MoySklad\Entity\Account")
     */
    public $agentAccount;

    /**
     * @Type("array<HYPERPC\MoySklad\Entity\Attribute>")
     */
    public $attributes = [];

    /**
     * @Type("DateTime<'Y-m-d H:i:s.v'>")
     * @Generator(type="datetime")
     */
    public $deliveryPlannedMoment;

    /**
     * @Type("MoySklad\Entity\Project")
     */
    public $project;

    /**
     * @Type("array<MoySklad\Entity\Document\CustomerOrder>")
     */
    public $purchaseOrders = [];

    /** @todo array<Demand> demands */
    /** @todo array<FinanceDocumentMarker> payments */
    /** @todo array<InvoiceOut> invoicesOut */

    /**
     * @Type("string")
     * @Generator(
     *     values={
     *         "GENERAL_TAX_SYSTEM",
     *         "SIMPLIFIED_TAX_SYSTEM_INCOME",
     *         "SIMPLIFIED_TAX_SYSTEM_INCOME_OUTCOME",
     *         "UNIFIED_AGRICULTURAL_TAX",
     *         "PRESUMPTIVE_TAX_SYSTEM",
     *         "PATENT_BASED"
     *     }
     * )
     */
    public $taxSystem;

    /** @todo ListEntity<AttachedFile> files */

    /**
     * @Type("string")
     */
    public $shipmentAddress;

    /** @todo shipmentAddressFull */
}
