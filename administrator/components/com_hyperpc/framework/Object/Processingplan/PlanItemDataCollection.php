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

namespace HYPERPC\Object\Processingplan;

use HYPERPC\MoySklad\Entity\Document\PlanItems;
use HYPERPC\Object\Processingplan\PlanItemData;
use Spatie\DataTransferObject\DataTransferObjectCollection;

class PlanItemDataCollection extends DataTransferObjectCollection
{
    public function current(): PlanItemData
    {
        return parent::current();
    }

    public static function create(array $data): self
    {
        return new static(PlanItemData::arrayOf($data));
    }

    /**
     * Convert data collection to Moysklad PlanItems object
     */
    public function toPlanItems(): PlanItems
    {
        $planItems = new PlanItems();
        $planItems->rows = [];

        /** @var PlanItemData $planItemData */
        foreach ($this->items() as $planItemData) {
            $planItems->rows[] = $planItemData->toMoyskladPlanItem();
        }

        return $planItems;
    }

    /**
     * Convert data collection to array of MoyskladPart entities
     *
     * @return  MoyskladPart[]
     */
    public function toMoyskladParts(): array
    {
        $parts = [];
        /** @var PlanItemData $planItemData */
        foreach ($this->items() as $planItemData) {
            $part = $planItemData->toMoyskladPart();
            $itemKey = $part->getItemKey();
            if (isset($parts[$itemKey])) {
                $parts[$itemKey]->quantity += $part->quantity;
            } else {
                $parts[$itemKey] = $part;
            }
        }

        return $parts;
    }
}
