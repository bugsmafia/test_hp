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

namespace HYPERPC\Helper;

use RuntimeException;
use HYPERPC\ORM\Table\Table;
use MoySklad\Entity\MetaEntity;
use MoySklad\Entity\Store\Store;
use HYPERPC\Joomla\Model\ModelAdmin;
use HYPERPC\Helper\Context\EntityContext;
use HYPERPC\Joomla\Model\Entity\MoyskladStore;
use HYPERPC\Helper\Traits\MoyskladEntityActions;
use HyperPcModelMoysklad_Store as ModelMoyskladStore;

/**
 * Class MoyskladStoreHelper
 *
 * @package HYPERPC\Helper
 *
 * @since   2.0
 */
class MoyskladStoreHelper extends EntityContext
{

    use MoyskladEntityActions;

    /**
     * Hold model.
     *
     * @var     ModelMoyskladStore
     *
     * @since   2.0
     */
    protected static $_model;

    /**
     * Get model.
     *
     * @return  ModelMoyskladStore
     *
     * @since   2.0
     */
    public function getModel()
    {
        if (!isset(self::$_model)) {
            self::$_model = ModelAdmin::getInstance('Moysklad_Store');
        }
        return self::$_model;
    }

    /**
     * Initialize helper.
     *
     * @return  void
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function initialize()
    {
        $table = Table::getInstance('Moysklad_Stores');
        $this->setTable($table);

        parent::initialize();
    }

    /**
     * Prepare data array for stores
     *
     * @param   Store $entity
     *
     * @return  array
     *
     * @throws  \Exception
     * @throws  \InvalidArgumentException
     *
     * @since   2.0
     */
    public function prepareData(MetaEntity $entity): array
    {
        if (!($entity instanceof Store)) {
            throw new \InvalidArgumentException(
                'Argument 1 passed to ' . __METHOD__ . ' must be an instance of ' . Store::class . ', ' . get_class($entity) . ' given'
            );
        }

        $storeLevel    = 1;
        $parentStoreId = 1;
        $parentStore = $entity->parent;
        if ($parentStore instanceof Store) {
            $parentStoreUuid = $this->hyper['helper']['moysklad']->getEntityUuidFromHref($parentStore->getMeta()->href);

            $hpParentStore = $this->findBy('uuid', $parentStoreUuid);
            if ($hpParentStore instanceof MoyskladStore && $hpParentStore->id) {
                $parentStoreId = $hpParentStore->id;
                $storeLevel    = $hpParentStore->level + 1;
            }
        }

        return [
            'uuid'          => $entity->id,
            'path'          => $entity->pathName,
            'name'          => $entity->name,
            'level'         => $storeLevel,
            'parent_id'     => $parentStoreId
        ];
    }

    /**
     * Get stores list.
     *
     * @param   bool  $published
     *
     * @return  MoyskladStore[]
     *
     * @throws  \Exception
     * @throws  RuntimeException
     *
     * @since   2.0
     */
    public function getList($published = true)
    {
        $db = $this->hyper['db'];
        $conditions = ['NOT ' . $db->quoteName('a.alias') . ' = ' . $db->quote('root')];

        if ($published === true) {
            $conditions[] = $db->quoteName('a.published') . ' = ' . HP_STATUS_PUBLISHED;
        }

        return $this->findAll([
            'conditions' => $conditions,
            'order'      => $db->quoteName('a.lft') . ' ASC'
        ]);
    }

    /**
     * Convert moysklad store's id to legacy store's id
     *
     * @param   int $id
     *
     * @return  int
     *
     * @since   2.0
     *
     * @todo    do it the right way (via settings)
     */
    public function convertToLagacyId($id)
    {
        if (in_array((int) $id, [12, 14])) {
            return 2; // Spb
        }

        return 1; // Moscow
    }

    /**
     * Convert legacy store's id to moysklad id
     *
     * @param   int $id
     *
     * @return  array
     *
     * @since   2.0
     *
     * @todo    do it the right way (via settings)
     */
    public function convertFromLegacyId($id)
    {
        $spbIds = [12, 14];
        if ($id === 2) {
            return $spbIds; // Spb
        }

        $stores = $this->getList(true);

        return array_diff(array_keys($stores), $spbIds); // Moscow
    }
}
