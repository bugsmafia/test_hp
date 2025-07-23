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

use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Pathway\Pathway;
use HYPERPC\Joomla\Model\ModelList;
use HYPERPC\Joomla\View\ViewLegacy;
use HYPERPC\Joomla\Model\Entity\Order;

/**
 * Class HyperPcViewProfile_Orders
 *
 * @property    Order $order
 * @property    array $productFolders
 * @property    array $groups
 *
 * @since       2.0
 */
class HyperPcViewProfile_Order extends ViewLegacy
{

    /**
     * Display action.
     *
     * @param   null $tpl
     *
     * @return  bool|mixed
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public function display($tpl = null)
    {
        $db     = $this->hyper['db'];
        $app    = $this->hyper['app'];
        $user   = $this->hyper['user'];
        $params = $this->hyper['params'];

        //  Check logged the user.
        if (!$user->id) {
            $app->enqueueMessage(Text::_('JGLOBAL_REMEMBER_MUST_LOGIN'), 'info');
            $app->redirect(Route::_('index.php?option=com_users&view=login', false));
            return false;
        }

        $this->order = $this->hyper['helper']['order']->findById($this->hyper['input']->get('id'), [
            'conditions' => [
                $db->quoteName('a.created_user_id') . ' = ' . $db->quote($user->id),
                $db->quoteName('a.context') . ' = ' . $db->quote($params->get('site_context'))
            ]
        ]);

        if (!$this->order->id) {
            $app->enqueueMessage(Text::_('COM_HYPERPC_ORDER_NOT_FOUND'), 'error');
            $app->redirect(Route::_('index.php?option=com_users&view=profile', false));
            return false;
        }

        $this->groups = $this->hyper['helper']['productFolder']->getList();

        $this->productFolders = $this->hyper['helper']['ProductFolder']->getList(false);

        /** @var Pathway $pathway */
        $pathway = $this->hyper['cms']->getPathway();
        $pathway->addItem(Text::sprintf('COM_HYPERPC_ORDER_NUMBER', $this->order->getName()));

        return parent::display($tpl);
    }

    /**
     * Load assets for display action.
     *
     * @return  void
     *
     * @since  2.0
     */
    protected function _loadAssets()
    {
        parent::_loadAssets();

        // (?) no longer needed
        // $this->hyper['helper']['assets']
        //     ->js('js:widget/site/order.js')
        //     ->widget('.hp-order', 'HyperPC.SiteOrder', [
        //         'sberbankWarningBeforeSend' => Text::_('COM_HYPERPC_CREDIT_SBERBANK_WARNING_BEFORE_SEND')
        //     ]);
    }
}
