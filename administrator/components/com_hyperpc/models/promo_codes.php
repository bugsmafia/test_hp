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
 * @author      Sergey Kalistratov <kalistratov.s.m@gmail.com>
 */

use HYPERPC\Joomla\Model\ModelList;

defined('_JEXEC') or die('Restricted access');

/**
 * Class HyperPcModelPromo_Codes
 *
 * @since 2.0
 */
class HyperPcModelPromo_Codes extends ModelList
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
            $query->where($db->quoteName('a.code') . ' LIKE ' . $search);
        }

        $query->order($this->getState('list.order', 'a.id ASC'));

        return $query;
    }

    /**
     * Method to get a table object, load it if necessary.
     *
     * @param   string $type
     * @param   string $prefix
     * @param   array $config
     * @return  \Joomla\CMS\Table\Table|\JTable
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getTable($type = 'Promo_codes', $prefix = HP_TABLE_CLASS_PREFIX, $config = [])
    {
        return parent::getTable($type, $prefix, $config);
    }
}
