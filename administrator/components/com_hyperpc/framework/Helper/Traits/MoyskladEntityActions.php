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

namespace HYPERPC\Helper\Traits;

use MoySklad\Entity\Variant;
use MoySklad\Entity\MetaEntity;
use MoySklad\Entity\Product\Product;
use HYPERPC\Joomla\Model\ModelAdmin;
use HYPERPC\Joomla\Model\Entity\Entity;
use MoySklad\Entity\Product\ProductFolder;
use HYPERPC\ORM\Entity\PriceRecountQueueItem;
use HYPERPC\Joomla\Model\Entity\MoyskladPart;
use HYPERPC\Joomla\Model\Entity\MoyskladProduct;
use HYPERPC\Joomla\Model\Entity\MoyskladService;
use HYPERPC\Joomla\Model\Entity\MoyskladVariant;

/**
 * Trait MoyskladEntityActions
 *
 * @package HYPERPC\Helper\Traits
 *
 * @since   2.0
 */
trait MoyskladEntityActions
{

    /**
     * Get model.
     *
     * @return  ModelAdmin
     *
     * @since   2.0
     */
    abstract public function getModel();

    /**
     * Prepare data.
     *
     * @param   MetaEntity $entity
     *
     * @return  array
     *
     * @since   2.0
     */
    abstract public function prepareData(MetaEntity $entity): array;

    /**
     * Create service by Moysklad entity
     *
     * @param   MetaEntity $entity
     *
     * @return  int
     *
     * @throws  \Exception
     * @throws  \RuntimeException
     * @throws  \InvalidArgumentException
     * @throws  \UnexpectedValueException
     *
     * @since   2.0
     */
    public function createByMoyskladEntity(MetaEntity $entity)
    {
        if ($entity instanceof Variant) {
            $parent = $this->hyper['helper']['moyskladVariant']->getParentFromMoyskladEntity($entity);
            if ($parent instanceof MoyskladProduct) {
                return 0; // Don't create product variants as moyskladVariant entity
            }
        } elseif ($entity instanceof Product) {
            if ($entity->productFolder && $entity->productFolder->getMeta()->getId() === $this->hyper['helper']['productFolder']->getOrderProductsFolderUuid()) {
                return 0; // Don't create products in the order's products folder
            }
        }

        $data = $this->prepareData($entity);

        $model = $this->getModel();

        $result = $model->save($data);
        if (!$result) {
            $this->hyper['helper']['moysklad']->log(__FUNCTION__ . ' ' . get_class($entity) . ' ' . json_encode($model->getErrors()));

            return 0;
        }

        $hpEntity = $model->loadFormData();

        if (!($hpEntity instanceof Entity)) {
            return 0;
        }

        $this->_onAfterCreate($hpEntity);

        return $hpEntity->id;
    }

    /**
     * Update service by Moysklad entity
     *
     * @param   MetaEntity $entity
     * @param   array $updatedFields
     *
     * @return  int
     *
     * @throws  \Exception
     * @throws  \RuntimeException
     * @throws  \InvalidArgumentException
     * @throws  \UnexpectedValueException
     *
     * @since   2.0
     */
    public function updateByMoyskladEntity(MetaEntity $entity, array $updatedFields = [])
    {
        if ($entity instanceof Product && $entity->productFolder instanceof ProductFolder) {
            $parentUuid = $entity->productFolder->getMeta()->getId();
            if ($parentUuid === $this->hyper['helper']['productFolder']->getOrderProductsFolderUuid()) {
                return $entity->externalCode; // Don't try to update products in the order's products folder
            }
        }

        $entityId = $entity->externalCode;

        if (is_numeric($entityId)) {
            $hpEntity = parent::findById($entityId);
        }

        // Try to find by uuid
        if (!isset($hpEntity) || empty($hpEntity->id)) {
            $hpEntity = parent::findBy('uuid', $entity->id);
        }

        if (!$hpEntity->id) {
            return $this->createByMoyskladEntity($entity);
        }

        if ($hpEntity instanceof MoyskladPart || $hpEntity instanceof MoyskladProduct) {
            $entityType = $this->hyper['helper']['position']->getPositionTypeFromMoyskladEntity($entity);
            $hpEntityType = $hpEntity->getType();
            if ($hpEntityType !== $entityType) { // entity type changed
                $this->moveToTrashByUuid($hpEntity->uuid);
                return $this->createByMoyskladEntity($entity);
            }
        }

        $data = $this->prepareData($entity);

        // Don't update product prices from moysklad
        if ($hpEntity instanceof MoyskladProduct) {
            unset($data['list_price']);
            unset($data['sale_price']);
        } elseif ($hpEntity instanceof MoyskladService || $hpEntity instanceof MoyskladVariant) {
            // create query item for update related products price
            if (empty($updatedFields) || in_array('salePrices', $updatedFields)) {
                $queueItem = new PriceRecountQueueItem();

                $queueItem->part_id = $hpEntity->id;
                $queueItem->option_id = 0;

                if ($hpEntity instanceof MoyskladVariant) {
                    $queueItem->part_id = $hpEntity->part_id;
                    $queueItem->option_id = $hpEntity->id;
                }

                $queueItem->getTable()->save($queueItem);
            }
        }

        // manage archive state
        if (property_exists($entity, 'archived')) {
            $archived = $entity->archived;
            $publishedProperty = property_exists($hpEntity, 'published') ? 'published' : (property_exists($hpEntity, 'state') ? 'state' : null);

            if ($archived && $publishedProperty) {
                if ($hpEntity->$publishedProperty !== HP_STATUS_ARCHIVED) {
                    $data[$publishedProperty] = HP_STATUS_ARCHIVED;

                    if (property_exists($hpEntity, 'params')) {
                        $hpEntity->params->set('pre_archived_state', $hpEntity->$publishedProperty);
                    }
                }
            } elseif ($publishedProperty) { // !archived
                if ($hpEntity->$publishedProperty === HP_STATUS_ARCHIVED) { // Archive status changed
                    $newStatus = HP_STATUS_UNPUBLISHED;
                    if (property_exists($hpEntity, 'params') && $hpEntity->params->get('pre_archived_state') !== null) {
                        $newStatus = $hpEntity->params->get('pre_archived_state');
                    }
                    $data[$publishedProperty] = $newStatus;
                }
            }
        }

        $hpEntity->bindData($data);

        $this->getModel()->save($hpEntity->getProperties());

        return $hpEntity->id;
    }

    /**
     * Move entity to trash by uuid
     *
     * @param   string $uuid
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function moveToTrashByUuid(string $uuid)
    {
        $hpEntity = parent::findBy('uuid', $uuid);

        if ($hpEntity->id) {
            if (property_exists($hpEntity, 'published')) {
                $hpEntity->set('published', HP_STATUS_TRASHED);
            }

            if (property_exists($hpEntity, 'state')) {
                $hpEntity->set('state', HP_STATUS_TRASHED);
            }

            $this->getModel()->save($hpEntity->getProperties());
        }
    }

    /**
     * On after create entity
     *
     * @param   Entity $entity
     *
     * @since   2.0
     */
    protected function _onAfterCreate(Entity $entity)
    {
    }
}
