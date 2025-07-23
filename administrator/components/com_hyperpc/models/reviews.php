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

use JBZoo\Utils\Filter;
use HYPERPC\Joomla\Model\ModelList;

defined('_JEXEC') or die('Restricted access');

/**
 * Class HyperPcModelReviews
 *
 * @since 2.0
 */
class HyperPcModelReviews extends ModelList
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
        $db    = $this->getTable()->getDbo();
        $query = $db->getQuery(true);

        $query
            ->select(['a.*'])
            ->from($db->quoteName($this->getTable()->getTableName(), 'a'))
            ->order($db->quoteName('a.id') . ' DESC');

        $search = $this->getState('filter.search');
        if (!empty($search)) {
            $conditions[] = $db->quoteName('a.id') . ' = ' . $db->quote(Filter::int($search));
        }

        $conditions[] = $db->quoteName('a.context') . ' = ' . $db->quote('com_hyperpc.position');

        $query->where($conditions);

        return $query;
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
        $id .= ':' . $this->getState('filter.context');
        $id .= ':' . $this->getState('list.order');

        return parent::getStoreId($id);
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
        $context = $this->getUserStateFromRequest($this->context . '.filter.context', 'filter_context');
        $this->setState('filter.context', $context);

        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        $access = $this->getUserStateFromRequest($this->context . '.list.order', 'list_order');
        $this->setState('list.order', $access);
        parent::populateState($ordering, $direction);
    }
}
