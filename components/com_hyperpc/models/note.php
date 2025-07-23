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
 * @author      Roman Evsyukov <roman_e@hyperpc.ru>
 */

use JBZoo\Utils\Str;
use HYPERPC\Data\JSON;
use HYPERPC\ORM\Entity\Note;
use HYPERPC\Joomla\Model\ModelAdmin;

defined('_JEXEC') or die('Restricted access');

/**
 * Class HyperPcModelNote
 *
 * @property    string $_context
 *
 * @since       2.0
 */
class HyperPcModelNote extends ModelAdmin
{

    /**
     * Get context.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getContext()
    {
        return $this->_context;
    }

    /**
     * Method to get a single record.
     *
     * @param   null|string|int  $pk
     *
     * @return  Note
     *
     * @since   2.0
     */
    public function getItem($pk = null)
    {
        return $this->hyper['helper']['note']->get($pk, $this->_context);
    }

    /**
     * Method to get a table object, load it if necessary.
     *
     * @param   string $name                The table name. Optional.
     * @param   string $prefix              The class prefix. Optional.
     * @param   array $options              Configuration array for model. Optional.
     *
     * @return  \HyperPcTableOrders|JTable  A \JTable object
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getTable($name = 'Notes', $prefix = HP_TABLE_CLASS_PREFIX, $options = [])
    {
        return parent::getTable($name, $prefix, $options);
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
        parent::initialize($config);
        $this->_context = HP_OPTION . '.configuration';
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  array
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function loadFormData()
    {
        $data = new JSON(parent::loadFormData());

        if (!$data->get('item_id')) {
            $data->set('item_id', $this->hyper['input']->get('id'));
        }

        return $data->getArrayCopy();
    }

    /**
     * Setup context.
     *
     * @param   string $context
     *
     * @return  $this
     *
     * @since   2.0
     */
    public function setContext($context)
    {
        $this->_context = Str::low($context);
        return $this;
    }
}
