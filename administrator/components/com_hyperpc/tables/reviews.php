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

use HYPERPC\ORM\Table\Table;

/**
 * Class HyperPcTableReviews
 *
 * @property    string  $id
 * @property    string  $rating
 * @property    string  $order_id
 * @property    string  $item_id
 * @property    string  $context
 * @property    string  $file
 * @property    string  $params
 * @property    string  $anonymous
 * @property    string  $published
 * @property    string  $comment
 * @property    string  $limitations
 * @property    string  $virtues
 * @property    string  $created_time
 * @property    string  $created_user_id
 * @property    string  $modified_time
 * @property    string  $modified_user_id
 *
 * @since       2.0
 */
class HyperPcTableReviews extends Table
{

    /**
     * HyperPcTableLeads constructor.
     *
     * @param   \JDatabaseDriver $db
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function __construct(\JDatabaseDriver $db)
    {
        parent::__construct(HP_TABLE_REVIEWS, HP_TABLE_PRIMARY_KEY, $db);
        $this->setEntity('Review');
    }

    /**
     * Method to store a node in the database table.
     *
     * @param   bool $updateNulls
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function store($updateNulls = false)
    {
        if (!$this->id) {
            $premoderation = $this->hyper['helper']['review']->isPreModeration();
            $this->published = $premoderation ? HP_STATUS_UNPUBLISHED : HP_STATUS_PUBLISHED;
        }

        return parent::store($updateNulls);
    }
}
