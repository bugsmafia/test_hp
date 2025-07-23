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
 * @author      Artem Vyshnevskiy
 */

use HYPERPC\Joomla\Model\ModelList;

defined('_JEXEC') or die('Restricted access');

/**
 * Class HyperPcModelGames
 *
 * @since 2.0
 */
class HyperPcModelGames extends ModelList
{
    /**
     * ModelGames constructor.
     *
     * @param   array $config
     *
     * @throws  \Exception
     */
    public function __construct(array $config = [])
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'id', 'a.id',
                'name', 'a.name',
                'ordering', 'a.ordering'
            ];
        }

        parent::__construct($config);
    }

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
        $db    = $this->getTable()->getDbo();
        $query = $db->getQuery(true);

        $query
            ->select(['a.*'])
            ->from($db->quoteName($this->getTable()->getTableName(), 'a'));

        $search = $this->getState('filter.search');
        if (!empty($search)) {
            $search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
            $query->where($db->quoteName('a.name') . ' LIKE ' . $search);
        }

        // Add the list ordering clause
        $listOrdering  = $this->state->get('list.ordering', 'a.ordering');
        $orderDirn     = $this->state->get('list.direction', 'ASC');

        $query->order($db->escape($listOrdering) . ' ' . $db->escape($orderDirn));

        return $query;
    }
}
