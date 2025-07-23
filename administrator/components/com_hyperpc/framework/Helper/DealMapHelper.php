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
 * @author      Roman Evsyukov
 */

namespace HYPERPC\Helper;

use HYPERPC\ORM\Table\Table;
use HYPERPC\ORM\Entity\DealMapItem;
use HYPERPC\Helper\Context\EntityContext;

/**
 * Class DealMapHelper
 *
 * @package     HYPERPC\Helper
 *
 * @property    \HyperPcTableDeal_Map $_table
 *
 * @since       2.0
 */
class DealMapHelper extends EntityContext
{
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
        $table = Table::getInstance('Deal_Map');
        $this->setTable($table);

        parent::initialize();
    }

    /**
     * Add row by crm lead id
     *
     * @param   int $crmLeadId
     *
     * @return  bool
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function addCrmLeadId(int $crmLeadId): bool
    {
        $dealMapItem = $this->findByCrmLeadId($crmLeadId, ['new' => true]);
        if (!$dealMapItem->id) {
            $dealMapItem->crm_lead_id = $crmLeadId;

            return Table::getInstance('Deal_Map')->save($dealMapItem);
        }

        return false;
    }

    /**
     * Add row by moysklad order id
     *
     * @param   string $moyskladOrderUuid
     *
     * @return  bool
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function addMoyskladOrderUuid(string $moyskladOrderUuid): bool
    {
        $dealMapItem = $this->findByMoyskladOrderUuid($moyskladOrderUuid, ['new' => true]);
        if (!$dealMapItem->id) {
            $dealMapItem->moysklad_order_uuid = $moyskladOrderUuid;

            return Table::getInstance('Deal_Map')->save($dealMapItem);
        }

        return false;
    }

    /**
     * Add row by order id
     *
     * @param   int $orderId
     *
     * @return  bool
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function addOrderId(int $orderId): bool
    {
        $dealMapItem = $this->findByOrderId($orderId, ['new' => true]);
        if (!$dealMapItem->id) {
            $dealMapItem->order_id = $orderId;

            return Table::getInstance('Deal_Map')->save($dealMapItem);
        }

        return false;
    }

    /**
     * Bind crm lead to moysklad order
     *
     * @param   int $crmLeadId
     * @param   string $moyskladOrderUuid
     *
     * @return  bool
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function bindCrmLeadToMoyskladOrder(int $crmLeadId, string $moyskladOrderUuid): bool
    {
        $dealMapItems = $this->findAll(['conditions' => [
            $this->_db->qn('a.crm_lead_id') . ' = ' . $this->_db->q($crmLeadId) . ' OR ' . $this->_db->qn('a.moysklad_order_uuid') . ' = ' . $this->_db->q($moyskladOrderUuid)
        ]]);

        if (count($dealMapItems) === 2) {
            return $this->_mergeDealMapItems($dealMapItems);
        } elseif (count($dealMapItems) === 1) {
            $dealMapItem = array_shift($dealMapItems);
        } else {
            $dealMapItem = new DealMapItem();
        }

        if ($dealMapItem->crm_lead_id !== $crmLeadId) {
            $dealMapItem->crm_lead_id = $crmLeadId;
            $dealMapItem->moysklad_order_uuid = $moyskladOrderUuid;

            return Table::getInstance('Deal_Map')->save($dealMapItem);
        }

        return false;
    }

    /**
     * Bind crm lead to site order
     *
     * @param   int $crmLeadId
     * @param   int $orderId
     *
     * @return  bool
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function bindCrmLeadToSiteOrder(int $crmLeadId, int $orderId): bool
    {
        $dealMapItems = $this->findAll(['conditions' => [
            $this->_db->qn('a.crm_lead_id') . ' = ' . $this->_db->q($crmLeadId) . ' OR ' . $this->_db->qn('a.order_id') . ' = ' . $this->_db->q($orderId)
        ]]);

        if (count($dealMapItems) === 2) {
            return $this->_mergeDealMapItems($dealMapItems);
        } elseif (count($dealMapItems) === 1) {
            $dealMapItem = array_shift($dealMapItems);
        } else {
            $dealMapItem = new DealMapItem();
        }

        if ($dealMapItem->crm_lead_id !== $crmLeadId) {
            $dealMapItem->order_id = $orderId;
            $dealMapItem->crm_lead_id = $crmLeadId;

            return Table::getInstance('Deal_Map')->save($dealMapItem);
        }

        return false;
    }

    /**
     * Bind moysklad order to crm lead
     *
     * @param   string $moyskladOrderUuid
     * @param   int $crmLeadId
     *
     * @return  bool
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function bindMoyskladOrderToCrmLead(string $moyskladOrderUuid, int $crmLeadId): bool
    {
        $dealMapItems = $this->findAll(['conditions' => [
            $this->_db->qn('a.moysklad_order_uuid') . ' = ' . $this->_db->q($moyskladOrderUuid) . ' OR ' . $this->_db->qn('a.crm_lead_id') . ' = ' . $this->_db->q($crmLeadId)
        ]]);

        if (count($dealMapItems) === 2) {
            return $this->_mergeDealMapItems($dealMapItems);
        } elseif (count($dealMapItems) === 1) {
            $dealMapItem = array_shift($dealMapItems);
        } else {
            $dealMapItem = new DealMapItem();
        }

        if ($dealMapItem->moysklad_order_uuid !== $moyskladOrderUuid) {
            $dealMapItem->crm_lead_id = $crmLeadId;
            $dealMapItem->moysklad_order_uuid = $moyskladOrderUuid;

            return Table::getInstance('Deal_Map')->save($dealMapItem);
        }

        return false;
    }

    /**
     * Bind moysklad order to site order
     *
     * @param   string $moyskladOrderUuid
     * @param   int $orderId
     *
     * @return  bool
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function bindMoyskladOrderToSiteOrder(string $moyskladOrderUuid, int $orderId): bool
    {
        $dealMapItems = $this->findAll(['conditions' => [
            $this->_db->qn('a.moysklad_order_uuid') . ' = ' . $this->_db->q($moyskladOrderUuid) . ' OR ' . $this->_db->qn('a.order_id') . ' = ' . $this->_db->q($orderId)
        ]]);

        if (count($dealMapItems) === 2) {
            return $this->_mergeDealMapItems($dealMapItems);
        } elseif (count($dealMapItems) === 1) {
            $dealMapItem = array_shift($dealMapItems);
        } else {
            $dealMapItem = new DealMapItem();
        }

        if ($dealMapItem->moysklad_order_uuid !== $moyskladOrderUuid) {
            $dealMapItem->order_id = $orderId;
            $dealMapItem->moysklad_order_uuid = $moyskladOrderUuid;

            return Table::getInstance('Deal_Map')->save($dealMapItem);
        }

        return false;
    }

    /**
     * Merge deal map item objects
     *
     * @param   array $dealMapItems
     *
     * @return  bool
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    private function _mergeDealMapItems(array $dealMapItems)
    {
        $dealMapItem = new DealMapItem();
        foreach ($dealMapItems as $item) {
            foreach ($item->toArray() as $key => $value) {
                if ($key != 'id' && !empty($value)) {
                    $dealMapItem->$key = $value;
                }
            }

            Table::getInstance('Deal_Map')->delete($item->id);
        }

        return Table::getInstance('Deal_Map')->save($dealMapItem);
    }
}
