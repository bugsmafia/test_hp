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
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use HYPERPC\Helper\RenderHelper;
use HYPERPC\Joomla\Model\Entity\Interfaces\Stockable;
use HYPERPC\Joomla\Model\Entity\Interfaces\ProductMarker;

/**
 * @var RenderHelper    $this
 * @var ProductMarker   $product
 * @var string          $availability
 */

$fromStock = $availability === Stockable::AVAILABILITY_INSTOCK;

$cartRoute = $this->hyper['helper']['cart']->getUrl();

$gtmOnclick = $this->hyper['helper']['render']->render('common/teaser/gtmProductClick', ['entity' => $product]);
?>
<?php if ($fromStock) :
    $itemKey = $product->getItemKey();

    $gtmEnabled   = $this->hyper['helper']['google']->enabledGtm();
    $gtmAddToCart = $gtmEnabled ? "onclick=\"gtmProductAddToCart('{$itemKey}')\"" : '';
    ?>
    <span class="jsCartButtons hp-cart-btn" data-itemkey="<?= $itemKey ?>">
        <button
            type="button"
            data-type="configuration"
            data-id="<?= $product->get('id') ?>"
            data-saved-configuration="<?= $product->getStockConfigurationId() ?>"
            class="jsAddToCart hp-add-to-cart uk-button uk-button-primary uk-width-1-1"
            <?= $gtmAddToCart ?>
        >
            <?= Text::_('COM_HYPERPC_ADD_TO_CART') ?>
        </button>
        <a href="<?= $cartRoute ?>" class="hp-go-to-cart uk-button uk-button-secondary uk-width-1-1">
            <?= Text::_('COM_HYPERPC_GO_TO_CART') ?>
        </a>
    </span>

    <a href="<?= $product->getViewUrl(); ?>" class="uk-button uk-button-default uk-width-1-1 tm-margin-16-top"<?= $gtmOnclick ?>>
        <?= Text::_('COM_HYPERPC_PRODUCT_TEASER_DETAILS') ?>
    </a>
<?php else :
    $available = $availability === Stockable::AVAILABILITY_PREORDER;
    ?>
    <?php if ($available) : ?>
        <a href="<?= $product->getConfigUrl() ?>" class="jsGoToConfigurator uk-button uk-button-primary uk-width-1-1">
            <?= Text::_('COM_HYPERPC_CONFIGURATE_AND_BUY') ?>
        </a>
    <?php else : ?>
        <span class="uk-button uk-button-secondary uk-disabled uk-width-1-1">
            <?= Text::_('COM_HYPERPC_CONFIGURATE_AND_BUY') ?>
        </span>
    <?php endif; ?>

    <a href="<?= $product->getViewUrl(); ?>" class="uk-button uk-button-default uk-width-1-1 tm-margin-16-top"<?= $gtmOnclick ?>>
        <?= Text::_('COM_HYPERPC_PRODUCT_TEASER_DETAILS') ?>
    </a>
<?php endif;
