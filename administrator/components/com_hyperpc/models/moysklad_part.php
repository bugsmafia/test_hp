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
 * @author      Roman Evsyukov
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Table\Table;
use HYPERPC\Joomla\Model\Entity\Entity;
use HYPERPC\Joomla\Model\Entity\MoyskladPart;

/**
 * Class HyperPcModelMoysklad_Part
 *
 * @since   2.0
 */
class HyperPcModelMoysklad_Part extends HyperPcModelPosition
{

    /**
     * Get global fields for form render.
     *
     * @return  array
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public function getGlobalFields()
    {
        $fields = ['retail', 'preorder', 'moysklad_store_items', 'balance', 'options_count', 'vendor_code', 'width', 'height', 'length', 'weight'];

        return array_merge(parent::getGlobalFields(), $fields);
    }

    /**
     * Get moysklad_parts fields.
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getEntityFields()
    {
        return ['balance', 'options_count', 'vendor_code', 'preorder', 'width', 'height', 'length', 'weight', 'retail'];
    }

    /**
     * Method to get a single record.
     *
     * @param   null|int $pk
     *
     * @return  bool|object|Entity
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public function getItem($pk = null)
    {
        $pk = (!empty($pk)) ? $pk : (int) $this->getState($this->getName() . '.id');
        /** @var HyperPcTableMoysklad_Parts */
        $partsTable = $this->getTable();

        if ($pk > 0) {
            //  Attempt to load the row.
            $return = $partsTable->load($pk);
            //  Check for a table object error.
            if ($return === false && $partsTable->getError()) {
                $this->setError($partsTable->getError());
                return false;
            }
        }

        /** @var HyperPcTablePositions */
        $positionsTable = $this->getTable('Positions', HP_TABLE_CLASS_PREFIX, []);

        //  Convert to the JObject before adding other data.
        $properties = $partsTable->getProperties(1);
        $entity = $partsTable->getEntity();

        $positionsTable->load($pk);
        $positionProperties = $positionsTable->getProperties(1);

        $properties = array_merge($properties, $positionProperties);

        return new $entity($properties);
    }

    /**
     * Get table object.
     *
     * @param   string $type
     * @param   string $prefix
     * @param   array $config
     *
     * @return  Table
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public function getTable($type = 'Moysklad_Parts', $prefix = HP_TABLE_CLASS_PREFIX, $config = [])
    {
        return parent::getTable($type, $prefix, $config);
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  MoyskladPart
     *
     * @throws  Exception
     * @throws  RuntimeException
     *
     * @since   2.0
     */
    public function loadFormData()
    {
        /** @var MoyskladPart $item */
        $item = parent::loadFormData();

        if (property_exists($item, 'list_price')) {
            $item->set('list_price', (int) $item->list_price->val());
        }

        if (property_exists($item, 'sale_price')) {
            $item->set('sale_price', (int) $item->sale_price->val());
        }

        if (property_exists($item, 'balance')) {
            $item->set('balance', (int) $item->getFreeStocks());
        }

        return $item;
    }
}
