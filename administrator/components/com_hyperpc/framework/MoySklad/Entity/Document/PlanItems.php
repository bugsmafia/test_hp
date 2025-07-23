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
use MoySklad\Entity\AbstractListEntity;

/**
 * Class PlanItems
 *
 * @package HYPERPC\MoySklad\Entity\Document
 *
 * @since 2.0
 */
class PlanItems extends AbstractListEntity
{

    /**
     * @Type("array<HYPERPC\MoySklad\Entity\Document\PlanItem>")
     */
    public $rows;
}
