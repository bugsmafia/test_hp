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

use HYPERPC\Data\JSON;

/**
 * Class Entity
 *
 * @property    int     $id
 * @property    JSON    $params
 *
 * @since       2.0
 */
class Plugin extends Entity
{

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
            ->setTableType('Extension')
            ->setTablePrefix('JTable');

        parent::initialize();
    }

    /**
     * Get admin (backend) edit url.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getAdminEditUrl()
    {
        return '';
    }
}