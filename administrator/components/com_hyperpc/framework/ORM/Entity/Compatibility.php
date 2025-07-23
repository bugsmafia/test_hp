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

namespace HYPERPC\ORM\Entity;

use HYPERPC\Data\JSON;
use Joomla\CMS\Date\Date;
use HYPERPC\Helper\CompatibilityHelper;

/**
 * Compatibility class.
 *
 * @property    int         $id
 * @property    string      $type
 * @property    string      $name
 * @property    string      $alias
 * @property    string      $description
 * @property    bool        $published
 * @property    JSON        $params
 * @property    int         $created_user_id
 * @property    Date        $created_time
 * @property    int         $modified_user_id
 * @property    int         $modified_time
 *
 * @property    CompatibilityHelper  $_helper
 *
 * @method      CompatibilityHelper  getHelper()
 *
 * @package     HYPERPC\ORM\Entity
 *
 * @since       2.0
 */
class Compatibility extends Entity
{

    protected $context = 'legacy';

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
            ->setTableType('Compatibilities');

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
        return $this->hyper['route']->build([
            'layout' => 'edit',
            'id'     => $this->id,
            'view'   => 'compatibility'
        ]);
    }

    /**
     * Get left group id
     *
     * @return  int
     *
     * @since   2.0
     */
    public function getLeftGroupId()
    {
        return $this->params->find($this->context . '.left.group_id', 0, 'int');
    }

    /**
     * Get right group id
     *
     * @return  int
     *
     * @since   2.0
     */
    public function getRightGroupId()
    {
        return $this->params->find($this->context . '.right.group_id', 0, 'int');
    }

    /**
     * Get left field id
     *
     * @return  int
     *
     * @since   2.0
     */
    public function getLeftFieldId()
    {
        return $this->params->find($this->context . '.left.field_id', 0, 'int');
    }

    /**
     * Get right field id
     *
     * @return  int
     *
     * @since   2.0
     */
    public function getRightFieldId()
    {
        return $this->params->find($this->context . '.right.field_id', 0, 'int');
    }

    /**
     * Set context
     *
     * @param   string $context
     *
     * @since   2.0
     */
    public function setContext($context)
    {
        $this->context = $context;
    }
}
