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

use HYPERPC\Joomla\Model\ModelList;

defined('_JEXEC') or die('Restricted access');

/**
 * Class HyperPcModelWorkers
 *
 * @since 2.0
 */
class HyperPcModelWorkers extends ModelList
{

    /**
     * Method to get a JDatabaseQuery object for retrieving the data set from a database.
     *
     * @return  JDatabaseQuery
     *
     * @throws  \Exception
     * @throws  \RuntimeException
     *
     * @since   2.0
     */
    protected function getListQuery()
    {
        $db    = $this->hyper['db'];
        $query = $db->getQuery(true);

        $query
            ->select(['a.*'])
            ->from($db->quoteName($this->getTable()->getTableName(), 'a'));

        $search = $this->getState('filter.search');
        if (!empty($search)) {
            $search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
            $query->where($db->quoteName('a.name') . ' LIKE ' . $search);
        }

        $query->order($this->getState('list.order', 'a.id ASC'));

        return $query;
    }

    /**
     * Method to auto-populate the model state.
     *
     * @param   string $ordering
     * @param   string $direction
     *
     * @since   2.0
     */
    protected function populateState($ordering = 'a.id', $direction = 'desc')
    {
        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        $access = $this->getUserStateFromRequest($this->context . '.list.order', 'list_order');
        $this->setState('list.order', $access);
        parent::populateState($ordering, $direction);
    }

    /**
     * Method to get a store id based on the model configuration state.
     *
     * @param   string $id
     * @return  string
     *
     * @since   2.0
     */
    protected function getStoreId($id = '')
    {
        //  Compile the store id.
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('list.order');

        return parent::getStoreId($id);
    }
}
