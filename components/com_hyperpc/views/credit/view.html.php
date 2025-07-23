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
 */

use Joomla\CMS\Form\Form;
use HYPERPC\Elements\Manager;
use Joomla\CMS\Language\Text;
use HYPERPC\Joomla\Model\ModelList;
use HYPERPC\Joomla\Model\ModelItem;
use HYPERPC\Joomla\View\ViewLegacy;
use HYPERPC\Joomla\Model\Entity\Cart;

defined('_JEXEC') or die('Restricted access');

/**
 * Class HyperPcViewCredit
 *
 * @property    Cart    $cart
 * @property    Form    $form
 * @property    array   $groups
 * @property    array   $elements
 *
 * @since       2.0
 */
class HyperPcViewCredit extends ViewLegacy
{

    /**
     * Hook on initialize view.
     *
     * @param   array $config
     * @return  void
     *
     * @since   2.0
     *
     * @SuppressWarnings("unused")
     */
    public function initialize(array $config)
    {
        /** @var HyperPcModelOrder|JModelLegacy $orderModel */
        $orderModel = ModelItem::getInstance('Order');
        $this->setModel($orderModel, true);
    }

    /**
     * Display action.
     *
     * @param   null|string $tpl
     * @return  void
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public function display($tpl = null)
    {
        if (!$this->hyper['helper']['credit']->isEnable()) {
            $this->hyper['cms']->enqueueMessage(Text::_('COM_HYPERPC_CRET_NOT_ENABLE'), 'info');
            $this->hyper['cms']->redirect($this->hyper['helper']['route']->getCartRoute());
        }

        $this->hyper['input']->set('tmpl', 'cart');

        $this->groups = $this->hyper['helper']['productFolder']->getList();
        $this->cart   = new Cart();

        if (!$this->cart->enableCredit()) {
            $this->hyper['cms']->enqueueMessage(Text::sprintf(
                'COM_HYPERPC_ORDER_ERROR_CREDIT_MIN_PRICE_LIMIT',
                $this->cart->getCreditMinSum()->html()
            ), 'info');
            $this->hyper['cms']->redirect($this->hyper['helper']['route']->getCartRoute());
        }

        $this->getModel()->itemsTotal = $this->cart->getTotalPrice();

        $this->form     = $this->get('Form');
        $this->elements = Manager::getInstance()->getByPosition('credit_form');

        parent::display($tpl);
    }

    /**
     * Load assets for display action.
     *
     * @return  void
     *
     * @since   2.0
     */
    protected function _loadAssets()
    {
        $this->hyper['helper']['assets']
            ->js('js:widget/credit.js')
            ->widget('.hp-credit-page', 'HyperPC.SiteCredit');
    }
}
