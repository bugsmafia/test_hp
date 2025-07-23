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
use Joomla\CMS\Form\Form;
use HYPERPC\Money\Type\Money;
use HYPERPC\Elements\Manager;
use Joomla\CMS\Language\Text;
use HYPERPC\Helper\CartHelper;
use HYPERPC\Joomla\Model\ModelItem;
use HYPERPC\Joomla\View\ViewLegacy;
use HYPERPC\Joomla\Model\Entity\Cart;
use HYPERPC\Joomla\Model\Entity\Interfaces\PartMarker;
use HYPERPC\Joomla\Model\Entity\Interfaces\ProductMarker;

/**
 * Class HyperPcViewCart
 *
 * @property    PartMarker|ProductMarker $item
 * @property    Cart                     $cart
 * @property    Data                     $data
 * @property    Form                     $form
 * @property    array                    $image
 * @property    array                    $groups
 * @property    array                    $options
 * @property    array                    $formData
 * @property    array                    $items
 * @property    Data                     $elements
 * @property    string                   $quantity
 * @property    Money                    $linePrice
 * @property    bool                     $showCaptcha
 *
 * @since       2.0
 */
class HyperPcViewCart extends ViewLegacy
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
     * Default display view action.
     *
     * @param   null|string $tpl
     * @return  mixed
     *
     * @throws  Exception
     * @throws  RuntimeException
     *
     * @since   2.0
     */
    public function display($tpl = null)
    {
        $this->hyper['input']->set('tmpl', 'cart');

        /** @var CartHelper */
        $cartHelper = $this->hyper['helper']['cart'];

        $this->groups = $this->hyper['helper']['productFolder']->getList();
        $this->cart   = new Cart();

        $this->getModel()->itemsTotal = $this->cart->getTotalPrice();

        $this->form     = $this->getModel()->getForm();
        $this->formData = $this->getModel()->loadFormData();
        if ($this->form) {
            $this->form->bind($this->formData);
        }

        $this->elements = Manager::getInstance()->getByPosition('order_form');
        $this->options  = $this->hyper['helper']['moyskladVariant']->getVariants();

        $this->items = $cartHelper->getItems();
        $jsItems = [];
        foreach ($this->items as $item) {
            $tempItem = clone $item;
            if ($item->get('savedConfiguration')) {
                $tempItem->set('name', $tempItem->get('name') . ' (' . Text::_('COM_HYPERPC_NUM') . $item->get('savedConfiguration') . ')');
            }

            $jsItems[] = $tempItem;
        }

        $this->showCaptcha = $cartHelper->showCaptcha();

        $this->hyper['helper']['google']
            ->setJsCartItems($jsItems, Text::_('COM_HYPERPC_ECOMMERCE_ITEM_LIST_NAME_CART_PAGE'), 'cart_page')
            ->setDataLayerAddToCart()
            ->setDataLayerRemoveFromCart();

        $this->hyper['doc']->setMetaData('robots', 'noindex');

        parent::display($tpl);
    }

    /**
     * Load assets for display action.
     *
     * @return  void
     *
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    protected function _loadAssets()
    {
        $creditMinLimit = $this->cart->getCreditMinSum();
        $creditMaxLimit = $this->cart->getCreditMaxSum();
        $orderMinLimit  = $this->cart->getOrderMinSum();

        $creditMinLimitMsg = Text::sprintf('COM_HYPERPC_ORDER_ERROR_CREDIT_MIN_PRICE_LIMIT', $creditMinLimit->text());
        $creditMaxLimitMsg = Text::sprintf('COM_HYPERPC_ORDER_ERROR_CREDIT_MAX_PRICE_LIMIT', $creditMaxLimit->text());
        $orderMinLimitMsg  = Text::sprintf('COM_HYPERPC_ORDER_ERROR_MIN_PRICE_LIMIT', $orderMinLimit->text());

        $googleHelper = $this->hyper['helper']['google'];

        $this->hyper['helper']['assets']
            ->js('js:widget/cart.js')
            ->widget('.hp-cart-page', 'HyperPC.SiteCart', [
                'creditMinSum'          => $creditMinLimit->val(),
                'creditMaxSum'          => $creditMaxLimit->val(),
                'orderMinSum'           => $orderMinLimit->val(),
                'creditMaxLimitMsg'     => $creditMaxLimitMsg,
                'creditMinLimitMsg'     => $creditMinLimitMsg,
                'orderMinLimitMsg'      => $orderMinLimitMsg,
                'vat'                   => $this->hyper['params']->get('vat', 20, 'int'),
                'gtmAddCallback'        => $googleHelper->getJsFunctionAddToCartName(),
                'gtmRemoveCallback'     => $googleHelper->getJsFunctionRemoveFromCartName(),
                'removeFromCartConfirm' => Text::_('COM_HYPERPC_CART_REMOVE_ITEM_CONFIRM_TEXT'),
                'clearCartConfirm'      => Text::_('COM_HYPERPC_CART_CLEAR_CONFIRM_TEXT')
            ]);

        $this->hyper['helper']['assets']
            ->js('js:widget/sticky-bottom.js')
            ->widget('.jsStickyBottom', 'HyperPC.StickyBottom');
    }
}
