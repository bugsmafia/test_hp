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
 * @author      Artem Vyshnevskiy
 */

namespace HYPERPC\Helper\Traits;

use HYPERPC\ORM\Table\Table;
use Joomla\Registry\Registry;
use Joomla\Database\DatabaseQuery;
use Joomla\CMS\Table\Table as JTable;

/**
 * Trait EntitySubtype
 *
 * @package HYPERPC\Helper\Traits
 */
trait EntitySubtype
{
    /**
     * Get table name.
     *
     * @return  JTable
     */
    abstract public function getTable(): JTable;

    /**
     * Get supertype table name.
     *
     * @return  string
     */
    abstract protected function _getSupertypeTableName(): string;

    /**
     * Delete record from database.
     *
     * @param   array  $options
     *
     * @return  bool
     *
     * @throws  \RuntimeException
     */
    public function delete(array $options = []): bool
    {
        $options = new Registry(array_replace([
            'conditions' => []
        ], $options));

        $superTable = $this->_getSupertypeTable();

        $db = $superTable->getDbo();

        $query = $db->getQuery(true);

        if ($options->get('id')) {
            $query
                ->delete($db->qn($superTable->getTableName()))
                ->where($db->qn('id') . ' = '. $db->q($options->get('id')));
        } else {
            $query
                ->delete($db->qn($superTable->getTableName(), 'a'))
                ->join('LEFT', $db->qn($this->getTable()->getTableName(), 'b'));

            $conditions = $options->get('conditions');
            if (count($conditions)) {
                foreach ($conditions as $condition) {
                    $query->where($condition);
                }
            } else {
                $query->where('0');
            }
        }

        $db->setQuery($query);

        return $db->execute();
    }

    /**
     * Get query
     *
     * @return  DatabaseQuery
     */
    protected function _getTraitQuery(): DatabaseQuery
    {
        $table = $this->getTable();
        $supertypeTable = $this->_getSupertypeTable();

        $db = $table->getDbo();

        $tableFileds = $table->getFields();
        unset($tableFileds[HP_TABLE_PRIMARY_KEY]);

        $query = $db
            ->getQuery(true)
            ->select('t.*')
            ->select(array_keys($tableFileds))
            ->from($db->qn($table->getTableName(), 'st'))
            ->join(
                'INNER',
                $db->qn($supertypeTable->getTableName(), 't'),
                'st.id = t.id'
            );

        return $query;
    }

    /**
     * Get query for from condition
     *
     * @return  string
     */
    protected function _getFromQuery()
    {
        return '(' . $this->_getTraitQuery() . ') AS a';
    }

    /**
     * Get supertype table.
     *
     * @return  Table $table
     *
     * @throws  \Exception
     */
    protected function _getSupertypeTable(): Table
    {
        $tableName = $this->_getSupertypeTableName();
        $table = Table::getInstance($tableName);
        if (!($table instanceof Table)) {
            throw new \Exception($tableName . ' table not found');
        }

        return $table;
    }
}
