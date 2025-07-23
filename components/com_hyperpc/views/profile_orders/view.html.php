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

use Joomla\CMS\Form\Form;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use HYPERPC\Joomla\Model\ModelList;
use HYPERPC\Joomla\View\ViewLegacy;
use Joomla\CMS\Pagination\Pagination;

defined('_JEXEC') or die('Restricted access');

/**
 * Class HyperPcViewProfile_Orders
 *
 * @property    array       $orders
 * @property    Form        $filterForm
 * @property    Pagination  $pagination
 *
 * @since       2.0
 */
class HyperPcViewProfile_Orders extends ViewLegacy
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
        $app  = $this->hyper['app'];
        $user = $this->hyper['user'];

        //  Check logged the user.
        if (!$user->id) {
            $app->enqueueMessage(Text::_('JGLOBAL_REMEMBER_MUST_LOGIN'), 'info');
            $app->redirect(Route::_('index.php?option=com_users&view=login', false));
            return false;
        }

        /** @var \HyperPcModelOrders $mOrder */
        $mOrder = ModelList::getInstance('Orders');

        $this->orders     = $mOrder->getItems();
        $this->filterForm = $mOrder->getFilterForm();
        $this->pagination = $mOrder->getPagination();

        return parent::display($tpl);
    }
}
