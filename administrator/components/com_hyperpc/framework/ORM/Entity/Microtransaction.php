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

use HYPERPC\Money\Type\Money;

/**
 * Microtransaction class.
 *
 * @property    int     $id
 * @property    string  $purchase_key
 * @property    Money   $total
 * @property    string  $description
 * @property    string  $player
 * @property    bool    $paid
 * @property    int     $module_id
 * @property    bool    $activated
 * @property    int     $created_user_id
 * @property    string  $created_time
 *
 * @package     HYPERPC\ORM\Entity
 *
 * @since       2.0
 */
class Microtransaction extends Entity
{

    /**
     * Custom field types.
     *
     * @var     array
     *
     * @since   2.0
     */
    protected $_fieldTypes = [
        'total' => 'money'
    ];

    /**
     * Field list of boolean type.
     *
     * @var     array
     *
     * @since   2.0
     */
    protected $_fieldBooleanType = [
        'paid',
        'activated'
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
            ->setTableType('Microtransactions');

        parent::initialize();
    }
}
