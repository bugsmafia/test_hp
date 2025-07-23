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

use HYPERPC\App;
use MoySklad\Entity\Assortment;
use HYPERPC\Joomla\Model\Entity\MoyskladPart;
use HYPERPC\MoySklad\Entity\Document\PlanItem;
use Spatie\DataTransferObject\DataTransferObject;

class PlanItemData extends DataTransferObject
{
    /**
     * Position id
     */
    public int $id;

    /**
     * Variant id
     */
    public ?int $option_id;

    /**
     * Position name
     */
    public string $name;

    /**
     * Position quantity
     */
    public int $quantity = 1;

    /**
     * Assortment type (product|variant)
     */
    public string $type;

    /**
     * Convert to MoyskladPart entity
     */
    public function toMoyskladPart(): MoyskladPart
    {
        $hp = App::getInstance();
        /** @var MoyskladPart */
        $part = $hp['helper']['moyskladPart']->findById($this->id);
        $part->set('quantity', $this->quantity);
        if ($this->option_id) {
            $part->set(
                'option',
                $hp['helper']['moyskladVariant']->findById($this->option_id)
            );
        }

        return $part;
    }

    /**
     * Data from Moysklad Processingplan PlanItem
     */
    public static function fromMoyskladPlanItem(PlanItem $planItem): self
    {
        $hp = App::getInstance();
        $assortmentUuid = $planItem->assortment->getMeta()->getId();
        $type = $planItem->assortment->getMeta()->type;
        $helper = $type === 'variant' ? 'moyskladVariant' : 'moyskladPart';
        $entity = $hp['helper'][$helper]->findBy('uuid', $assortmentUuid);

        if (empty($entity->id)) {
            /** @todo log it */
            trigger_error("Not found {$type} by assortmentUuid", E_USER_WARNING);
        }

        $quantity = $planItem->quantity;
        $id = $entity->id;
        $name = $entity->name;
        $optionId = null;
        if ($type === 'variant') {
            $optionId = $id;
            $part = $entity->getPart();
            $id = $part->id;
            $name = "{$part->name} {$name}";
        }

        return new self([
            'id' => $id,
            'option_id' => $optionId,
            'name' => $name,
            'quantity' => (int) $quantity,
            'type' => $type
        ]);
    }

    /**
     * Convert to Moysklad PlanItem
     */
    public function toMoyskladPlanItem(): PlanItem
    {
        $hp = App::getInstance();
        
        $helper = $this->type === 'variant' ? 'moyskladVariant' : 'moyskladPart';
        $entity = $hp['helper'][$helper]->findById($this->type === 'variant' ? $this->option_id : $this->id);

        $moyskladHelper = $hp['helper']['moysklad'];
        $planItem = new PlanItem();

        $planItem->assortment = new Assortment(
            $moyskladHelper->buildEntityMeta(
                $this->type,
                $entity->uuid
            )
            ->toBaseMeta()
        );

        $planItem->quantity = $this->quantity;

        return $planItem;
    }
}
