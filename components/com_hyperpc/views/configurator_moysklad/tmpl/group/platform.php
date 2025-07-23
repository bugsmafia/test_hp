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
use HYPERPC\Helper\ConfiguratorHelper;
use HYPERPC\Helper\MoyskladProductHelper;
use HYPERPC\Joomla\Model\Entity\Interfaces\PartMarker;
use HYPERPC\Joomla\Model\Entity\Interfaces\ProductMarker;

/**
 * @var         array           $groupList
 * @var         array           $productParts
 * @var         array           $complectations
 * @var         ProductMarker   $product
 * @var         RenderHelper    $this
 */

/** @var ConfiguratorHelper $configuratorHelper */
$configuratorHelper = $this->hyper['helper']['configurator'];
/** @var MoyskladProductHelper $productHelper */
$productHelper = $product->getHelper();

$imageGroupIds = $configuratorHelper->getImageGroupIds($product, $groupList, $productParts);
$imageSrc = $product->getConfigurationImagePath(305, 171);

$partsFromConfig = false;
if ($product->saved_configuration) {
    $partsFromConfig = true;
}
$parts = $productHelper->getTeaserParts($product, 'large', $partsFromConfig, false);

$comlectationParts = [];
foreach ($parts as $groupId => $groupParts) {
    if (!empty($groupParts)) {
        $comlectationParts[$groupId] = [];
    }

    foreach ($groupParts as $part) {
        $partData = [
            'id'       => $part->id,
            'name'     => $part->getConfiguratorName($product->id),
            'quantity' => $part->quantity
        ];

        $isReloadContentForProduct = $part->isReloadContentForProduct($product->id);
        if (!$isReloadContentForProduct && $part instanceof PartMarker && $part->option?->id) {
            $partData['optionName'] = $part->option->getConfigurationName();
        }

        $comlectationParts[$groupId][] = $partData;
    }
}

$hasComplectations = count($complectations);
?>

<div class="uk-grid uk-flex-between" uk-margin>
    <div class="uk-h4 uk-text-normal uk-margin-remove">
        <?= $product->getNameWithoutBrand(); ?>
    </div>
    <?php if ($hasComplectations) : ?>
        <div>
            <button type="button" class="uk-button uk-button-small uk-button-default" uk-toggle="#change-platform-modal">
                <span class="uk-icon uk-text-middle" uk-icon="icon: refresh; ratio:0.75" style="margin-inline-end: 5px"></span>
                <?= Text::_('COM_HYPERPC_CHANGE_PLATFORM') ?>
            </button>
        </div>
    <?php endif; ?>
</div>

<hr class="uk-margin-small">

<div class="uk-grid uk-grid-collapse">
    <div class="uk-width-1-1 uk-width-auto@xl">
        <div class="jsBoxCaseImg uk-text-center uk-margin-top uk-margin-auto" data-group='<?= json_encode($imageGroupIds) ?>'>
            <img src="<?= $imageSrc ?>" alt="" style="max-height: 171px">
        </div>
    </div>
    <div class="uk-width-expand">
        <div class="jsPlatformConfiguration hp-configurator-platform-parts uk-grid uk-grid-collapse uk-child-width-1-1 uk-child-width-1-2@s uk-grid-match uk-margin-small-top" uk-grid>
            <?php foreach ($comlectationParts as $groupId => $parts) :
                $group = $groupList[$groupId];
                ?>
                <div class="" data-group="<?= $groupId ?>">
                    <div class="">
                        <div class="uk-text-small">
                            <div class="uk-text-muted"><?= $group->title ?></div>
                            <div class="jsPlatformConfigurationGroupParts">
                                <?php foreach ($parts as $partData) : ?>
                                    <div>
                                        <?= $partData['quantity'] > 1 ? $partData['quantity'] . ' x ' : '' ?>
                                        <?= $partData['name'] ?>
                                        <?php if (isset($partData['optionName'])) : ?>
                                            <span class="uk-text-nowrap">
                                                <?= Text::sprintf('COM_HYPERPC_PRODUCT_OPTION', $partData['optionName']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="uk-text-center uk-text-small uk-margin-small-top uk-margin-small-bottom">
            <button type="button" class="jsShowFullSpecs uk-button uk-button-link uk-link-muted tm-link-dashed">
                <?= Text::_('COM_HYPERPC_CONFIGURATOR_FULL_SPECIFICATION') ?>
            </button>
        </div>
    </div>
</div>
