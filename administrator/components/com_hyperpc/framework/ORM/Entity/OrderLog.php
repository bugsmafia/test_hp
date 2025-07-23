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
use Joomla\CMS\Date\Date;

/**
 * OrderLog class.
 *
 * @property-read   int     $id
 * @property-read   int     $order_id
 * @property-read   string  $type
 * @property-read   JSON    $content
 * @property-read   Date    $created_time
 *
 * @package         HYPERPC\ORM\Entity
 *
 * @since           2.0
 */
class OrderLog extends Entity
{

    /**
     * Field list of json type.
     *
     * @var     array
     *
     * @since   2.0
     */
    protected $_fieldJsonType = [
        'content'
    ];

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
            ->setTableType('Order_Logs');

        parent::initialize();
    }
}
