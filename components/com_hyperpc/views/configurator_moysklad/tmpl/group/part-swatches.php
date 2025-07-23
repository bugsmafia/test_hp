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
 * @author      Sergey Kalistratov <kalistratov.s.m@gmail.com>
 */

defined('_JEXEC') or die('Restricted access');

use HYPERPC\Helper\RenderHelper;
use HYPERPC\Joomla\Model\Entity\ProductFolder;
use HYPERPC\Joomla\Model\Entity\Interfaces\PartMarker;
use HYPERPC\Joomla\Model\Entity\Interfaces\OptionMarker;
use HYPERPC\Joomla\Model\Entity\Interfaces\ProductMarker;

/**
 * @var         RenderHelper    $this
 * @var         PartMarker      $part
 * @var         ProductFolder   $group
 * @var         OptionMarker    $option
 * @var         ProductMarker   $product
 * @var         bool            $isChecked
 * @var         int             $groupTotal
 */

/** @var HYPERPC\Helper\ConfiguratorHelper $helper */
$helper = $this->hyper['helper']['configurator'];

$imgWidth  = 901;//$this->hyper['params']->get('configurator_part_img_width', HP_PART_IMAGE_THUMB_WIDTH);
$imgHeight = 0;

$partName = $part->getConfiguratorName($product->id);

$isChecked = $helper->isCheckedPart($product, $part->id);

$partWrapperAttrs = [];

$options    = $product->getPartOptions($part);
$hasOptions = !empty($options);

if (count($part->options) && !$hasOptions) {
    return;
}

$partPrice = $part->getListPrice();

if ($hasOptions) {
    $pickedOption = $product->getDefaultPartOption($part, $part->get('options', []));
    $isChecked = !empty($pickedOption->id) && $helper->isOptionInConfigurator($product, $pickedOption);

    $defaultOption = $part->option;
    $defaultOptionId = $defaultOption->id;
}

$partWrapperAttrs['data'] = [
    'id'    => $part->id,
    'group' => $part->getGroupId(),
    'price' => $partPrice->val(),
    'name'  => $partName
];

$partWrapperAttrs['class'] = 'hp-part-wrapper hp-part-swatches' . ($isChecked ? ' hp-part-checked' : '');

if (!empty($part->description)) {
    $partWrapperAttrs['data-url'] = $part->getViewUrl();
}

$increseDaysToBuild = $part->params->get('increase_days_to_build', 0, 'int');
if ($increseDaysToBuild) {
    $partWrapperAttrs['data-extra-days'] = $increseDaysToBuild;
}

