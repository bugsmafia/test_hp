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

use Joomla\CMS\Language\Text;
use HYPERPC\Helper\CartHelper;
use HYPERPC\Helper\HtmlHelper;
use HYPERPC\Helper\RenderHelper;
use HYPERPC\Joomla\Model\Entity\Interfaces\ProductMarker;

/**
 * @var         ProductMarker $product
 * @var         bool          $isInCart
 * @var         string        $type
 * @var         string        $itemKey
 * @var         RenderHelper  $this
 */

/** @var CartHelper */
$cartHelper = $this->hyper['helper']['cart'];
/** @var HtmlHelper */
$htmlHelper = $this->hyper['helper']['html'];

$wrapperClass = [
    'jsCartButtons',
    'hp-cart-btn',
    'hp-' . $type . '-btn-' . $product->id,
    ($isInCart === true) ? 'hp-element-in-cart' : ''
];

$commonClasses = 'uk-button uk-button-small uk-button-normal@s';

$addCartAttr = [
    'data'  => [
        'id'    => $product->id,
        'title' => $product->name,
        'type'  => CartHelper::TYPE_CONFIGURATION
    ],
    'class' => [
        'jsAddToCart',
        'hp-add-to-cart',
        'uk-button-primary',
        $commonClasses
    ],
];

$linkCartAttr = [
    'href'   => $cartHelper->getUrl(),
    'title'  => Text::_('COM_HYPERPC_GO_TO_CART'),
    'class'  => [
        'jsGoToCart',
        'hp-go-to-cart',
        'uk-button-default',
        $commonClasses
    ],
];
?>

<div class="<?= implode(' ', $wrapperClass) ?>" data-itemkey="<?= $itemKey ?>">
    <button <?= $htmlHelper->buildAttrs($addCartAttr) ?>>
        <span uk-icon="icon: cart"></span>
        <?= Text::_('COM_HYPERPC_BUY') ?>
    </button>
    <a <?= $htmlHelper->buildAttrs($linkCartAttr) ?>>
        <?= Text::_('COM_HYPERPC_ADDED_TO_CART_BUTTON_TEXT') ?>
    </a>
</div>

<?php
echo $this->render('product/teaser/credit_form', [
    'product' => $product
]);
