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

use HYPERPC\ORM\Table\Table;
use MoySklad\Entity\MetaEntity;
use HYPERPC\Joomla\Model\ModelAdmin;
use MoySklad\Entity\Product\Service;
use HYPERPC\Helper\Traits\EntitySubtype;
use HYPERPC\Helper\Traits\MoyskladEntityActions;
use HyperPcModelMoysklad_Service as ModelMoyskladService;

/**
 * Class MoyskladServiceHelper
 *
 * @package HYPERPC\Helper
 *
 * @since   2.0
 */
class MoyskladServiceHelper extends PositionHelper
{
    use EntitySubtype {
        EntitySubtype::_getFromQuery as _getSubtypeFromQuery;
        EntitySubtype::_getTraitQuery as _getSubtypeQuery;
    }

    use MoyskladEntityActions;

    /**
     * Get query for from condition
     *
     * @return  string
     *
     * @throws  \Exception
     * @throws  \RuntimeException
     *
     * @since   2.0
     */
    protected function _getFromQuery()
    {
        $subtypeQuery = $this->_getSubtypeQuery();
        $parentQuery = parent::_getTraitQuery();

        $parentQuery
            ->clear('from')
            ->from($this->_db->qn($this->_getSupertypeTable()->getTableName(), 'a'));

        $subtypeQuery
            ->clear('join')
            ->join('INNER', "({$parentQuery}) AS t", 'st.id = t.id');
        
        return "({$subtypeQuery}) AS a";
    }

    /**
     * Hold model.
     *
     * @var     ModelMoyskladService
     *
     * @since   2.0
     */
    protected static $_model;

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
        $table = Table::getInstance('Moysklad_Services');
        $table->setEntity('MoyskladService');

        $this->setTable($table);

        $this->_db = $this->_table->getDbo();
    }

    /**
     * Get supertype table name.
     *
     * @return  string
     */
    protected function _getSupertypeTableName(): string
    {
        return 'Positions';
    }

    /**
     * Get model.
     *
     * @return  ModelMoyskladService
     *
     * @since   2.0
     */
    public function getModel()
    {
        if (!isset(self::$_model)) {
            self::$_model = ModelAdmin::getInstance('Moysklad_Service');
        }
        return self::$_model;
    }

    /**
     * Clear array of parts from external parts
     *
     * @param   array $data
     *
     * @return  array
     *
     * @since   2.0
     *
     * @todo    move to a better place
     */
    public function clearExternalParts(array $data)
    {
        foreach ($data as $groupId => $group) {
            foreach ($group as $key => $part) {
                if ($part->isDetached()) {
                    unset($data[$groupId][$key]);

                    if (!count($data[$groupId])) {
                        unset($data[$groupId]);
                    }
                }
            }
        }

        return $data;
    }

    /**
     * Prepare data.
     *
     * @param   Service $entity
     *
     * @return  array
     *
     * @throws  \InvalidArgumentException
     *
     * @since   2.0
     */
    public function prepareData(MetaEntity $entity): array
    {
        if (!($entity instanceof Service)) {
            throw new \InvalidArgumentException(
                'Argument 1 passed to ' . __METHOD__ . ' must be an instance of ' . Service::class . ', ' . get_class($entity) . ' given'
            );
        }

        return [
            'uuid'              => $entity->id,
            'type_id'           => 1,
            'name'              => $entity->name,
            'product_folder_id' => $this->_getParentFolderId($entity),
            'list_price'        => $this->_getListPriceFromMoyskladEntity($entity),
            'sale_price'        => $this->_getSalePriceFromMoyskladEntity($entity),
            'vat'               => (int) $entity->effectiveVat,
            'barcodes'          => json_encode($this->_getBarcodesFromMoyskladEntity($entity), JSON_PRETTY_PRINT)
        ];
    }
}
