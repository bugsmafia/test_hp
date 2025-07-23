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

namespace HYPERPC\ORM\Entity;

/**
 * PriceRecountQueueItem class.
 *
 * @property    int         $id
 * @property    int         $part_id
 * @property    int         $option_id
 *
 * @package     HYPERPC\ORM\Entity
 *
 * @since       2.0
 */
class PriceRecountQueueItem extends Entity
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
            ->setTableType('Price_Recount_Queue');

        parent::initialize();
    }
}