$iconInfoSmall = '<svg width="18" height="18" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M12.13,11.59 C11.97,12.84 10.35,14.12 9.1,14.16 C6.17,14.2 9.89,9.46 8.74,8.37 C9.3,8.16 10.62,7.83 10.62,8.81 C10.62,9.63 10.12,10.55 9.88,11.32 C8.66,15.16 12.13,11.15 12.14,11.18 C12.16,11.21 12.16,11.35 12.13,11.59 C12.08,11.95 12.16,11.35 12.13,11.59 L12.13,11.59 Z M11.56,5.67 C11.56,6.67 9.36,7.15 9.36,6.03 C9.36,5 11.56,4.54 11.56,5.67 L11.56,5.67 Z"></path><circle fill="none" stroke="#000" stroke-width="1.1" cx="10" cy="10" r="9"></circle></svg>';
?>
<li <?= $this->hyper['helper']['html']->buildAttrs($partWrapperAttrs) ?>>
    <div class="hp-configurator-part" data-image>
        <div class="uk-hidden">
            <input class="jsPartInput uk-radio" type="radio" name="parts[<?= $group->id ?>]"
                    value="<?= $part->id ?>" <?= $isChecked ? 'checked' : '' ?>>
        </div>
        <div class="uk-margin-small-bottom">
            <span class="tm-text-medium uk-text-emphasis uk-margin-small-right"><?= $partName ?></span>
            <span class="hp-configurator-part-price uk-text-nowrap">
                <?php if (!$isChecked) :
                    $priceDiff = clone $partPrice;
                    $priceDiff->add('-' . $groupTotal);
                    ?>
                    <?php if (!$priceDiff->isEmpty()) :
                        $sign = $priceDiff->isPositive() ? '+' : '-';
                        $ruleData = $priceDiff->getRuleData($priceDiff->getRule());
                        $priceDiffText = str_replace(
                            ['-', '%v', '%s'],
                            [$sign, $priceDiff->abs()->text(), ''],
                            $ruleData['format_negative']
                        );
                        ?>
                        <?= $priceDiffText ?>
                    <?php endif; ?>
                <?php endif; ?>
            </span>
        </div>

        <?php if ($hasOptions) : ?>
            <div class="options-<?= $part->id ?> hp-part-options uk-flex uk-flex-wrap uk-flex-top"
                 uk-margin="margin: uk-margin-top" data-default-option="<?= $defaultOption->id ?>">
                <?php foreach ($options as $option) :
                    if ($option->isOutOfStock()) {
                        continue;
                    }

                    $isDefault   = false;
                    $optionPrice = $option->getSalePrice()->add('-' . $partPrice->val());

                    if ($isChecked && $option->id === $defaultOption->id) {
                        $isDefault = true;
                    }

                    $attrs = [
                        'value' => $option->id,
                        'type'  => 'radio',
                        'class' => 'uk-radio jsOptionInput tm-radio-check',
                        'name'  => 'options[' . $part->id . ']'
                    ];

                    if ($isDefault) {
                        $attrs['checked'] = 'checked';
                    }

                    $linkAttrs = [
                        'class'     => 'jsLoadIframe uk-icon',
                        'href'      => $option->getViewUrl(),
                        'title'     => $partName . ' ' . $option->name
                    ];

                    $optionImage = $option->getRender()->image($imgWidth, $imgHeight);
                    $swatchImage = $option->params->get('mini_image') ? $option->params->get('mini_image') : 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAFAAAAAoAQAAAABBG0CsAAAAAnRSTlMAAHaTzTgAAAANSURBVHgBYxgFQxIAAAG4AAHd2GmkAAAAAElFTkSuQmCC';
                    ?>
                    <div class="hp-option hp-option-swatch uk-margin-right<?= $isDefault ? ' hp-option-checked' : '' ?>"
                         data-name="<?= $option->name ?>"
                         data-price="<?= $optionPrice->val() ?>"
                         data-image="<?= array_key_exists('thumb', $optionImage) ? '/' . ltrim($optionImage['thumb']->getPath(), '/') : '' ?>"
                         title="<?= $option->getConfigurationName() ?>"
                         >
                        <div>
                            <div class="hp-option-swatch-img-wrapper uk-position-relative">
                                <img src="<?= $swatchImage ?>" alt="" width="80">
                            </div>
                            <div class="hp-option-swatch-name">
                                <?php if (strpos($option->alias, 'ral') !== false) : ?>
                                    <?= strtoupper(str_replace('-', ' ', $option->alias)) ?>
                                <?php else : ?>
                                    <?= $option->name ?>
                                <?php endif; ?>
                            </div>
                        </div>
    
                        <div class="hp-option-swatch-input-wrapper">
                            <input <?= $this->hyper['helper']['html']->buildAttrs($attrs) ?>>
                        </div>
    
                        <?php if (!empty($part->description) || count($part->review) || !empty($option->description) || count($option->review)) : ?>
                            <div class="hp-option-swatch-info-wrapper jsPreventCheck">
                                <span class="uk-link-muted">
                                    <a <?= $this->hyper['helper']['html']->buildAttrs($linkAttrs) ?>>
                                        <?= $iconInfoSmall ?>
                                    </a>
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</li>
