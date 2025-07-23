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
 * @author      Roman Evsyukov
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Table\Table;
use HYPERPC\Joomla\Model\ModelList;

/**
 * Class HyperPcModelPositions
 *
 * @since 2.0
 */
class HyperPcModelPositions extends ModelList
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
        $db             = $this->_db;
        $query          = $db->getQuery(true);
        $folder_id      = $this->hyper['input']->get('folder_id', 1, 'int');

        $query
            ->select(['a.*'])
            ->from($db->quoteName($this->getTable()->getTableName(), 'a'))
            ->where([
                $db->quoteName('a.product_folder_id') . ' = ' . $db->quote($folder_id),
            ]);

        $search = $this->getState('filter.search');
        if (!empty($search)) {
            $search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
            $query->where($db->quoteName('a.name') . ' LIKE ' . $search);
        }

        $published = $this->getState('filter.published');
        if (is_numeric($published)) {
            $query->where($db->quoteName('a.state') . ' = ' . (int) $published);
        } elseif ($published === '') {
            $defaultStatuses = [HP_STATUS_PUBLISHED, HP_STATUS_UNPUBLISHED];
            $query->where($db->quoteName('a.state') . ' IN (' . implode(', ', $defaultStatuses) . ')');
        }

        $query->order($this->getState('list.order', 'a.name ASC'));

        return $query;
    }

    /**
     * Method to get a table object, load it if necessary.
     *
     * @param   string $type
     * @param   string $prefix
     * @param   array $config
     *
     * @return  Table|JTable
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public function getTable($type = 'Positions', $prefix = HP_TABLE_CLASS_PREFIX, $config = [])
    {
        return parent::getTable($type, $prefix, $config);
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

        $search = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published');
        $this->setState('filter.published', $search);

        parent::populateState($ordering, $direction);
    }
}
