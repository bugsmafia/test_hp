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

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use HYPERPC\Joomla\Controller\ControllerForm;

/**
 * Class HyperPcControllerOrder
 *
 * @since   2.0
 */
class HyperPcControllerOrder extends ControllerForm
{

    /**
     * The prefix to use with controller messages.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $text_prefix = 'COM_HYPERPC_ORDER';

    /**
     * Hook on initialize controller.
     *
     * @param   array $config
     *
     * @return  void
     *
     * @since   2.0
     */
    public function initialize(array $config)
    {
        $this->registerTask('send_to_moysklad', 'sendToMoysklad');
    }

    /**
     * Send order to Moysklad.
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function sendToMoysklad()
    {
        Session::checkToken() or die(Text::_('JINVALID_TOKEN'));

        $redirectUrl = 'index.php?option=' . HP_OPTION . '&view=orders';

        $orderId = $this->input->get('id');
        if (empty($orderId)) {
            $message = Text::_($this->text_prefix . '_NOT_FOUND');
            $this->setRedirect($redirectUrl, $message, 'error');
            return false;
        }

        $orderHelper = $this->hyper['helper']['order'];

        $order = $orderHelper->findById($orderId);
        if (empty($order->id)) {
            $message = Text::_($this->text_prefix . '_NOT_FOUND');
            $this->setRedirect($redirectUrl, $message, 'error');
            return false;
        }

        $moyskladHelper = $this->hyper['helper']['moysklad'];
        try {
            $customerOrders = $moyskladHelper->findCustomerordersByExternalCode($order->id);
        } catch (\Throwable $th) {
            $this->setRedirect($redirectUrl, $th->getMessage(), 'error');
            return false;
        }

        if (!empty($customerOrders)) {
            $customerOrder = current($customerOrders);
        } else {
            try {
                $customerOrder = $order->toMoyskladEntity();
            } catch (\Throwable $th) {
                $this->setRedirect($redirectUrl, $th->getMessage(), 'error');
                return false;
            }

            $customerOrder->applicable = true;
            $customerOrder->shared = true;

            try {
                $customerOrder = $moyskladHelper->createCustomerOrder($customerOrder);
            } catch (\Throwable $th) {
                $this->setRedirect($redirectUrl, $th->getMessage(), 'error');
                return false;
            }
        }

        $editHref = $customerOrder->getMeta()->uuidHref;
        $uuid = $customerOrder->getMeta()->getId();

        $this->hyper['helper']['dealMap']->bindMoyskladOrderToSiteOrder($uuid, $order->id);

        $order->params->set('moysklad_uuid', $uuid);
        $orderHelper->getTable()->save($order->getArray());

        $this->setRedirect($redirectUrl, Text::sprintf(
            $this->text_prefix . '_MOYSKLAD_SUCCESS_SEND_ALERT_MSG',
            $order->id,
            $editHref,
            $editHref
        ));

        return true;
    }
}
