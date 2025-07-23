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

use JBZoo\Data\Data;
use JBZoo\Utils\Filter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use HYPERPC\Joomla\Model\Entity\Order;
use HYPERPC\Joomla\Controller\ControllerForm;

/**
 * Class HyperPcControllerOrder
 *
 * @method  HyperPcModelOrder getModel($name = '', $prefix = '', $config = ['ignore_request' => true])
 *
 * @since   2.0
 */
class HyperPcControllerOrder extends ControllerForm
{

    /**
     * The URL view list variable.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $view_item = 'cart';

    /**
     * The URL view list variable.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $view_list = 'order';

    /**
     * Hook on initialize controller.
     *
     * @param   array $config
     *
     * @return  void
     *
     * @since   2.0
     *
     * @SuppressWarnings("unused")
     */
    public function initialize(array $config)
    {
        $this
            ->registerTask('save', 'save')
            ->registerTask('save_credit', 'saveCredit');

        if ($this->hyper['input']->get('task') === 'save' && $this->hyper['user']->id) {
            $this->view_list = 'profile_order';
        }
    }

    /**
     * Method to save a record.
     *
     * @param   string $key     The name of the primary key of the URL variable.
     * @param   string $urlVar  The name of the URL variable if different from the primary key
     *                          (sometimes required to avoid router collisions).
     *
     * @return  bool
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function save($key = null, $urlVar = null)
    {
        $this->hyper['helper']['order']->setSession('form');

        $data = new Data($this->hyper['input']->get(JOOMLA_FORM_CONTROL, [], 'array'));

        if (!Session::checkToken()) {
            $this->hyper['cms']->enqueueMessage(Text::_('JINVALID_TOKEN'), 'error');
            $this->hyper['cms']->redirect($this->hyper['helper']['route']->getCartRoute());
            return false;
        };

        $total      = Filter::int($data->get('total', 0));
        $allowTotal = Filter::int($this->hyper['params']->get('order_min_price', 3000));

        if ($total < $allowTotal) {
            $allowedTotalTxt = $this->hyper['helper']['money']->get($allowTotal)->text();
            $msg = Text::sprintf('COM_HYPERPC_ORDER_ERROR_MIN_PRICE_LIMIT', $allowedTotalTxt);

            $this->hyper['cms']->enqueueMessage($msg, 'info');
            $this->hyper['cms']->redirect($this->hyper['helper']['route']->getCartRoute());
            return false;
        }

        $result = parent::save($key, $urlVar);
        if ($result) {
            $model = $this->getModel();
            $order = $model::getHoldOrder();

            $this->setMessage(
                $this->hyper['helper']['order']->getSuccessMessage($order)
            );
        }

        return $result;
    }

    /**
     * Method to save a record from product credit form.
     *
     * @return  bool
     *
     * @throws  Exception
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function saveCredit()
    {
        $this->hyper['helper']['order']->setSession('form');

        $data       = new Data($this->hyper['input']->get(JOOMLA_FORM_CONTROL, [], 'array'));
        $total      = Filter::int($data->get('total', 0));
        $allowTotal = Filter::int($this->hyper['params']->get('credit_min_price', 3000));

        if ($total < $allowTotal) {
            $allowedTotalTxt = $this->hyper['helper']['money']->get($allowTotal)->text();
            $msg = Text::sprintf('COM_HYPERPC_ORDER_ERROR_CREDIT_MIN_PRICE_LIMIT', $allowedTotalTxt);

            $this->hyper['cms']->enqueueMessage($msg, 'info');
            $this->hyper['cms']->redirect($this->hyper['helper']['route']->getCartRoute());
            return false;
        }

        $result = parent::save(null, null);
        $model  = $this->getModel();

        $orderId = null;
        if ($model::getHoldOrder() !== null) {
            $orderId = $model::getHoldOrder()->id;
        }

        /** @var Order $order */
        $order = $this->hyper['helper']['order']->findById($orderId, ['new' => true]);
        if (!$result) {
            //  Remove invalid order.
            if ($order->id) {
                $model->getTable()->delete($order->id);
            }
            $this->hyper['cms']->redirect($this->hyper['route']->build(['view' => 'credit']));
            return false;
        }

        $redirectUrl = $this->hyper['route']->build([
            'view'  => 'order',
            'id'    => $order->id,
            'token' => $order->getToken()
        ]);

        $this->hyper['cms']->enqueueMessage(
            $this->hyper['helper']['order']->getSuccessMessage($order)
        );

        $this->hyper['cms']->redirect($redirectUrl);

        return true;
    }

    /**
     * Gets the URL arguments to append to an item redirect.
     *
     * @param   null    $recordId
     * @param   string  $urlVar
     *
     * @return  string
     *
     * @since   2.0
     */
    protected function getRedirectToItemAppend($recordId = null, $urlVar = 'id')
    {
        $append = parent::getRedirectToItemAppend($recordId, $urlVar);
        $data   = new Data($this->hyper['input']->get(JOOMLA_FORM_CONTROL, [], 'array'));

        if ($data->get('form', 0, 'int') === HP_ORDER_FORM_CREDIT) {
            $this->view_item = 'credit';
            return '&view=credit';
        }

        if (array_key_exists('form', $data->getArrayCopy())) {
            $append .= '#form';
        }

        return $append;
    }

    /**
     * Gets the URL arguments to append to a list redirect.
     *
     * @return  string
     *
     * @since   2.0
     */
    protected function getRedirectToListAppend()
    {
        $url   = parent::getRedirectToListAppend();
        $model = $this->getModel();
        $order = $model::getHoldOrder();

        if ($order instanceof Order) {
            $url .= '&id=' . $order->id;
            if (!$this->hyper['user']->id) {
                $url .= '&token=' . $order->getToken();
            }
        }

        return $url;
    }

    /**
     * Method to check if you can add a new record.
     *
     * @param   array $data
     *
     * @return  bool
     *
     * @since   2.0
     */
    protected function allowAdd($data = [])
    {
        return true;
    }

    /**
     * Method to check if you can save a new or existing record.
     * Extended classes can override this if necessary.
     *
     * @param   array   $data
     * @param   string  $key
     *
     * @return  bool
     *
     * @since   2.0
     */
    protected function allowSave($data, $key = HP_TABLE_PRIMARY_KEY)
    {
        return true;
    }
}
