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

use Joomla\CMS\Table\Table;
use HYPERPC\Helper\UsercodeHelper;
use HYPERPC\Joomla\Model\ModelForm;
use HYPERPC\Joomla\Model\Entity\Entity;
use HYPERPC\Joomla\Model\Entity\Interfaces\PartMarker;

defined('_JEXEC') or die('Restricted access');

/**
 * Class HyperPcModelUser_Code
 *
 * @property UsercodeHelper   $_helper
 * @method   UsercodeHelper   getHelper()
 *
 * @since    2.0
 */
class HyperPcModelUser_Code extends ModelForm
{

    /**
     * Getting the form from the model.
     *
     * @param   array $data
     * @param   bool $loadData
     *
     * @return  bool|\Joomla\CMS\Form\Form
     *
     * @since   2.0
     */
    public function getForm($data = [], $loadData = true)
    {
        $form = $this->loadForm(HP_OPTION . '.user_code', 'user_code', [
            'control'   => 'jform',
            'load_data' => $loadData
        ]);

        if ($form === null) {
            return false;
        }

        return $form;
    }

    /**
     * Save action.
     *
     * @param   array $data
     *
     * @return  bool
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public function save($data)
    {
        $table  = $this->getTable();
        $key    = $table->getKeyName();
        $pk     = (!empty($data[$key])) ? $data[$key] : (int) $this->getState($this->getName() . '.id');

        //  Allow an exception to be thrown.
        try {
            //  Load the row if saving an existing record.
            if ($pk > 0) {
                $table->load($pk);
            }

            //  Bind the data.
            if (!$table->bind($data)) {
                $this->setError($table->getError());
                return false;
            }

            //  Check the data.
            if (!$table->check()) {
                $this->setError($table->getError());
                return false;
            }

            //  Store the data.
            if (!$table->store()) {
                $this->setError($table->getError());
                return false;
            }

        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * Initialize model hook method.
     *
     * @param   array $config
     *
     * @return  void
     *
     * @since   2.0
     */
    public function initialize(array $config)
    {
        $this->_helper = $this->hyper['helper']['usercode'];
    }

    /**
     * Get category object by request id.
     *
     * @param   int $id
     *
     * @return  PartMarker|Entity
     *
     * @throws  \RuntimeException
     *
     * @since   2.0
     */
    public function getItem($id = null)
    {
        if ($id === null) {
            $id = $this->hyper['input']->get('id', 1);
        }

        return $this->_helper->findById($id, ['a.*']);
    }

    /**
     * Get table object.
     *
     * @param   string  $name
     * @param   string  $prefix
     * @param   array   $options
     *
     * @return  Table|HyperPcTableUser_Codes|JTable
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getTable($name = 'User_Codes', $prefix = HP_TABLE_CLASS_PREFIX, $options = [])
    {
        return parent::getTable($name, $prefix, $options);
    }
}
