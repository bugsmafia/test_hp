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
 * @author      Sergey Kalistratov <kalistratov.s.m@gmail.com>
 */

use HYPERPC\Joomla\Model\ModelForm;
use HYPERPC\Joomla\Model\Entity\SaveConfiguration;

defined('_JEXEC') or die('Restricted access');

/**
 * Class HyperPcModelSaved_Configuration
 *
 * @since 2.0
 */
class HyperPcModelSaved_Configuration extends ModelForm
{

    /**
     * Get save configuration by id.
     *
     * @param   null|int $id
     * @return  bool|SaveConfiguration
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public function getItem($id = null)
    {
        $id    = (!empty($id)) ? $id : (int) $this->getState($this->getName() . '.id');
        $table = $this->getTable();

        if ($id > 0) {
            // Attempt to load the row.
            $return = $table->load($id);

            // Check for a table object error.
            if ($return === false && $table->getError()) {
                $this->setError($table->getError());
                return false;
            }
        }

        // Convert to the JObject before adding other data.
        $properties = $table->getProperties(1);

        return new SaveConfiguration($properties);
    }

    /**
     * Getting the form from the model.
     *
     * @param   array $data
     * @param   bool $loadData
     * @return  bool
     *
     * @since   2.0
     */
    public function getForm($data = [], $loadData = true)
    {
        return false;
    }

    /**
     * Method to get a table object, load it if necessary.
     *
     * @param   string $type
     * @param   string $prefix
     * @param   array $config
     * @return  HyperPcTableSaved_Configurations|JTable
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public function getTable($type = 'Saved_Configurations', $prefix = HP_TABLE_CLASS_PREFIX, $config = [])
    {
        return parent::getTable($type, $prefix, $config);
    }
}
