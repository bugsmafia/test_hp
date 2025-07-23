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
use HYPERPC\Joomla\View\ViewLegacy;
use HYPERPC\Joomla\Model\Entity\Order;

/**
 * @var HyperPcViewOrder $this
 */

/**
 * Class HyperPcViewOrder
 *
 * @property Order $order
 * @property array $groups
 * @property array $productFolders
 *
 * @since    2.0
 */
class HyperPcViewOrder extends ViewLegacy
{

    /**
     * Display action.
     *
     * @param   null|string $tpl
     * @return  bool
     *
     * @throws  \Exception
     * @throws  \RuntimeException
     *
     * @since   2.0
     */
    public function display($tpl = null)
    {
        $id    = $this->hyper['input']->get('id', 0, 'int');
        $token = $this->hyper['input']->get('token');

        /** @var Order $order */
        $order = $this->hyper['helper']['order']->findById($id);

        if (!$order->id || $token === null || $token !== $order->getToken()) {
            $this->hyper['cms']->enqueueMessage(Text::_('COM_HYPERPC_ORDER_NOT_FOUND'), 'error');
            $this->hyper['cms']->redirect('/', 403);
            return false;
        }

        $this->hyper['doc']->setMetaData('robots', 'noindex');

        $this->groups = $this->hyper['helper']['productFolder']->getList();

        $this->productFolders = $this->hyper['helper']['productFolder']->getList(false);
        
        $this->order = $order;

        $this->hyper['doc']->setTitle(Text::sprintf('COM_HYPERPC_ORDER_NUMBER', $this->order->getName()));

        parent::display($tpl);
    }
}
