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

defined('_JEXEC') or die('Restricted access');

use Joomla\Database\ParameterType;
use HYPERPC\Joomla\Model\ModelList;

/**
 * Class HyperPcModelOrders
 *
 * @since 2.0
 */
class HyperPcModelOrders extends ModelList
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
            ->from($db->qn($this->getTable()->getTableName(), 'a'))
            ->order($db->qn('a.id') . ' DESC');

        $context = $this->hyper->getContext();

        $query
            ->where(
                ($context === 'hyperpc' ? $db->qn('a.context') . ' IS NULL OR ' : '') .
                $db->qn('a.context') . ' = :context'
            )
            ->bind(':context', $context);

        $search = $this->getState('filter.search');
        if (is_numeric($search)) {
            $search = (int) $search;
            $query
                ->where($db->qn('a.id') . ' = :id')
                ->bind(':id', $search, ParameterType::INTEGER);
        }

        $status = $this->getState('filter.status');
        if (is_numeric($status)) {
            $status = (int) $status;
            $query
                ->where($db->qn('a.status') . ' = :status')
                ->bind(':status', $status, ParameterType::INTEGER);
        }

        $payment = $this->getState('filter.payment_type');
        if (!empty($payment)) {
            $query
                ->where($db->qn('a.payment_type') . ' = :paymentType')
                ->bind(':paymentType', $payment);
        }

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
        return $this->hyper['helper']['order']->getDbo();
    }

    /**
     * Method to get a store id based on the model configuration state.
     *
     * @param   string $id
     *
     * @return  string
     *
     * @since   2.0
     */
    protected function getStoreId($id = '')
    {
        //  Compile the store id.
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.status');
        $id .= ':' . $this->getState('filter.payment_type');
        $id .= ':' . $this->getState('list.order');

        return parent::getStoreId($id);
    }

    /**
     * Method to auto-populate the model state.
     *
     * @param   string $ordering
     * @param   string $direction
     *
     * @return  void
     *
     * @since   2.0
     */
    protected function populateState($ordering = 'a.id', $direction = 'desc')
    {
        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        $status = $this->getUserStateFromRequest($this->context . '.filter.status', 'filter_status');
        $this->setState('filter.status', $status);

        $payment = $this->getUserStateFromRequest($this->context . '.filter.payment_type', 'filter_payment_type');
        $this->setState('filter.payment_type', $payment);

        $access = $this->getUserStateFromRequest($this->context . '.list.order', 'list_order');
        $this->setState('list.order', $access);

        parent::populateState($ordering, $direction);
    }
}
