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

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Table\Table;
use HYPERPC\Joomla\Model\Entity\Entity;
use HYPERPC\Joomla\Model\Entity\MoyskladService;

/**
 * Class HyperPcModelMoysklad_Service
 *
 * @since   2.0
 */
class HyperPcModelMoysklad_Service extends HyperPcModelPosition
{
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
        /** @var \HyperPcTableMoysklad_Services */
        $servicesTable = $this->getTable();

        if ($pk > 0) {
            //  Attempt to load the row.
            $return = $servicesTable->load($pk);
            //  Check for a table object error.
            if ($return === false && $servicesTable->getError()) {
                $this->setError($servicesTable->getError());
                return false;
            }
        }

        /** @var \HyperPcTablePositions */
        $positionsTable = $this->getTable('Positions', HP_TABLE_CLASS_PREFIX, []);

        //  Convert to the JObject before adding other data.
        $properties = $servicesTable->getProperties(1);
        $entity = $servicesTable->getEntity();

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
    public function getTable($type = 'Moysklad_Services', $prefix = HP_TABLE_CLASS_PREFIX, $config = [])
    {
        return parent::getTable($type, $prefix, $config);
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  MoyskladService
     *
     * @throws  Exception
     * @throws  RuntimeException
     *
     * @since   2.0
     */
    public function loadFormData()
    {
        /** @var MoyskladService $item */
        $item = parent::loadFormData();

        if (property_exists($item, 'list_price')) {
            $item->set('list_price', (int) $item->list_price->val());
        }

        if (property_exists($item, 'sale_price')) {
            $item->set('sale_price', (int) $item->sale_price->val());
        }

        return $item;
    }
}
