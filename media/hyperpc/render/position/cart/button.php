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
 * @author      Roman Evsyukov
 */

use Joomla\CMS\Language\Text;
use HYPERPC\Helper\HtmlHelper;
use HYPERPC\Helper\CartHelper;
use HYPERPC\Helper\GoogleHelper;
use HYPERPC\Helper\RenderHelper;
use HYPERPC\Joomla\Model\Entity\Position;
use HYPERPC\Joomla\Model\Entity\MoyskladPart;
use HYPERPC\Joomla\Model\Entity\MoyskladProduct;
use HYPERPC\Joomla\Model\Entity\MoyskladVariant;
use HYPERPC\Joomla\Model\Entity\Interfaces\Stockable;

/**
 * @var         bool            $isInCart
 * @var         bool            $useDefaultOption
 * @var         string          $size
 * @var         array           $buttons
 * @var         Position        $position
 * @var         RenderHelper    $this
 * @var         MoyskladVariant $option
 */

if ($position->getType() === 'service' && !$position->isForRetailSale()) {
    return;
}

$onlyForUpgrade = $position instanceof MoyskladPart && $position->isOnlyForUpgrade();
if ($onlyForUpgrade) {
    return;
}

/** @var HtmlHelper */
$htmlHelper = $this->hyper['helper']['html'];

/** @var GoogleHelper */
$googleHelper = $this->hyper['helper']['google'];

$itemId   = $position->id;
$itemKey  = $position->getItemKey();
$itemType = $position->getType();

$disabled = false;
if ($position instanceof Stockable) {
    $availability = $position->getAvailability();
    $disabled = in_array($availability, [Stockable::AVAILABILITY_OUTOFSTOCK, Stockable::AVAILABILITY_DISCONTINUED]);
}
$disabledClass = $disabled ? ' uk-disabled tm-background-gray-25 uk-text-muted' : '';

if ($position instanceof MoyskladPart) {
    if (method_exists($position, 'getDefaultOption') && $useDefaultOption === true) {
        $option = $position->getDefaultOption();
    }

    if (isset($option) && $option->id !== null) {
        $itemId      .= '-' . $option->id;
        $availability = $option->getAvailability();

        $itemKey = $option->getItemKey();
    }
}

$buttonSize = '';
if ($position instanceof MoyskladPart) {
    switch ($size) {
        case 'small':
            $buttonSize = ' uk-button-small';
            break;
        case 'navbar':
            $buttonSize = ' uk-button-small uk-button-normal@s';
            break;
    }
} elseif ($position instanceof MoyskladProduct) {
    $buttonSize = ' uk-button-small uk-button-normal@s';
}

$gtmAddToCart = $googleHelper->enabledGtm() ? "gtmProductAddToCart('" . $itemKey . "');" : '';

$classes = [
    'jsCartButtons',
    'hp-cart-btn',
    'hp-' . $itemType . '-btn-' . $itemId,
    $isInCart ? 'hp-element-in-cart' : ''
];

$linkCartAttr = [
    'href'   => $this->hyper['helper']['cart']->getUrl(),
    'title'  => Text::_('COM_HYPERPC_GO_TO_CART'),
    'class'  => 'uk-button uk-button-default hp-go-to-cart' . $buttonSize,
    'target' => $this->hyper['input']->get('tmpl') === 'component' ? '_blank' : false
];

$addCartAttr = [
    'type'       => 'button',
    'data-type'  => CartHelper::TYPE_POSITION,
    'data-id'    => $position->id,
    'data-title' => $position->name,
    'class'      => [
        'jsAddToCart',
        'hp-add-to-cart',
        'uk-button',
        'uk-button-primary',
        'uk-flex',
        'uk-flex-middle',
        $buttonSize,
        $disabledClass
    ],
    'onclick' => $gtmAddToCart
];

$addCartButtonContent = [
    '<span class="uk-icon tm-margin-4-right">' . $htmlHelper->svgIcon('clock', 18, 18) . '</span>',
    Text::_('COM_HYPERPC_TO_ORDER')
];

if ($position->isService() || $availability === Stockable::AVAILABILITY_INSTOCK) {
    $addCartButtonContent = [
        '<span class="uk-icon tm-margin-4-right">' . $htmlHelper->svgIcon('cart') . '</span>',
        Text::_('COM_HYPERPC_BUY')
    ];
}

if ($position->isProduct()) {
    $addCartButtonContent = [
        '<span class="uk-icon">' . $htmlHelper->svgIcon('cart') . '</span>',
        Text::_('COM_HYPERPC_TO_CART')
    ];

    if (in_array('add_to_cart_and_save_config', $buttons)) {
        $addConfigAttr = $addCartAttr;
        $addCartAttr['class'] = [
            'jsCreateConfigAddToCart',
            'hp-add-to-cart',
            'uk-button',
            'uk-button-primary',
            $buttonSize
        ];
    } elseif ($position->saved_configuration) {
        $addCartAttr['data-type'] = CartHelper::TYPE_CONFIGURATION;
        $addCartAttr['data-saved-configuration'] = $position->saved_configuration;
    }
}

if (isset($option) && $option->id !== null) {
    $addCartAttr['data-default-option'] = $option->id;
}
?>
<div class="<?= implode(' ', $classes) ?>" data-itemkey="<?= $itemKey ?>">
    <button <?= $htmlHelper->buildAttrs($addCartAttr) ?>>
        <?= implode(PHP_EOL, $addCartButtonContent) ?>
    </button>

    <a <?= $htmlHelper->buildAttrs($linkCartAttr) ?>>
        <?= Text::_('COM_HYPERPC_ADDED_TO_CART_BUTTON_TEXT') ?>
    </a>
</div>
