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
 * Class LossDocumentPosition
 *
 * @package HYPERPC\MoySklad\Entity\Document\Position
 *
 * @since 2.0
 */
class LossDocumentPosition extends DocumentPosition
{
    /**
     * @Type("string")
     * @Generator()
     */
    public $reason;

    /**
     * @Type("array")
     * @Generator()
     */
    public $things;
}
