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

defined('_JEXEC') or die('Restricted access');

use HYPERPC\Helper\OrderHelper;
use HYPERPC\ORM\Entity\DealMapItem;
use HYPERPC\Joomla\Model\Entity\Order;
use HYPERPC\Joomla\Controller\ControllerAdmin;

/**
 * Class HyperPcControllerDeal_Map
 *
 * @since   2.0
 */
class HyperPcControllerDeal_Map extends ControllerAdmin
{
    /**
     * Update deal map.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function create()
    {
        $redirectUrl = $this->hyper['route']->build(['view' => 'manager']);

        /** @var OrderHelper $orderHelper */
        $orderHelper = $this->hyper['helper']['order'];

        /** @var Order[] $orders */
        $orders = $orderHelper->findAll();
        $i = 0;
        foreach ($orders as $order) {
            $moyskladUuid = $order->getUuid() ?: null;
            $crmLeadId = $order->getAmoLeadId() ?: null;

            $dealMapItem = new DealMapItem();
            $dealMapItem->order_id = $order->id;
            $dealMapItem->moysklad_order_uuid = $moyskladUuid;
            $dealMapItem->crm_lead_id = $crmLeadId;

            $result = false;
            try {
                $result = $dealMapItem->getTable()->save($dealMapItem->toArray());
            } catch (\Throwable $th) {
                $this->hyper['cms']->enqueueMessage($th->getMessage(), 'error');
            }

            if ($result) {
                $i++;
            }
        }

        $this->hyper['cms']->enqueueMessage($i . ' rows created');
        $this->hyper['cms']->redirect($redirectUrl);
    }
}
