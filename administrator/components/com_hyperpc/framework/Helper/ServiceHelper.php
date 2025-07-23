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

namespace HYPERPC\Helper;

use HYPERPC\ORM\Table\Table;
use HYPERPC\Helper\Context\EntityContext;

defined('_JEXEC') or die('Restricted access');

/**
 * Class ServiceHelper
 *
 * @package     HYPERPC\Helper
 *
 * @since       2.0
 */
class ServiceHelper extends EntityContext
{

    /**
     * Container constructor.
     *
     * @param   array $values
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function __construct(array $values = [])
    {
        $table = Table::getInstance('Services');
        $this->setTable($table);
        parent::__construct($values);
    }

    /**
     * Get service group.
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getServiceGroups()
    {
        $groupIds = (array) $this->hyper['params']->get('product_service_groups');

        if (!count($groupIds)) {
            return $groupIds;
        }

        return $this->hyper['helper']['group']->findById($groupIds);
    }

    /**
     * Check service group.
     *
     * @param   string $groupId
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function isServiceGroup($groupId)
    {
        return in_array((string) $groupId, (array) $this->hyper['params']->get('product_service_groups'));
    }
}
