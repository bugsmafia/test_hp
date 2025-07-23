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

use JBZoo\Image\Image;
use Joomla\CMS\Language\Text;
use HYPERPC\Joomla\Model\Entity\MoyskladPart;
use HYPERPC\Joomla\Model\Entity\Interfaces\PartMarker;
use HYPERPC\Joomla\Model\Entity\Interfaces\ProductMarker;
use HYPERPC\Joomla\Model\Entity\Interfaces\CategoryMarker;

/**
 * @var         PartMarker          $part
 * @var         CategoryMarker      $group
 * @var         ProductMarker       $product
 * @var         string              $hiddenCls
 * @var         string              $hiddenAttr
 * @var         bool                $hasChangeParts
 */

$partName = $part->getConfiguratorName($product->id, true);
$viewLink = $part->getViewUrl();

$optionId = 0;
if (isset($part->option) && $part->option !== null) {
    $optionId = $part->option->id;
    if (!empty($part->option->getParams()->get('short_desc'))) {
        $part->params->set('short_desc', $part->option->getParams()->get('short_desc'));
    }

    if ($part instanceof MoyskladPart && !empty($part->option->images->get('image', '', 'hpimagepath'))) {
        $part->images->set('image', $part->option->images->get('image', '', 'hpimagepath'));
    } elseif (!empty($part->option->params->get('image', '', 'hpimagepath'))) {
        $part->set('image', $part->option->params->get('image', '', 'hpimagepath'));
    }

    $viewLink = $part->option->getViewUrl();
}

$quantity = $part->get('quantity', 1, 'int');

$isSetImage = false;
$partImage  = (array) $part->getRender()->image(780, 439);

if (key_exists('thumb', $partImage) && $partImage['thumb'] instanceof Image) {
    $isSetImage = !($this->hyper['helper']['image']->isPlaceholder($partImage['thumb']->getPath()));
}

if ($isSetImage) {
    $imgAttrs  = [
        'title'  => '',
        'style'  => '',
        'alt'    => $part->name,
        'class'  => 'hp-equipment-part__img',
        'src'    => $partImage['thumb']->getPath(),
        'width'  => $partImage['thumb']->getWidth(),
        'height' => $partImage['thumb']->getHeight()
    ];
}

$task = 'moysklad_product.display-group-configurator';

$groupChangePartUrl = $this->hyper['route']->build([
    'd_pid'     => $part->id,
    'd_oid'     => $optionId,
    'tmpl'      => 'component',
    'folder_id' => $group->get('id'),
    'id'        => $product->id,
    'task'      => $task
]);

$description = strip_tags($part->getParams()->get('short_desc'));

$defaultItemKey = $part->getItemKey();

$rowClassPrefix = $part->getFolderId();
if ($hasChangeParts) {
    $rowClassPrefix .= ' jsCanBeChanged';

    $this->defaultPartsData[$defaultItemKey] = [
        'part_id'    => $part->id,
        'option_id'  => $optionId,
        'url_view'   => $viewLink,
        'name'       => $partName,
        'desc'       => $description,
        'url_change' => $groupChangePartUrl,
        'image'      => $isSetImage ? '/' . ltrim($partImage['thumb']->getPath(), '/') : '',
        'folder_id'  => $group->get('id'),
        'advantages' => $part->getAdvantages()
    ];
}
?>
<div class="hp-group-row-<?= $rowClassPrefix ?> hp-equipment-part<?= $hiddenCls ?>"<?= $hiddenAttr ?> data-id="<?= $part->id ?>"<?= $hasChangeParts ? ' data-default-itemkey="'. $part->getItemKey() . '"' : '' ?>>
    <div class="uk-container uk-container-large">
        <div class="hp-equipment-part__grid uk-grid uk-child-width-1-2@m uk-flex-middle uk-flex-center">
            <?php if ($isSetImage) : ?>
                <div class="uk-text-center">
                    <img <?= $this->hyper['helper']['html']->buildAttrs($imgAttrs) ?> />
                </div>
            <?php endif; ?>
            <div>
                <div class="uk-section-small uk-position-relative">
                    <span class="uk-text-large"><?= $group->get('title')?></span>
                    <h3 class="hp-equipment-part__name uk-h2 uk-margin-remove-top">
                        <?= $partName ?>
                    </h3>
                    <div class="uk-margin-top hp-equipment-part__desc">
                        <?= $description ?>
                    </div>

                    <?php if ($part->isPublished() || $part->isArchived()) : ?>
                        <div class="uk-margin-small tm-text-medium">
                            <div class="uk-grid uk-grid-small uk-grid-divider">
                                <span class="uk-first-column">
                                    <a href="<?= $viewLink ?>" class="jsItemMoreButton hp-equipment-part__link uk-button-text jsLoadIframe">
                                        <?= Text::_('COM_HYPERPC_DETAILS') ?>
                                    </a>
                                </span>
                                <?php if ($hasChangeParts) : ?>
                                    <span>
                                        <button data-href="<?= $groupChangePartUrl ?>" class="jsItemChangeButton hp-equipment-part__link uk-button-text jsLoadIframe">
                                            <?= Text::_('COM_HYPERPC_PRODUCT_QUICK_CONFIGURATOR_PART_CHANGE') ?>
                                        </button>
                                        <input class="jsGroupPartValue" type="hidden" name="group[<?= $part->getFolderId() ?>][part]" value="<?= $part->id ?>"/>
                                        <input class="jsGroupOptionValue" type="hidden" name="group[<?= $part->getFolderId() ?>][option]" value="<?= $optionId ?>"/>
                                    </span>
                                    <span hidden>
                                        <button class="jsItemResetButton hp-equipment-part__link uk-button-text">
                                            <?= Text::_('COM_HYPERPC_PRODUCT_QUICK_CONFIGURATOR_PART_RESET') ?>
                                        </button>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
</div>
