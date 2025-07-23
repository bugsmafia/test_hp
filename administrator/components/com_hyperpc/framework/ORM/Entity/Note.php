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

namespace HYPERPC\ORM\Entity;

use Joomla\CMS\Date\Date;
use HYPERPC\Helper\ReviewHelper;

/**
 * Review class.
 *
 * @property    int         $id
 * @property    int         $item_id
 * @property    string      $context
 * @property    string      $note
 * @property    Date        $created_time
 * @property    int         $created_user_id
 * @property    int         $modified_time
 * @property    int         $modified_user_id
 *
 * @property    ReviewHelper  $_helper
 *
 * @method      ReviewHelper  getHelper()
 *
 * @package     HYPERPC\ORM\Entity
 *
 * @since       2.0
 */
class Note extends Entity
{

    /**
     * Get admin (backend) edit url.
     *
     * @return  null
     *
     * @since   2.0
     */
    public function getAdminEditUrl()
    {
        return null;
    }

    /**
     * Initialize hook method.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function initialize()
    {
        $this
            ->setTablePrefix()
            ->setTableType('Notes');

        parent::initialize();
    }
}
