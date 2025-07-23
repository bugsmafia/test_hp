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

namespace HYPERPC\Object\Date;

use Joomla\CMS\Date\Date;
use Spatie\DataTransferObject\DataTransferObject;

class DatesRange extends DataTransferObject
{
    /**
     * Min date
     */
    public ?Date $min;

    /**
     * Max date
     */
    public ?Date $max;
}
