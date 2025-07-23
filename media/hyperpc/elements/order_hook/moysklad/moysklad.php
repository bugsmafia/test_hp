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

use HYPERPC\Helper\CrmHelper;
use HYPERPC\Helper\MoyskladHelper;
use HYPERPC\Elements\ElementOrderHook;
use HYPERPC\Joomla\Model\Entity\Order;

/**
 * Class ElementOrderHookMoysklad
 *
 * @since   2.0
 */
class ElementOrderHookMoysklad extends ElementOrderHook
{

    /**
     * Hold order entity.
     *
     * @var     Order
     *
     * @since   2.0
     */
    protected $_order;

    /**
     * Hold helper instance.
     *
     * @var     MoyskladHelper
     *
     * @since   2.0
     */
    protected $_helper;

    /**
     * Hook action.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function hook()
    {
        $this->_order = $this->_getOrder();

        if (!count($this->_order->positions)) {
            return; // Don't create the customer order if there are no positions in the site order
        }

        $this->_helper = $this->hyper['helper']['moysklad'];

        try {
            $customerOrder = $this->_order->toMoyskladEntity();
        } catch (\Throwable $th) {
            $this->_helper->log('Method toMoyskladEntity in create order hook throws error: ' . $th->getMessage());
            return;
        }

        $customerOrder->applicable = true;
        $customerOrder->shared = true;

        $amoLeadId = $this->_order->getAmoLeadId();
        if (!empty($amoLeadId)) {
            $this->hyper['helper']['moyskladCustomerOrder']->setAmoSyncFields($customerOrder, $this->_order);
        }

        try {
            $customerOrder = $this->_helper->createCustomerOrder($customerOrder);
        } catch (\Throwable $th) {
            $this->_helper->log('Method createCustomerOrder in create order hook throws error: ' . $th->getMessage());
            return;
        }

        $moyskladUuid = $customerOrder->getMeta()->getId();

        $this->hyper['helper']['dealMap']->bindMoyskladOrderToSiteOrder($moyskladUuid, $this->_order->id);

        $this->_order->params->set('moysklad_uuid', $moyskladUuid);
        $this->_order->helper->getTable()->save($this->_order->getArray());

        if (!empty($amoLeadId)) {
            /** @var CrmHelper $crmHelper */
            $crmHelper = $this->hyper['helper']['crm'];
            $crmHelper->updateLead([[
                'updated_at'    => time(),
                'id'            => $amoLeadId,
                'tags'          => false,
                'custom_fields' => [[
                    'id' => $crmHelper->getCustomFieldId(CrmHelper::LEAD_FIELD_MOYSKLAD_ORDER_URL_KEY),
                    'values' => [['value' => $this->_order->getMoyskladEditUrl() ?? '']]
                ]]
            ]]);
        }
    }
}
