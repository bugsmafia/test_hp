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

namespace HYPERPC\Helper;

use HYPERPC\Joomla\Model\Entity\Field;

/**
 * Class FieldsHelper
 *
 * @package HYPERPC\Helper
 *
 * @since   2.0
 */
class FieldsHelper extends AppHelper
{

    /**
     * Get fields group by context.
     *
     * @param   string  $context
     * @param   int     $state
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getGroups($context, $state = HP_STATUS_PUBLISHED)
    {
        $db    = $this->hyper['db'];
        $query = $db->getQuery(true);

        $query
            ->select([
                'a.id',
                'a.title'
            ])
            ->from($db->qn('#__fields_groups', 'a'))
            ->where([
                $db->qn('a.context') . ' = ' . $db->q($context),
                $db->qn('a.state')   . ' = ' . $db->q($state),
            ]);

        return (array) $db->setQuery($query)->loadObjectList('id');
    }

    /**
     * Get group allowed fields by context.
     *
     * @param   int     $groupId
     * @param   string  $context
     * @param   string  $order
     * @param   array   $allowedFilters
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getGroupFields($groupId, $context = 'part', $order = 'ASC', $allowedFilters = [])
    {
        $db = $this->hyper['db'];

        $conditions = [
            $db->qn('c.category_id') . ' = '  . $db->q($groupId),
            $db->qn('f.type')        . ' != ' . $db->q('hpseparator'),
            $db->qn('f.state')       . ' = '  . $db->q(HP_STATUS_PUBLISHED),
            $db->qn('f.context')     . ' = '  . $db->q(HP_OPTION . '.' . $context)
        ];

        $query = $db
            ->getQuery(true)
            ->select([
                'GROUP_CONCAT(c.category_id SEPARATOR ",") as category_ids',
                'f.*'
            ])
            ->from(
                $db->qn('#__fields_categories', 'c')
            )
            ->join(
                'LEFT', $db->qn('#__fields', 'f') . ' ON c.field_id = f.id'
            )
            ->where($conditions)
            ->group($db->quoteName('f.id'))
            ->order($db->qn('f.ordering') . ' ' . $order);

        $allQueryFields = $db
            ->getQuery(true)
            ->select([
                'a.*', 'b.*'
            ])
            ->from(
                $db->qn('#__fields', 'a')
            )
            ->join(
                'LEFT', $db->qn('#__fields_categories', 'b') . ' ON b.field_id = a.id'
            )
            ->where([
                $db->qn('b.category_id') . ' IS NULL',
                $db->qn('a.type')        . ' != ' . $db->q('hpseparator'),
                $db->qn('a.state')       . ' = '  . $db->q(HP_STATUS_PUBLISHED),
                $db->qn('a.context')     . ' = '  . $db->q(HP_OPTION . '.' . $context)
            ])
            ->order($db->qn('a.ordering') . ' ' . $order);

        if (!empty($allowedFilters)) {
            $query->where($db->qn('f.id') . ' IN (' . implode(', ', $allowedFilters) . ')');
            $allQueryFields->where($db->qn('a.id') . ' IN (' . implode(', ', $allowedFilters) . ')');
        }

        return $this->hyper['helper']['object']->createList(
            array_merge(
                $db->setQuery($allQueryFields)->loadObjectList(),
                $db->setQuery($query)->loadObjectList()
            ),
            Field::class,
            'id'
        );
    }

    /**
     * Change fields ordering
     *
     * @param   array   $fields
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function changeOrdering($fields)
    {
        $db = $this->hyper['db'];

        if (!count($fields)) {
            return false;
        }

        $query = $db->getQuery(true)
            ->select([
                'a.id, a.ordering'
            ])
            ->from(
                $db->qn(JOOMLA_TABLE_FIELDS, 'a')
            )
            ->where($db->qn('a.id') . ' IN (' . implode(', ', $fields) . ')');

        $list = $db->setQuery($query)->loadObjectList();

        if (count($list) > 0) {
            foreach ($list as $field) {
                $field->ordering = array_search($field->id, $fields);

                $query = $db->getQuery(true)
                    ->update($db->qn(JOOMLA_TABLE_FIELDS))
                    ->set($db->qn('ordering') . ' = ' . $db->q(array_search($field->id, $fields)))
                    ->where($db->qn('id') . ' = ' . $field->id);

                $db->setQuery($query);
                $db->execute();
            }

            return true;
        }

        return false;
    }

    /**
     * Get fields values by field ids.
     *
     * @param   array $fields
     *
     * @return  mixed
     *
     * @since   2.0
     */
    public function getFieldsValues($fields = [])
    {
        $data = [];
        $db   = $this->hyper['db'];

        if (!count($fields)) {
            return false;
        }

        $query = $db->getQuery(true)
            ->select([
                'f.name, fv.value'
            ])
            ->from(
                $db->qn(JOOMLA_TABLE_FIELDS, 'f')
            )
            ->join(
                'LEFT',
                $db->qn(JOOMLA_TABLE_FIELDS_VALUES, 'fv') . ' ON fv.field_id = f.id'
            )
            ->where($db->qn('f.id') . ' IN (' . implode(', ', $fields) . ')');

        $list = $db->setQuery($query)->loadObjectList();

        foreach ($list as $item) {
            if (!isset($data[$item->name])) {
                $data[$item->name] = [];
            }

            if (!in_array($item->value, $data[$item->name])) {
                $data[$item->name][] = $item->value;
            }
        }

        return $data;
    }

