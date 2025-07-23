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
use HYPERPC\Helper\HtmlHelper;
use HYPERPC\Helper\RenderHelper;
use HYPERPC\Joomla\Model\Entity\MoyskladProduct;
use HYPERPC\Joomla\Model\Entity\Interfaces\Stockable;

/**
 * @var         MoyskladProduct $product
 * @var         array           $buttons
 * @var         bool            $isInCart
 * @var         string          $type
 * @var         string          $itemKey
 * @var         RenderHelper    $this
 */

/** @var HtmlHelper */
$htmlHelper = $this->hyper['helper']['html'];

$classes = [
    'jsCartButtons',
    'hp-cart-btn',
    'hp-' . $type . '-btn-' . $product->id,
    ($isInCart === true) ? 'hp-element-in-cart' : ''
];

$commonClasses = 'uk-button uk-button-small uk-button-normal@s';

$gtmEnabled   = $this->hyper['helper']['google']->enabledGtm();
$gtmAddToCart = $gtmEnabled ? "gtmProductAddToCart('" . $itemKey . "');" : '';

$addCartButtonText = Text::_('COM_HYPERPC_BUY');
if (array_intersect($buttons, ['buy_in_stock', 'add_to_cart', 'add_to_cart_and_save_config'])) {
    $addCartButtonText = Text::_('COM_HYPERPC_TO_CART');
}

$addCartAttr = [
    'data-type'  => $type,
    'data-id'    => $product->id,
    'data-title' => $product->name,
    'class'      => [
        'jsAddToCart',
        'hp-add-to-cart',
        'uk-button-primary',
        $commonClasses
    ],
    'onclick' => $gtmAddToCart
];

$addCartDisabledAttr = [
    'class' => [
        'uk-disabled',
        'uk-button-secondary',
        $commonClasses
    ]
];

$linkCartAttr = [
    'href'  => $this->hyper['helper']['cart']->getUrl(),
    'title' => Text::_('COM_HYPERPC_GO_TO_CART'),
    'class' => 'uk-button-default hp-go-to-cart ' . $commonClasses
];

$linkConfigAttr = [
    'href'  => $product->getConfigUrl(),
    'title' => Text::_('COM_HYPERPC_GO_TO_CONFIGURATOR'),
    'class' => 'jsGoToConfigurator hp-config-btn uk-button-default uk-margin-small-left ' . $commonClasses
];
?>
<div class="<?= implode(' ', $classes) ?>" data-itemkey="<?= $itemKey ?>">
    <?php if (in_array('buy', $buttons) || in_array('add_to_cart', $buttons)) :
        $availability = $product->getAvailability();
        ?>
        <?php if (in_array($availability, [Stockable::AVAILABILITY_INSTOCK, Stockable::AVAILABILITY_PREORDER])) : ?>
            <span <?= $htmlHelper->buildAttrs($addCartAttr) ?>>
                <span uk-icon="icon: cart"></span>
                <?= $addCartButtonText ?>
            </span>
            <a <?= $htmlHelper->buildAttrs($linkCartAttr) ?>>
                <?= Text::_('COM_HYPERPC_ADDED_TO_CART_BUTTON_TEXT') ?>
            </a>
        <?php else : ?>
            <span <?= $htmlHelper->buildAttrs($addCartDisabledAttr) ?>>
                <span uk-icon="icon: cart"></span>
                <?= $addCartButtonText ?>
            </span>
        <?php endif; ?>
    <?php endif; ?>

    <?php if (in_array('buy_in_stock', $buttons) || in_array('add_to_cart', $buttons)) :
        $addCartAttr['data-stock-id'] = $product->params->get('stock')->id;
        ?>
        <span <?= $htmlHelper->buildAttrs($addCartAttr) ?>>
            <span uk-icon="icon: cart"></span>
            <?= $addCartButtonText ?>
        </span>
        <a <?= $htmlHelper->buildAttrs($linkCartAttr) ?>>
            <?= Text::_('COM_HYPERPC_ADDED_TO_CART_BUTTON_TEXT') ?>
        </a>
    <?php endif; ?>

    <?php if (in_array('buy_and_save_config', $buttons) || in_array('add_to_cart_and_save_config', $buttons)) :
        $addConfigAttr = $addCartAttr;
        $addConfigAttr['class'] = [
            'jsCreateConfigAddToCart',
            'hp-add-to-cart',
            'uk-button-primary',
            $commonClasses
        ];
        ?>
        <span <?= $htmlHelper->buildAttrs($addConfigAttr) ?>>
            <span uk-icon="icon: cart"></span>
            <?= $addCartButtonText ?>
        </span>
        <a <?= $htmlHelper->buildAttrs($linkCartAttr) ?>>
            <?= Text::_('COM_HYPERPC_ADDED_TO_CART_BUTTON_TEXT') ?>
        </a>
    <?php endif; ?>

    <?php if (in_array('configurator', $buttons)) : ?>
        <a <?= $htmlHelper->buildAttrs($linkConfigAttr) ?>>
            <span uk-icon="icon: cog"></span>
            <span><?= Text::_('COM_HYPERPC_CONFIGURATOR') ?></span>
        </a>
    <?php endif; ?>
</div>
