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

namespace HYPERPC\Object\Mail\Configuration\Specification;

use Spatie\DataTransferObject\DataTransferObject;
use HYPERPC\Object\Mail\Configuration\Specification\ItemData;

class SectionData extends DataTransferObject
{
    /**
     * Section title
     */
    public string $sectionTitle;

    /**
     * Section price
     */
    public string $sectionPrice;

    /**
     * Section items
     *
     * @var ItemData[]
     */
    public array $items = [];
}
