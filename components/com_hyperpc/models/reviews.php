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
            ->from($db->quoteName($this->getTable()->getTableName(), 'a'));

        $search = $this->getState('filter.search');
        if (!empty($search)) {
            $search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
            $query->where($db->quoteName('a.id') . ' LIKE ' . $search);
        }

        $query->order($this->getState('list.order', 'a.created_time DESC'));

        $offset = $this->hyper['input']->get('limitstart', 0, 'uint');
        $this->setState('list.start', $offset);

        $user = $this->hyper['user'];
        $query->where($db->quoteName('a.created_user_id') . ' = ' . $user->id);

        return $query;
    }

    /**
     * Get data base object.
     *
     * @return  JDatabaseDriver
     *
     * @since   2.0
     */
    public function getDbo()
    {
        return $this->hyper['helper']['review']->getDbo();
    }
}
