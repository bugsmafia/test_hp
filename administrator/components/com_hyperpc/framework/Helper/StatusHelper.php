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

namespace HYPERPC\Helper;

use HYPERPC\ORM\Table\Table;
use HYPERPC\Joomla\Model\Entity\Status;
use HYPERPC\Helper\Context\EntityContext;

/**
 * Class StatusHelper
 *
 * @package HYPERPC\Helper
 *
 * @since   2.0
 */
class StatusHelper extends EntityContext
{

    /**
     * Hold status actual list.
     *
     * @var     array
     *
     * @since   2.0
     */
    protected static $_statuses = [];

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
        $table = Table::getInstance('Statuses');
        $this->setTable($table);

        parent::initialize();

        $db = $this->hyper['db'];
        self::$_statuses = $this->findAll([
            'conditions' => [$db->quoteName('a.published') . ' = ' . HP_STATUS_PUBLISHED],
            'order'      => 'a.pipeline_id ASC'
        ]);
    }

    /**
     * Get actual worker list.
     *
     * @return  Status[]
     *
     * @since   2.0
     */
    public function getStatusList()
    {
        return self::$_statuses;
    }

    /**
     * Find statuses by moysklad UUID
     *
     * @param   string $uuid
     *
     * @return  Status[]
     *
     * @since   2.0
     */
    public function findByUuid($uuid)
    {
        $statuses = [];
        foreach ($this->getStatusList() as $id => $_status) {
            if ($_status->params->get('moysklad_uuid') === $uuid) {
                $statuses[$id] = clone $_status;
            }
        }

        return $statuses;
    }
}
