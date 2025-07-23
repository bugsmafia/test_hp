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
use HYPERPC\Joomla\Model\Entity\Interfaces\ProductMarker;

/**
 * @var         int             $currentProductId
 * @var         array           $groups
 * @var         ProductMarker   $product
 * @var         ProductMarker   $complectation
 * @var         RenderHelper    $this
 */

/** @var MoyskladProductHelper $productHelper */
$productHelper = $product->getHelper();
/** @var CartHelper $cartHelper */
$cartHelper = $this->hyper['helper']['cart'];

$price = $complectation->getConfigPrice(true);

$parts         = $productHelper->getTeaserParts($complectation, 'platform', false, false);
$platformParts = $productHelper->getSpecificationWithFieldValues($parts);

$imageMaxHeight = 250;
$imageSrc       = $cartHelper->getItemImage($complectation, 0, $imageMaxHeight);

$selected      = $complectation->id === $product->id;
$selectedClass = $selected ? ' hp-configurator-complectation--current' : '';
$href          = $complectation->getConfigUrl(0, 'default');
$itemKey       = $complectation->getItemkey();

$complectationName = $complectation->getNameWithoutBrand();
?>

<li class="hp-configurator-complectation<?= $selectedClass ?>"<?= !$selected ? ' data-href="' . $href . '"' : ' hidden' ?>>
    <div class="uk-card uk-card-small uk-card-default tm-card-bordered">
        <div class="hp-configurator-complectation__image uk-card-media-top uk-background-default uk-text-center">
            <div class="uk-display-inline-block uk-background-cover" style="background-image: url('<?= $imageSrc ?>');">
                <canvas width="<?= $imageMaxHeight ?>" height="<?= $imageMaxHeight ?>"></canvas>
            </div>
            <input type="radio" name="complectation"<?= $selected ? ' hidden checked disabled' : '' ?>
                id="complectation-<?= $complectation->id ?>" value="<?= !$selected ? $href : '' ?>"
                class="hp-configurator-complectation__checkbox uk-checkbox uk-position-top-left uk-position-small">
            <label for="complectation-<?= $complectation->id ?>" class="uk-position-cover">
        </div>
        <div class="uk-card-body">
            <div class="hp-configurator-complectation__name uk-h5 uk-margin-remove-bottom">
                <label for="complectation-<?= $complectation->id ?>">
                    <?= $complectationName ?>
                </label>
            </div>
            <div>
                <?= Text::_('COM_HYPERPC_PRICE') ?>
                <?= Text::sprintf('COM_HYPERPC_STARTS_FROM', $price->text()) ?>
            </div>
            <hr class="uk-margin-small uk-margin-remove-bottom">
            <div class="uk-flex uk-flex-column uk-flex-between">
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
                </div>
            </div>
        </div>
        <div class="uk-card-footer">
            <button type="button" class="jsSpecificationButton uk-button uk-button-link uk-text-small uk-link-muted tm-link-dashed"
                    data-itemkey="<?= $itemKey ?>" data-title="<?= $complectationName ?>">
                <?= Text::_('COM_HYPERPC_CONFIGURATOR_FULL_SPECIFICATION') ?>
            </button>
        </div>
    </div>
</li>
