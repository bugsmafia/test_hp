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
use HYPERPC\ORM\Entity\ProductInStock;
use HYPERPC\Joomla\Model\Entity\Interfaces\Stockable;
use HYPERPC\Joomla\Model\Entity\Interfaces\ProductMarker;

/**
 * @var         RenderHelper    $this
 * @var         string          $type
 * @var         string          $itemKey
 * @var         ProductMarker   $product
 * @var         array           $buttons
 * @var         bool            $isInCart
 */

/** @var HtmlHelper */
$htmlHelper = $this->hyper['helper']['html'];

$classes = [
    'jsCartButtons',
    'hp-cart-btn',
    'hp-' . $type . '-btn-' . $product->id,
    ($isInCart === true) ? 'hp-element-in-cart' : ''
];

$gtmEnabled   = $this->hyper['helper']['google']->enabledGtm();
$gtmAddToCart = $gtmEnabled ? "gtmProductAddToCart('" . $itemKey . "');" : '';
$gtmOnClick   = $this->hyper['helper']['render']->render('common/teaser/gtmProductClick', ['entity' => $product]);

$addCartAttr = [
    'data-type'  => $type,
    'data-id'    => $product->id,
    'data-title' => $product->name,
    'class'      => [
        'jsAddToCart',
        'hp-add-to-cart',
        'uk-button uk-button-primary uk-button-small'
    ],
    'onclick' => $gtmAddToCart
];

$addCartDisabledAttr = [
    'class' => [
        'uk-button uk-button-secondary uk-button-small',
        'uk-disabled'
    ],
];

$linkCartAttr = [
    'href'  => $this->hyper['helper']['cart']->getUrl(),
    'title' => Text::_('COM_HYPERPC_GO_TO_CART'),
    'class' => 'uk-button uk-button-default uk-button-small hp-go-to-cart'
];

$linkConfigAttr = [
    'href'    => $product->getConfigUrl(),
    'title'   => Text::_('COM_HYPERPC_GO_TO_CONFIGURATOR'),
    'class'   => 'jsGoToConfigurator uk-button uk-button-default uk-button-small'
];

$linkDetailsAttr = [
    'href'    => $product->getViewUrl(),
    'title'   => Text::_('COM_HYPERPC_PRODUCT_TEASER_DETAILS'),
    'class'   => 'uk-button uk-button-default uk-button-small'
];

$linkChooseAttr = [
    'href'    => $product->getConfigUrl(),
    'title'   => Text::_('COM_HYPERPC_GO_TO_CONFIGURATOR'),
    'class'   => 'jsGoToConfigurator uk-button uk-button-primary uk-button-small'
];
?>

<?php if (in_array('buy', $buttons)) :
    $availability = $product->getAvailability();
    ?>
    <?php if (in_array($availability, [Stockable::AVAILABILITY_INSTOCK, Stockable::AVAILABILITY_PREORDER])) : ?>
        <div class="<?= implode(' ', $classes) ?>" data-itemkey="<?= $itemKey ?>">
            <span>
                <span <?= $htmlHelper->buildAttrs($addCartAttr) ?>>
                    <span class="uk-icon">
                        <?= $htmlHelper->svgIcon('cart') ?>
                    </span>
                    <?= Text::_('COM_HYPERPC_TO_CART') ?>
                </span>
            </span>
            <span>
                <a <?= $htmlHelper->buildAttrs($linkCartAttr) ?>>
                    <?= Text::_('COM_HYPERPC_ADDED_TO_CART_BUTTON_TEXT') ?>
                </a>
            </span>
        </div>
    <?php else : ?>
        <div class="<?= implode(' ', $classes) ?>">
            <span>
                <span <?= $htmlHelper->buildAttrs($addCartDisabledAttr) ?>>
                    <span class="uk-icon">
                        <?= $htmlHelper->svgIcon('cart') ?>
                    </span>
                    <?= Text::_('COM_HYPERPC_TO_CART') ?>
                </span>
            </span>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php if (in_array('buy_in_stock', $buttons)) :
    $stock = $product->params->get('stock');
    if ($stock instanceof ProductInStock) {
        $itemKey .= '-' . $stock->id;
        $addCartAttr['data-stock-id'] = $stock->id;
    } elseif ($product->saved_configuration) {
        $addCartAttr['data-type'] = 'configuration';
        $addCartAttr['data-saved-configuration'] = $product->saved_configuration;
    }
    ?>
    <div class="<?= implode(' ', $classes) ?>" data-itemkey="<?= $itemKey ?>">
        <span>
            <span <?= $htmlHelper->buildAttrs($addCartAttr) ?>>
                <span class="uk-icon">
                    <?= $htmlHelper->svgIcon('cart') ?>
                </span>
                <?= Text::_('COM_HYPERPC_TO_CART') ?>
            </span>
        </span>
        <span>
            <a <?= $htmlHelper->buildAttrs($linkCartAttr) ?>>
                <?= Text::_('COM_HYPERPC_ADDED_TO_CART_BUTTON_TEXT') ?>
            </a>
        </span>
    </div>
<?php endif; ?>

<?php if (in_array('configurator', $buttons)) : ?>
    <span>
        <a <?= $htmlHelper->buildAttrs($linkConfigAttr) ?><?= $gtmOnClick ?>>
            <span class="uk-icon">
                <?= $htmlHelper->svgIcon('cog') ?>
            </span>
            <?= Text::_('COM_HYPERPC_CONFIGURATOR') ?>
        </a>
    </span>
<?php endif; ?>

<?php if (in_array('details', $buttons)) : ?>
    <span>
        <a <?= $htmlHelper->buildAttrs($linkDetailsAttr) ?><?= $gtmOnClick ?>>
            <?= Text::_('COM_HYPERPC_PRODUCT_TEASER_DETAILS') ?>
        </a>
    </span>
<?php endif; ?>

<?php if (in_array('choose', $buttons)) : ?>
    <span>
        <a <?= $htmlHelper->buildAttrs($linkChooseAttr) ?><?= $gtmOnClick ?>>
            <?= Text::_('COM_HYPERPC_CHOOSE') ?>
        </a>
    </span>
<?php endif;
