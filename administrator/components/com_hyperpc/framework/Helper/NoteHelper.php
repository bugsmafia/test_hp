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
 * @author      Roman Evsyukov <roman_e@hyperpc.ru>
 */

namespace HYPERPC\Helper;

use HYPERPC\ORM\Table\Table;
use HYPERPC\ORM\Entity\Note;
use HYPERPC\Helper\Context\EntityContext;

/**
 * Class NoteHelper
 *
 * @package     HYPERPC\Helper
 *
 * @property    \HyperPcTableNotes $_table
 *
 * @since       2.0
 */
class NoteHelper extends EntityContext
{

    /**
     * Xml note form value filter.
     *
     * @param   $value
     *
     * @return  string
     *
     * @since   2.0
     */
    public static function formFilter($value)
    {
        return strip_tags($value);
    }

    /**
     * Get note.
     *
     * @param   int|string  $itemId
     * @param   string      $context
     * @param   string|int  $createdUserId
     *
     * @return  Note
     *
     * @since   2.0
     */
    public function get($itemId, $context, $createdUserId = null)
    {
        $db = $this->hyper['db'];

        $conditions = [
            $db->quoteName('a.context') . ' = ' . $db->quote($context)
        ];

        if ($createdUserId) {
            $conditions[] = $db->quoteName('a.created_user_id') . ' = ' . $db->quote($createdUserId);
        }

        return $this->findByItemId($itemId, [
            'conditions' => $conditions,
            'order'      => 'a.id DESC'
        ]);
    }

    /**
     * Reassign all notes between users
     *
     * @param  string $oldUserId
     * @param  string $userId
     *
     * @return mixed
     *
     * @since 2.0
     */
    public function reassignUser(string $oldUserId, string $userId)
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query
            ->update($db->quoteName(HP_TABLE_NOTES))
            ->set([
                $db->quoteName('created_user_id') . ' = ' . $db->quote($userId),
            ])
            ->where([
                $db->quoteName('created_user_id') . ' = ' . $db->quote($oldUserId),
            ]);

        return $db->setQuery($query)->execute();
    }

    /**
     * Initialize helper.
     *
     * @return  void
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function initialize()
    {
        $table = Table::getInstance('Notes');
        $this->setTable($table);

        parent::initialize();
    }
}
