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
use HYPERPC\Helper\CartHelper;
use HYPERPC\Helper\RenderHelper;
use HYPERPC\Helper\MoyskladProductHelper;
use HYPERPC\Joomla\Model\Entity\MoyskladProduct;

/**
 * @var RenderHelper    $this
 * @var array           $groups
 * @var array           $options
 * @var MoyskladProduct $product
 */

/** @var MoyskladProductHelper $productHelper */
$productHelper = $this->hyper['helper']['moyskladProduct'];
/** @var CartHelper $cartHelper */
$cartHelper = $this->hyper['helper']['cart'];

$this->hyper['helper']['assets']
    ->js('js:widget/site/product-specification.js')
    ->widget('body', 'HyperPC.ProductSpecification');
?>

<?php foreach ($products as $product) :
    $category = $this->hyper['helper']['productFolder']->getById($product->category_id);

    if (!$category->id) {
        continue;
    }

    $price = $product->getConfigPrice(true);

    $parts = $productHelper->getTeaserParts($product, 'platform', false, false);
    $platformParts = $productHelper->getSpecificationWithFieldValues($parts);

    $nameWithoutBrand = $product->getNameWithoutBrand();
    $linkToConfigurator = $product->getConfigUrl();

    $imageMaxHeight = 250;
    $imageSrc = $cartHelper->getItemImage($product, 0, $imageMaxHeight);
    ?>
    <div class="hp-product-teaser">
        <div class="uk-card uk-card-small uk-card-default tm-card-bordered">
            <div class="uk-card-media-top uk-background-default">
                <a href="<?= $linkToConfigurator ?>" style="background: url('<?= $imageSrc ?>') 50% 50% no-repeat; background-size: auto 100%"
                    class="jsGoToConfigurator uk-display-block"
                    title="<?= Text::_('COM_HYPERPC_GO_TO_CONFIGURATOR') ?>">
                    <canvas width="<?= $imageMaxHeight ?>" height="<?= $imageMaxHeight ?>"></canvas>
                </a>
            </div>
            <div class="uk-card-body">
                <div class="uk-h5 uk-margin-remove-bottom">
                    <a href="<?= $linkToConfigurator ?>"
                        class="jsGoToConfigurator uk-link-reset"
                        title="<?= Text::_('COM_HYPERPC_GO_TO_CONFIGURATOR') ?>">
                        <?= $nameWithoutBrand ?>
                    </a>
                </div>
                <div>
                    <?= Text::_('COM_HYPERPC_PRICE') ?>
                    <?= Text::sprintf('COM_HYPERPC_STARTS_FROM', $price->text()) ?>
                </div>
                <hr class="uk-margin-small uk-margin-remove-bottom">
                <div>
                    <div class="uk-grid uk-grid-small uk-child-width-1-2">
                        <?php foreach ($platformParts as $groupName => $parts) : ?>
                            <div class="uk-margin-small-top uk-text-small">
                                <div class="uk-text-muted"><?= $groupName ?></div>
                                <div>
                                    <?php foreach ($parts as $part) : ?>
                                        <div><?= $part ?></div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="jsSpecificationButton uk-button uk-button-link uk-text-small uk-link-muted tm-link-dashed uk-margin-small-top"
                        data-itemkey="<?= $product->getItemKey() ?>" data-title="<?= $nameWithoutBrand ?>">
                        <?= Text::_('COM_HYPERPC_CONFIGURATOR_FULL_SPECIFICATION') ?>
                    </button>
                </div>
            </div>
            <div class="uk-card-footer">
                <a href="<?= $linkToConfigurator ?>"
                   class="jsGoToConfigurator uk-width-1-1 uk-button uk-button-default">
                    <?= Text::_('COM_HYPERPC_CONFIGURATE_AND_BUY') ?>
                </a>
            </div>
        </div>
    </div>
<?php endforeach;
