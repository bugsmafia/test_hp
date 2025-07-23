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

namespace HYPERPC\MoySklad\Entity\Agent;

use JMS\Serializer\Annotation\Type;
use MoySklad\Util\Object\Annotation\Generator;
use MoySklad\Entity\Agent\Counterparty as CounterpartyBase;

/**
 * Class Counterparty
 *
 * @package HYPERPC\MoySklad\Entity\Agent;
 *
 * @since 2.0
 *
 * @todo add support for files field
 * @todo add support for discounts field
 */
class Counterparty extends CounterpartyBase
{
    /**
     * @Type("string")
     */
    public $externalCode;

    /**
     * @Type("MoySklad\Entity\Agent\Employee")
     */
    public $owner;

    /**
     * @Type("bool")
     */
    public $shared;

    /**
     * @Type("MoySklad\Entity\Group")
     * @Generator(type="object", anyFromExists=true)
     */
    public $group;

    /**
     * @Type("DateTime<'Y-m-d H:i:s.v'>")
     */
    public $updated;

    /**
     * @Type("bool")
     */
    public $archived;

    /**
     * @Type("DateTime<'Y-m-d H:i:s.v'>")
     */
    public $created;

    /**
     * @Type("string")
     */
    public $email;

    /**
     * @Type("string")
     */
    public $phone;

    /**
     * @Type("string")
     */
    public $inn;

    /**
     * @Type("string")
     */
    public $description;

    /**
     * @Type("string")
     * @Generator(setNullIfNotIn={"companyType": {"individual", "entrepreneur"}})
     */
    public $legalFirstName;

    /**
     * @Type("string")
     * @Generator(setNullIfNotIn={"companyType": {"individual", "entrepreneur"}})
     */
    public $legalMiddleName;

    /**
     * @Type("string")
     * @Generator(setNullIfNotIn={"companyType": {"individual", "entrepreneur"}})
     */
    public $legalLastName;

    /**
     * @Type("array<MoySklad\Entity\Attribute>")
     */
    public $attributes = [];
}
