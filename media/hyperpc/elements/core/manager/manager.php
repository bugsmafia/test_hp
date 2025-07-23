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
 * @link        https://github.com/HYPER-PC/HYPERPC".
 *
 * @author      Sergey Kalistratov © <kalistratov.s.m@gmail.com>
 */

defined('_JEXEC') or die('Restricted access');

use HYPERPC\Data\JSON;
use Joomla\Input\Cookie;
use HYPERPC\ORM\Table\Table;
use HYPERPC\Elements\Element;
use HYPERPC\Helper\WorkerHelper;
use HYPERPC\Joomla\Model\Entity\Worker;

/**
 * Class ElementCoreManager
 *
 * @since   2.0
 */
class ElementCoreManager extends Element
{

    const COOKIE_VALUE_HOLDER = 'mng_val';

    /**
     * Get saved cookie value.
     *
     * @return  mixed
     *
     * @since   2.0
     */
    public function getCookieValue()
    {
        /** @var Cookie $cookie */
        $cookie = $this->hyper['input']->cookie;
        return $cookie->get(self::COOKIE_VALUE_HOLDER);
    }

    /**
     * Get manager list.
     *
     * @return  mixed
     *
     * @since   2.0
     */
    public function getList()
    {
        /** @var \JDatabaseDriverMysqli $db */
        $db = $this->hyper['db'];
        return $this->hyper['helper']['worker']->findAll([
            'conditions' => [$db->qn('a.published') . ' = ' . $db->q(HP_STATUS_PUBLISHED)]
        ]);
    }

    /**
     * Get list option for generic option list.
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getListOptions()
    {
        $options = [];

        /**@var Worker $worker */
        foreach ($this->getList() as $i => $worker) {
            $options[$i]['value'] = $worker->id;
            $options[$i]['text']  = $worker->name;
        }

        return $options;
    }

    /**
     * Callback on save item.
     *
     * @param   Table   $table
     * @param   bool    $isNew
     *
     * @return  void
     *
     * @throws \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function onBeforeSaveItem(Table &$table, $isNew)
    {
        $elData = new JSON($table->elements);
        $value  = $elData->find($this->_type . '.value');

        /** @var WorkerHelper $wHelper */
        $workerHelper = $this->hyper['helper']['worker'];

        if ($isNew) {
            if ($value === null) {
                $worker = $workerHelper->getDefaultWorker();
                if ($worker->id) {
                    $table->worker_id = $worker->id;
                }
            } else {
                $table->worker_id = $workerHelper->getCurrentValue($value);
            }
        }

        /** @var Cookie $cookie */
        $cookie = $this->hyper['input']->cookie;
        if (!$this->getCookieValue()) {
            $cookie->set(self::COOKIE_VALUE_HOLDER, $value, time() + 60 * 60 * 24 * 30);
        }
    }
}
