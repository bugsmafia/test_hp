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
 * Class DocumentEntity
 *
 * @package HYPERPC\MoySklad\Entity\Document
 *
 * @since 2.0
 */
abstract class DocumentEntity extends MetaEntity
{
    /**
     * @Type("string")
     *
     * @todo move to MetaEntity
     */
    public $name;

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
    public $shared = false;

    /**
     * @Type("MoySklad\Entity\Agent\Employee")
     */
    public $owner;

    /**
     * @Type("DateTime<'Y-m-d H:i:s.v'>")
     * @Generator(type="datetime")
     */
    public $moment;

    /**
     * @Type("bool")
     * @Generator()
     */
    public $applicable = true;

    /**
     * @Type("int")
     */
    public $sum;

    /**
     * @Type("bool")
     */
    public $printed = false;

    /**
     * @Type("bool")
     */
    public $published = false;
}