    /**
     * Get field list by ids.
     *
     * @param   array   $ids
     * @param   bool    $oderById
     * @param   string  $context
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getFieldsById(array $ids = [], $oderById = false, $context = '')
    {
        $db = $this->hyper['db'];

        if (!count($ids)) {
            return [];
        }

        $conditions[] = $db->qn('f.id') . ' IN (' . implode(', ', $ids) . ')';

        if ($context) {
            $conditions[] = $db->qn('f.context')     . ' = '  . $db->q(HP_OPTION . '.' . $context);
        }

        $query = $db->getQuery(true)
            ->select([
                'f.*',
                'GROUP_CONCAT(c.category_id SEPARATOR ",") as category_ids'
            ])
            ->from(
                $db->qn(JOOMLA_TABLE_FIELDS, 'f')
            )
            ->join(
                'LEFT',
                $db->qn(JOOMLA_TABLE_FIELDS_CATEGORIES, 'c') . ' ON c.field_id = f.id'
            )
            ->where($conditions)
            ->group($db->quoteName('f.id'));

        if ($oderById === true) {
            $query->order('FIELD (id, ' . implode(', ', $ids) . ')');
        }

        $_list = $db->setQuery($query)->loadAssocList('id');

        $class = Field::class;
        $list  = [];
        foreach ($_list as $id => $item) {
            $list[$id] = new $class($item);
        }

        return (array) $list;
    }

    /**
     * Get field by id.
     *
     * @param   integer $id
     * @param   string $context
     *
     * @return  Field|null
     *
     * @throws \Exception
     *
     * @since   2.0
     */
    public function getFieldById($id, $context = '')
    {
        $db = $this->hyper['db'];

        if (empty($id)) {
            return null;
        }

        $conditions[] = $db->qn('f.id') . ' = ' . $db->q($id);

        if ($context) {
            $conditions[] = $db->qn('f.context')     . ' = '  . $db->q(HP_OPTION . '.' . $context);
        }

        $query = $db->getQuery(true)
            ->select([
                'f.*',
                'GROUP_CONCAT(c.category_id SEPARATOR ",") as category_ids'
            ])
            ->from(
                $db->qn(JOOMLA_TABLE_FIELDS, 'f')
            )
            ->join(
                'LEFT',
                $db->qn(JOOMLA_TABLE_FIELDS_CATEGORIES, 'c') . ' ON c.field_id = f.id'
            )
            ->where($conditions)
            ->group($db->quoteName('f.id'));

        $field = new Field((array) $db->setQuery($query)->loadObject());

        return $field;
    }
}
