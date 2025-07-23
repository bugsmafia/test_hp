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

use JBZoo\Data\JSON;
use Joomla\CMS\Table\Table;
use HYPERPC\Joomla\Model\Entity\Entity;

/**
 * Class HyperPcModelMoysklad_Product
 *
 * @since   2.0
 */
class HyperPcModelMoysklad_Product extends HyperPcModelPosition
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
        $fields = ['on_sale', 'moysklad_store_items', 'vendor_code'];

        return array_merge(parent::getGlobalFields(), $fields);
    }

    /**
     * Get moysklad_product fields.
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getEntityFields()
    {
        return ['vendor_code', 'on_sale', 'configuration'];
    }

    /**
     * Method to get a single record.
     *
     * @param   null|int $pk
     *
     * @return  bool|object|Entity
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getItem($pk = null)
    {
        $pk = (!empty($pk)) ? $pk : (int) $this->getState($this->getName() . '.id');
        /** @var \HyperPcTableMoysklad_Products */
        $productsTable = $this->getTable();

        if ($pk > 0) {
            //  Attempt to load the row.
            $return = $productsTable->load($pk);
            //  Check for a table object error.
            if ($return === false && $productsTable->getError()) {
                $this->setError($productsTable->getError());
                return false;
            }
        }

        /** @var \HyperPcTablePositions */
        $positionsTable = $this->getTable('Positions', HP_TABLE_CLASS_PREFIX, []);

        //  Convert to the JObject before adding other data.
        $properties = $productsTable->getProperties(1);
        $entity = $productsTable->getEntity();

        $positionsTable->load($pk);
        $positionProperties = $positionsTable->getProperties(1);

        $properties = array_merge($properties, $positionProperties);

        return new $entity($properties);
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  bool|\Joomla\CMS\Object\CMSObject
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function loadFormData()
    {
        $item = parent::loadFormData();

        if (property_exists($item, 'list_price')) {
            $item->set('list_price', (int) $item->list_price->val());
        }

        if (property_exists($item, 'sale_price')) {
            $item->set('sale_price', (int) $item->sale_price->val());
        }

        if (property_exists($item, 'on_sale')) {
            $item->set('on_sale', (int) $item->on_sale);
        }

        if (property_exists($item, 'configuration')) {
            $item->set('configuration', $item->configuration);
        }

        return $item;
    }

    /**
     * Method to save the form data.
     *
     * @param   array $data
     *
     * @return  bool
     *
     * @throws  \Exception
     * @throws  \RuntimeException
     * @throws  \InvalidArgumentException
     * @throws  \UnexpectedValueException
     *
     * @since   2.0
     */
    public function save($data)
    {
        $task = $this->hyper['input']->get('task');

        if (!empty($data['configuration']) && \is_array($data['configuration'])) {
            $defaults = $data['configuration']['default'];
            if (!empty($defaults) && \is_array(\reset($defaults))) { // $defaults is an array of arrays
                $defaultParts = [];
                foreach ($defaults as $productFolderParts) {
                    foreach ($productFolderParts as $partId) {
                        if (!\in_array($partId, $defaultParts)) {
                            $defaultParts[] = $partId;
                        }
                    }
                }

                $data['configuration']['default'] = $defaultParts;
            }

            if ($task !== null) { // is this check really needed?
                $params           = new JSON($data['configuration']);
                $partVariants     = (array) $params->get('options');
                $selectedVariants = (array) $params->get('option', []);

                if (\count($partVariants)) {
                    foreach ($partVariants as $variantId => $partId) {
                        $_data = [
                            'is_default' => false,
                            'part_id'    => $partId
                        ];

                        if (\in_array($variantId, $selectedVariants)) {
                            $_data['is_default'] = true;
                        }

                        $partVariants[$variantId] = $_data;
                    }
                }

                $params->set('part_options', $partVariants);
                $data['configuration'] = $params->getArrayCopy();
            }
        }

        return parent::save($data);
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
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getTable($type = 'Moysklad_Products', $prefix = HP_TABLE_CLASS_PREFIX, $config = [])
    {
        return parent::getTable($type, $prefix, $config);
    }
}
