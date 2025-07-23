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

namespace HYPERPC\MoySklad\Entity\Document\Position;

use JMS\Serializer\Annotation\Type;
use MoySklad\Util\Object\Annotation\Generator;
use HYPERPC\MoySklad\Entity\Document\DocumentPosition;

/**
 * Class CustomerOrderDocumentPositio
 *
 * @package HYPERPC\MoySklad\Entity\Document\Position
 *
 * @since 2.0
 */
class CustomerOrderDocumentPosition extends DocumentPosition
{
    /**
     * @Type("float")
     */
    public $discount;

    /**
     * @Type("float")
     */
    public $reserve;

    /**
     * @Type("int")
     */
    public $shipped;

    /**
     * @Type("string")
     * @Generator(
     *     values={
     *         "TAX_SYSTEM_SAME_AS_GROUP",
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

    /**
     * @Type("int")
     */
    public $vat;
}
