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
use MoySklad\Entity\Product\Product;
use HYPERPC\Helper\Traits\EntitySubtype;
use HYPERPC\Joomla\Model\Entity\MoyskladPart;
use HYPERPC\Helper\Traits\MoyskladEntityActions;
use HyperPcModelMoysklad_Part as ModelMoyskladPart;
use HYPERPC\Joomla\Model\Entity\Interfaces\PartMarker;

/**
 * Class MoyskladPartHelper
 *
 * @package HYPERPC\Helper
 *
 * @since   2.0
 */
class MoyskladPartHelper extends PositionHelper
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
     * @var     ModelMoyskladPart
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
        $table = Table::getInstance('Moysklad_Parts');
        $table->setEntity('MoyskladPart');
        $this->setTable($table);

        $this->_db = $this->_table->getDbo();
    }

    /**
     * Get parts by array if item keys
     *
     * @param   array $itemKeys
     *
     * @return  MoyskladPart[]
     *
     * @throws  \Exception
     */
    public function getByItemKeys(array $itemKeys): array
    {
        $partIds = [];
        $optionIds = [];

        $itemIds = [];

        foreach ($itemKeys as $itemKey) {
            preg_match('/position-(\d+)(-(\d+))?/', $itemKey, $matches);
            if (empty($matches)) {
                continue;
            }

            $itemIds[$itemKey] = ['id' => $matches[1]];
            $partIds[] = $matches[1];

            if (isset($matches[3])) {
                $itemIds[$itemKey]['optionId'] = $matches[3];
                $optionIds[] = $matches[3];
            }
        }

        $parts = $this->findById($partIds);
        $options = $this->hyper['helper']['moyskladVariant']->findById($optionIds);

        $result = [];

        foreach ($itemIds as $itemKey => $data) {
            try {
                $part = clone $parts[$data['id']];
            } catch (\Throwable $th) {
                continue;
            }
            
            if (isset($data['optionId'])) {
                $option = $options[$data['optionId']];

                $part->set('option', $option);
                $part->set('name', "{$part->name} {$option->name}");
                $part->list_price = clone $option->list_price;
                $part->sale_price = clone $option->sale_price;
            }

            $result[] = $part;
        }

        return $result;
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
     * Get min and max days to dilevery to the warehouse for preordered items
     *
     * @param   PartMarker $part
     *
     * @return  array
     *
     * @todo Get days for preordered parts
     *
     * @since   2.0
     */
    public function getDaysToPreorder($part)
    {
        return ['min' => 3, 'max' => 4];
    }

    /**
     * Get model.
     *
     * @return  \HyperPcModelMoysklad_Part
     *
     * @since   2.0
     */
    public function getModel()
    {
        if (!isset(self::$_model)) {
            self::$_model = ModelAdmin::getInstance('Moysklad_Part');
        }
        return self::$_model;
    }

    /**
     * Prepare data array for folders
     *
     * @param   Product $entity
     *
     * @return  array
     *
     * @throws  \InvalidArgumentException
     *
     * @since 2.0
     */
    public function prepareData(MetaEntity $entity): array
    {
        if (!($entity instanceof Product)) {
            throw new \InvalidArgumentException(
                'Argument 1 passed to ' . __METHOD__ . ' must be an instance of ' . Product::class . ', ' . get_class($entity) . ' given'
            );
        }

        return [
            'uuid'              => $entity->id,
            'type_id'           => 2,
            'name'              => $entity->name,
            'product_folder_id' => $this->_getParentFolderId($entity),
            'list_price'        => $this->_getListPriceFromMoyskladEntity($entity),
            'sale_price'        => $this->_getSalePriceFromMoyskladEntity($entity),
            'vat'               => (int) $entity->effectiveVat,
            'vendor_code'       => $entity->article,
            'options_count'     => $entity->variantsCount,
            'balance'           => (int) $entity->stock,
            'barcodes'          => json_encode($this->_getBarcodesFromMoyskladEntity($entity), JSON_PRETTY_PRINT)
        ];
    }
}
