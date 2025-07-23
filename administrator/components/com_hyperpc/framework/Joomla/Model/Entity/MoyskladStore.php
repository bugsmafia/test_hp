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

namespace HYPERPC\Joomla\Model\Entity;

use Exception;

/**
 * Class MoyskladStore
 *
 * @package HYPERPC\Joomla\Model\Entity
 *
 * @since   2.0
 */
class MoyskladStore extends Entity
{

    /**
     * Store alias.
     *
     * @var     string
     *
     * @since   2.0
     */
    public $alias;

    /**
     * Primary key.
     *
     * @var     int
     *
     * @since   2.0
     */
    public $id;

    /**
     * Store level.
     *
     * @var     int
     *
     * @since   2.0
     */
    public $level;

    /**
     * Tree left point.
     *
     * @var     int
     *
     * @since   2.0
     */
    public $lft;

    /**
     * Store params.
     *
     * @var     JSON
     *
     * @since   2.0
     */
    public $params;

    /**
     * Parent store id.
     *
     * @var     int
     *
     * @since   2.0
     */
    public $parent_id;

    /**
     * Tree path.
     *
     * @var     string
     *
     * @since   2.0
     */
    public $path;

    /**
     * Published status.
     *
     * @var     int
     *
     * @since   2.0
     */
    public int $published = 0;

    /**
     * Geo id status.
     *
     * @var     int|null
     *
     * @since   2.0
     */
    public $geoid = 0;

    /**
     * Tree right point.
     *
     * @var     int
     *
     * @since   2.0
     */
    public $rgt;

    /**
     * Store name.
     *
     * @var     string
     *
     * @since   2.0
     */
    public $name;

    /**
     * Store uuid.
     *
     * @var     string
     *
     * @since   2.0
     */
    public $uuid;

    /**
     * Get edit store link url.
     *
     * @return  string
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public function getEditUrl()
    {
        return $this->hyper['route']->build([
            'id'     => $this->id,
            'view'   => 'moysklad_store',
            'layout' => 'edit'
        ]);
    }

    /**
     * Get store children.
     *
     * @param   string  $order
     * @param   int     $published
     *
     * @return  array
     *
     * @throws  \RuntimeException
     *
     * @since   2.0
     */
    public function getSubstores($published = 1, $order = 'a.id ASC')
    {
        $db = $this->hyper['db'];

        $conditions = [
            $db->quoteName('parent_id') . ' = ' . $db->quote($this->id)
        ];

        if ($published === 1) {
            $conditions[] = $db->quoteName('a.published') . ' = ' . $db->quote(HP_STATUS_PUBLISHED);
        }

        return $this->hyper['helper']['moyskladStore']->findAll([
            'order'      => $order,
            'conditions' => $conditions
        ]);
    }

    /**
     * Get site view folder url.
     *
     * @param   array $query
     *
     * @return  string
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public function getViewUrl(array $query = [])
    {
        return $this->hyper['helper']['route']->url(array_replace($query, [
            'view' => 'moysklad_store',
            'id'   => $this->id
        ]));
    }

    /**
     * Check is root store.
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function isRoot()
    {
        return ($this->alias === 'root');
    }

    /**
     * Check folder is archived
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function isArchived()
    {
        return (int) $this->published === HP_STATUS_ARCHIVED;
    }

    /**
     * Fields of boolean data.
     *
     * @return  array
     *
     * @since   2.0
     */
    protected function _getFieldBoolean()
    {
        return [];
    }
}
