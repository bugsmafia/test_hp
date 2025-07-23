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

use JBZoo\Data\Data;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use HYPERPC\Helper\RenderHelper;
use HYPERPC\Helper\CompareHelper;
use HYPERPC\Helper\ConfiguratorHelper;
use HYPERPC\Joomla\Model\Entity\Position;
use HYPERPC\Joomla\Model\Entity\MoyskladService;
use HYPERPC\Joomla\Model\Entity\Interfaces\PartMarker;
use HYPERPC\Joomla\Model\Entity\Interfaces\OptionMarker;
use HYPERPC\Joomla\Model\Entity\Interfaces\ProductMarker;
use HYPERPC\Joomla\Model\Entity\Interfaces\CategoryMarker;

/**
 * @var         RenderHelper                $this
 * @var         PartMarker|MoyskladService  $part
 * @var         CategoryMarker              $group
 * @var         OptionMarker                $option
 * @var         Data                        $layout
 * @var         ProductMarker               $product
 * @var         bool                        $required
 * @var         int                         $groupTotal
 * @var         array                       $compareItems
 * @var         bool                        $partIsMultiply
 * @var         bool                        $checkOptionBinding
 * @var         bool                        $divideByAvailability
 * @var         int[]                       $compatibilityFieldIds
 */

/** @var ConfiguratorHelper $helper */
$helper = $this->hyper['helper']['configurator'];

$partWidth  = $this->hyper['params']->get('configurator_part_img_width', HP_PART_IMAGE_THUMB_WIDTH);
$partHeight = $this->hyper['params']->get('configurator_part_img_height', HP_PART_IMAGE_THUMB_HEIGHT);

$partName = $part->getConfiguratorName($product->id);
$viewUrl  = $part->getViewUrl();

$isChecked = $helper->isCheckedPart($product, $part->id);

$partWrapperAttrs = [];

$options    = $part instanceof PartMarker ? $product->getPartOptions($part) : [];
$hasOptions = !empty($options);

if ($part instanceof PartMarker && count($part->options) && !$hasOptions) {
    return;
}

$partPrice = $part->getListPrice();

if ($hasOptions) {
    $pickedOption = $product->getDefaultPartOption($part, $part->get('options', []));
    $isChecked = !empty($pickedOption->id) && $helper->isOptionInConfigurator($product, $pickedOption);

    $defaultOption = $part->option;
    $defaultOptionId = $defaultOption->id;

    if ($defaultOption instanceof OptionMarker && !empty($defaultOption->params->get('image', '', 'hpimagepath'))) {
        $image = $defaultOption->getRender()->image($partWidth, $partHeight);
    }
}

$isReloadContent = $part->isReloadContentForProduct($product->id);
if ($isReloadContent) {
    if (!empty($part->getParams()->get('reload_content_desc'))) {
        $viewUrl = $part->getViewUrl(['product_id' => $product->id]);
        $image   = $part->getRender()->image($partWidth, $partHeight, 'hp_part_img', $part->params->get('reload_image'));
    } else {
        $viewUrl = '';
    }
}

if ($divideByAvailability) {
    $onlyInstockByDefault = $helper->inStockOnlyInitState($product);

    $optionsInstock = [];
    $partIsInstock = $part instanceof PartMarker ? $helper->isPartInStock($part, $options, $optionsInstock) : true;

    if ($partIsInstock) {
        $group->set('allPartsOutOfStock', false);
    } else {
        $group->set('hasOutOfStockParts', true);
    }
}

if (isset($group->field) && $group->field !== null && isset($part->fields) && is_array($part->fields)) {
    $filters = [$group->field->name => []];
    $fieldsValues = [];
    foreach ($part->fields as $field) {
        if ($group->field->name === $field->name && !in_array($field->value, $fieldsValues)) {
            $filters[$group->field->name][] = $field->value;
            $fieldsValues[] = $field->value;
        }
    }

    if (!isset($group->field->actualFilters)) {
        $group->field->actualFilters = [];
        $group->field->checkedFilters = [];
        $group->field->visibleFilters = [];
    }

    $group->field->actualFilters = array_merge($group->field->actualFilters, $fieldsValues);
    if (!$divideByAvailability || !$onlyInstockByDefault) {
        $group->field->visibleFilters = $group->field->actualFilters;
    } elseif ($partIsInstock) {
        $group->field->visibleFilters = array_merge($group->field->visibleFilters, $fieldsValues);
    }

    if ($isChecked) {
        $group->field->checkedFilters = array_merge($group->field->checkedFilters, $fieldsValues);
    }

    if ($group->field->type === 'list') {
        $partWrapperAttrs['data-' . $group->field->name] = implode(' ', $filters[$group->field->name]);
    } else {
        if (!empty($filters[$group->field->name])) {
            $partWrapperAttrs['data-' . $group->field->name] = hash('crc32', array_shift($filters[$group->field->name]));
        }
    }
}

$partWrapperAttrs['data'] = [
    'url'   => $viewUrl,
    'id'    => $part->id,
    'group' => $part->getGroupId(),
    'price' => $partPrice->val(),
    'name'  => $partName
];

$partWrapperAttrs['class'] = 'hp-part-wrapper' . ($isChecked ? ' hp-part-checked' : '');

if ($divideByAvailability) {
    $partWrapperAttrs['data-instock'] = $partIsInstock ? 'true' : 'false';
    if (!$partIsInstock && $onlyInstockByDefault) {
        $partWrapperAttrs['class'] .= ' hp-part-wrapper--disabled';
    }
}

if ($isReloadContent) {
    $partWrapperAttrs['data-reloaded'] = '1';
}

$hasProperties = $group->partHasProperties($part->id);

if (!isset($image)) {
    $image = $part->getRender()->image($partWidth, $partHeight);
}

$partImagePath = array_key_exists('thumb', $image) ? '/' . ltrim($image['thumb']->getPath(), '/') : '';

$partWrapperAttrs['data-detached'] = $part->isDetached() ? 'true' : false;

$increseDaysToBuild = $part->params->get('increase_days_to_build', 0, 'int');
if ($increseDaysToBuild) {
    $partWrapperAttrs['data-extra-days'] = $increseDaysToBuild;
}

if (!empty($compatibilityFieldIds) && !empty($part->fields)) {
    $compatibilities = [];
    foreach ($part->fields as $field) {
        if (in_array($field->id, $compatibilityFieldIds, true)) {
            $compatibilities[$field->id] = $field->value;
        }
    }

    $partWrapperAttrs['data-compatibilities'] = json_encode($compatibilities);
}

$quantityList    = new Data($product->configuration->get('quantity', []));
$defaultParts    = $product->configuration->get('default', []);
$defPartQuantity = $quantityList->get($part->id, 1);

$iconCompareAdd = $this->hyper['helper']['html']->svgIcon('hpCompareAdd');
$iconInfo = '<svg width="20" height="20" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M12.13,11.59 C11.97,12.84 10.35,14.12 9.1,14.16 C6.17,14.2 9.89,9.46 8.74,8.37 C9.3,8.16 10.62,7.83 10.62,8.81 C10.62,9.63 10.12,10.55 9.88,11.32 C8.66,15.16 12.13,11.15 12.14,11.18 C12.16,11.21 12.16,11.35 12.13,11.59 C12.08,11.95 12.16,11.35 12.13,11.59 L12.13,11.59 Z M11.56,5.67 C11.56,6.67 9.36,7.15 9.36,6.03 C9.36,5 11.56,4.54 11.56,5.67 L11.56,5.67 Z"></path><circle fill="none" stroke="#000" stroke-width="1.1" cx="10" cy="10" r="9"></circle></svg>';
$iconInfoSmall = '<svg width="16" height="16" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M12.13,11.59 C11.97,12.84 10.35,14.12 9.1,14.16 C6.17,14.2 9.89,9.46 8.74,8.37 C9.3,8.16 10.62,7.83 10.62,8.81 C10.62,9.63 10.12,10.55 9.88,11.32 C8.66,15.16 12.13,11.15 12.14,11.18 C12.16,11.21 12.16,11.35 12.13,11.59 C12.08,11.95 12.16,11.35 12.13,11.59 L12.13,11.59 Z M11.56,5.67 C11.56,6.67 9.36,7.15 9.36,6.03 C9.36,5 11.56,4.54 11.56,5.67 L11.56,5.67 Z"></path><circle fill="none" stroke="#000" stroke-width="1.1" cx="10" cy="10" r="9"></circle></svg>';
$iconClose = '<svg width="20" height="20" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill="none" stroke="#000" stroke-width="1.06" d="M16,16 L4,4"></path><path fill="none" stroke="#000" stroke-width="1.06" d="M16,4 L4,16"></path></svg>';
$iconMore = '<svg width="20" height="20" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><circle cx="3" cy="10" r="2"></circle><circle cx="10" cy="10" r="2"></circle><circle cx="17" cy="10" r="2"></circle></svg>';
$iconMoreSmall = '<svg width="16" height="16" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><circle cx="3" cy="10" r="2"></circle><circle cx="10" cy="10" r="2"></circle><circle cx="17" cy="10" r="2"></circle></svg>';
$iconCog = '<svg width="20" height="20" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><circle fill="none" stroke="#000" cx="9.997" cy="10" r="3.31"></circle><path fill="none" stroke="#000" d="M18.488,12.285 L16.205,16.237 C15.322,15.496 14.185,15.281 13.303,15.791 C12.428,16.289 12.047,17.373 12.246,18.5 L7.735,18.5 C7.938,17.374 7.553,16.299 6.684,15.791 C5.801,15.27 4.655,15.492 3.773,16.237 L1.5,12.285 C2.573,11.871 3.317,10.999 3.317,9.991 C3.305,8.98 2.573,8.121 1.5,7.716 L3.765,3.784 C4.645,4.516 5.794,4.738 6.687,4.232 C7.555,3.722 7.939,2.637 7.735,1.5 L12.263,1.5 C12.072,2.637 12.441,3.71 13.314,4.22 C14.206,4.73 15.343,4.516 16.225,3.794 L18.487,7.714 C17.404,8.117 16.661,8.988 16.67,10.009 C16.672,11.018 17.415,11.88 18.488,12.285 L18.488,12.285 Z"></path></svg>';
$iconMark = '<svg width="20" height="20" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><circle cx="10" cy="10" r="4"></circle></svg>';

$advantages = $part->getAdvantages();
if (count($advantages)) {
    $partWrapperAttrs['data-advantages'] = json_encode($advantages);
}

$compareType = $part instanceof Position ? CompareHelper::TYPE_POSITION : CompareHelper::TYPE_PART;
?>

<li <?= $this->hyper['helper']['html']->buildAttrs($partWrapperAttrs) ?>>

    <div class="hp-configurator-part uk-grid uk-grid-collapse uk-flex-between"
        data-image="<?= $partImagePath ?>">
        <?php if (array_key_exists('thumb', $image) && $layout->get('default') === 'column' && !$isMobile) : ?>
            <div class="hp-conf-part__image">
                <img src="<?= $partImagePath ?>" alt="<?= $partName ?>" width="<?= $image['thumb']->getWidth() ?>" height="<?= $image['thumb']->getHeight() ?>" loading="lazy" />
            </div>
        <?php endif; ?>

        <div class="uk-width-1-1 uk-width-auto@s uk-flex uk-flex-between uk-flex-left@s">
            <div class="uk-text-truncate">
                <?php if ($partIsMultiply) : ?>
                    <label class="hp-input-wrapper">
                        <input class="jsPartInput uk-checkbox" type="checkbox" name="parts[<?= $group->id ?>][]"
                            value="<?= $part->id ?>" <?= ($isChecked) ? 'checked' : '' ?>/>
                    </label>
                <?php else : ?>
                    <label>
                        <input class="jsPartInput uk-radio" type="radio" name="parts[<?= $group->id ?>]"
                            value="<?= $part->id ?>" <?= ($isChecked) ? 'checked' : '' ?>/>
                    </label>
                <?php endif; ?>

                <?php if ($divideByAvailability) :
                    $hiddenClass = $isChecked && $hasOptions ? ' uk-hidden' : '';
                    ?>
                    <span class="hp-conf-part__availability uk-icon<?= $hiddenClass ?>"><?= $iconMark ?></span>
                <?php endif; ?>

                <?php
                $hasQuantity = $part->params->get('enable_quantity', 0, 'bool');
                if ($hasQuantity) {
                    $quantityOptionsEls = $helper->groupQuantityOptions($product, $group);

                    if (count($quantityOptionsEls)) {
                        $quantityAttrs = [
                            'data-default'  => $quantityList->get($part->id, 1),
                            'title'         => Text::_('COM_HYPERPC_CONFIGURATOR_CHOSE_QUANTITY_PART'),
                            'class'         => 'jsPartQuantity jsPreventCheck hp-conf-part__quantity uk-select uk-form-small'
                        ];

                        if (in_array((string) $part->id, $defaultParts) && !in_array($defPartQuantity, $quantityOptionsEls)) {
                            $quantityOptionsEls[$defPartQuantity] = (int) $defPartQuantity;

                            ksort($quantityOptionsEls);
                        }

                        echo HTMLHelper::_(
                            'select.genericlist',
                            $quantityOptionsEls,
                            'part_quantity[' . $part->id. ']',
                            $quantityAttrs,
                            null,
                            null,
                            $defPartQuantity,
                            null
                        );
                    }
                }
                ?>

                <span class="hp-conf-part__name"><?= $partName ?></span>
            </div>

            <?php if (!$isMobile) : ?>
                <div>
                    <div class="jsPreventCheck hp-conf-part__buttons uk-flex uk-visible@s uk-link-muted">

                        <?php if ($hasOptions && !$isReloadContent) : ?>
                            <a class="jsOptionToggle uk-icon uk-margin-small-left<?= $isChecked ? ' uk-hidden' : '' ?>" uk-toggle="target: .options-<?= $part->id ?>" title="<?= $part->getOptionBtnTitle() ?>" >
                                <?= $iconCog ?>
                            </a>
                        <?php else : ?>
                            <?php if ($group->getParams()->get('configurator_show_part_info', true, 'bool')) : ?>
                                <?php if (!$isReloadContent || ($isReloadContent && !empty($part->getParams()->get('reload_content_desc')))) : ?>
                                    <span class="uk-margin-small-left">
                                        <a href="<?= $viewUrl ?>" class="jsLoadIframe uk-icon"
                                        title="<?= Text::_('COM_HYPERPC_CONFIGURATOR_PART_INFO') ?>">
                                            <?= $iconInfo ?>
                                        </a>
                                    </span>
                                <?php endif; ?>
                            <?php endif; ?>
                        <?php endif; ?>
                        <?php if ($hasProperties && !$isReloadContent) : ?>
                            <?php if (!$hasOptions) :
                                $isInCompare = array_key_exists($part->id, $compareItems);
                                $linkTitle   = $isInCompare ? 'COM_HYPERPC_CONFIGURATOR_COMPARE_REMOVE' : 'COM_HYPERPC_CONFIGURATOR_COMPARE_ADD';

                                $compareBtnAttr = [
                                    'class' => 'jsCompareAdd hp-compare-btn' . ($isInCompare ? ' inCompare' : ''),
                                    'title' => Text::_($linkTitle),
                                    'data'  => [
                                        'id'   => $part->id,
                                        'type' => $compareType,
                                        'itemkey' => $part->getItemKey()
                                    ]
                                ]
                                ?>
                                <span class="uk-margin-small-left">
                                    <a <?= $this->hyper['helper']['html']->buildAttrs($compareBtnAttr) ?>>
                                        <span class="uk-icon"><?= $iconCompareAdd ?></span>
                                    </a>
                                </span>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php if (!$partIsMultiply && !$required) : ?>
                            <span class="jsUnsetPart uk-icon uk-text-danger uk-visible@s <?= !$isChecked ? 'uk-hidden' : '' ?>"
                                title="<?= Text::_('COM_HYPERPC_CONFIGURATOR_PART_UNSET') ?>">
                                <?= $iconClose ?>
                            </span>
                        <?php endif; ?>

                    </div>
                </div>
            <?php endif; ?>

            <div <?= !$isMobile ? ' class="uk-hidden@s"' : '' ?>>
                <?php
                $reloadedContent = trim($part->getParams()->get('reload_content_desc', ''));

                $needInfoBtn = $group->params->get('configurator_show_part_info', true, 'bool') && (!$isReloadContent || !empty($reloadedContent));
                $needCompareBtn = !$hasOptions && $hasProperties && !$isReloadContent;
                $needOptionsBtn = $hasOptions && !$isReloadContent;
                $needUnsetBtn = !$required && !$partIsMultiply;

                $needMoreBtn = $needInfoBtn || $needOptionsBtn || $needCompareBtn || $needUnsetBtn;

                $hiddenClass = $hasOptions && $isChecked && !$needUnsetBtn ? ' uk-hidden' : '';
                if ($isReloadContent) {
                    if (empty($reloadedContent) && ($needUnsetBtn && !$isChecked)) {
                        $hiddenClass = ' uk-hidden';
                    }
                }

                if ($needMoreBtn) : ?>
                    <span class="jsPreventCheck hp-conf-part__more-btn uk-icon<?= $hiddenClass ?>">
                        <?= $iconMore ?>
                    </span>
                    <div class="jsPreventCheck uk-dropdown" uk-dropdown="mode: click; offset: -18; pos: bottom-right">
                        <ul class="uk-nav uk-dropdown-nav tm-dropdown-nav-iconnav">
                            <?php if ($divideByAvailability) :
                                $availabilityText = $partIsInstock ?
                                    Text::_('COM_HYPERPC_CONFIGURATOR_PART_AVAILABILITY_TEXT_INSTOCK') :
                                    Text::_('COM_HYPERPC_CONFIGURATOR_PART_AVAILABILITY_TEXT_PREORDER');
                                ?>
                                <li class="uk-margin-small"><?= $availabilityText ?></li>
                                <li class="uk-nav-divider"></li>
                            <?php endif; ?>
                            <?php if ($needOptionsBtn) : ?>
                                <li>
                                    <a class="jsOptionToggle <?= $isChecked ? ' uk-hidden' : '' ?>" href="#" uk-toggle="target: .options-<?= $part->id ?>">
                                        <span class="uk-icon">
                                            <?= $iconCog ?>
                                        </span>
                                        <?= $part->getOptionBtnTitle() ?>
                                    </a>
                                </li>
                            <?php elseif ($needInfoBtn) : ?>
                                <li>
                                    <a href="<?= $viewUrl ?>" class="jsLoadIframe">
                                        <span class="uk-icon">
                                            <?= $iconInfo ?>
                                        </span>
                                        <?= Text::_('COM_HYPERPC_CONFIGURATOR_PART_INFO') ?>
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php if ($needCompareBtn) :
                                $isInCompare = array_key_exists($part->id, $compareItems);
                                $linkTitle   = $isInCompare ? 'COM_HYPERPC_CONFIGURATOR_COMPARE_REMOVE' : 'COM_HYPERPC_CONFIGURATOR_COMPARE_ADD';
                                $linkText    = $isInCompare ? 'COM_HYPERPC_COMPARE_REMOVE_BTN_TEXT' : 'COM_HYPERPC_COMPARE_ADD_BTN_TEXT';

                                $compareBtnAttr = [
                                    'class' => 'jsCompareAdd hp-compare-btn' . ($isInCompare ? ' inCompare' : ''),
                                    'data'  => [
                                        'id'   => $part->id,
                                        'type' => $compareType,
                                        'itemkey' => $part->getItemKey()
                                    ]
                                ];
                                ?>
                                <li>
                                    <a <?= $this->hyper['helper']['html']->buildAttrs($compareBtnAttr) ?>>
                                        <span class="uk-icon">
                                            <?= $iconCompareAdd ?>
                                        </span>
                                        <span class="hp-compare-btn-text">
                                            <?= Text::_($linkText) ?>
                                        </span>
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php if ($needUnsetBtn) : ?>
                                <li>
                                    <a class="jsUnsetPart<?= !$isChecked ? ' uk-hidden' : '' ?>">
                                        <span class="uk-icon uk-text-danger">
                                            <?= $iconClose ?>
                                        </span>
                                        <?= Text::_('COM_HYPERPC_CONFIGURATOR_PART_UNSET') ?>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div>
            <div class="hp-configurator-part-price">
                <?php if (!$isChecked) :
                    $priceDiff = $partPrice->multiply($defPartQuantity, true);
                    if (!$partIsMultiply) {
                        $priceDiff->add('-' . $groupTotal);
                    }
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
            </div>
        </div>

        <?php if ($hasOptions) :
            $optionsWrapperClass = 'options-' . $part->id . ' hp-part-options uk-width-1-1 uk-text-muted';
            $optionsWrapperClass .= ($part->isReloadContentForProduct($product->id)) ? ' uk-hidden' : '';
            ?>
            <div class="<?= $optionsWrapperClass ?>"<?= !$isChecked ? ' hidden' : '' ?>
                data-default-option="<?= $defaultOptionId ?>">
                <?php foreach ($options as $option) :
                    if ($option->isOutOfStock()) {
                        continue;
                    }

                    $isDefault   = false;
                    $optionPrice = $option->getPrice()->add('-' . $partPrice->val());

                    if ($option->id === $pickedOption->id) {
                        $isDefault = true;
                    }

                    $attrs = [
                        'value' => $option->id,
                        'type'  => 'radio',
                        'class' => 'uk-radio jsOptionInput',
                        'name'  => 'options[' . $part->id . ']'
                    ];

                    if ($isChecked && $isDefault) {
                        $attrs['checked'] = 'checked';
                    }

                    $linkAttrs = [
                        'class'     => 'jsPreventCheck jsLoadIframe uk-icon',
                        'href'      => $option->getViewUrl(),
                        'title'     => $partName . ' ' . $option->name
                    ];

                    $optionImagePath = $option->params->get('image', '', 'hpimagepath');
                    if (!$isReloadContent && !$isDefault && !empty($optionImagePath)) {
                        $optionImage = $option->getRender()->image($partWidth, $partHeight);
                    } elseif (empty($optionImagePath)) {
                        $optionImage = $part->getRender()->image($partWidth, $partHeight);
                    } else {
                        $optionImage = $image;
                    }

                    if ($hasProperties) {
                        $itemCompareKey = $this->hyper['helper']['compare']->getItemKey(['itemId' => $part->id, 'optionId' => $option->id]);
                        $isInCompare    = array_key_exists($itemCompareKey, $compareItems);
                        $linkTitle      = $isInCompare ? 'COM_HYPERPC_CONFIGURATOR_COMPARE_REMOVE' : 'COM_HYPERPC_CONFIGURATOR_COMPARE_ADD';
                        $linkText       = $isInCompare ? 'COM_HYPERPC_COMPARE_REMOVE_BTN_TEXT' : 'COM_HYPERPC_COMPARE_ADD_BTN_TEXT';

                        $compareBtnAttr = [
                            'id'      => 'hp-compare-option-' . $option->id,
                            'class'   => 'jsCompareAdd hp-compare-btn jsPreventCheck' . ($isInCompare ? ' inCompare' : ''),
                            'title'   => $isMobile ? '' : Text::_($linkTitle),
                            'data'    => [
                                'id'        => $part->id,
                                'option-id' => $option->id,
                                'type'      => $compareType,
                                'itemkey'   => $option->getItemKey()
                            ]
                        ];
                    }

                    $needInfoBtn    = $group->params->get('configurator_show_part_info', true, 'bool');
                    $needCompareBtn = $hasProperties;

                    $optionWrapperAttrs = [
                        'class' => 'hp-option' . ($isChecked && $isDefault ? ' hp-option-checked' : ''),
                        'data'  => [
                            'name' => $option->name,
                            'price' => $optionPrice->val(),
                            'image' => array_key_exists('thumb', $optionImage) ? '/' . ltrim($optionImage['thumb']->getPath(), '/') : ''
                        ]
                    ];

                    $optionIsInStock = null;
                    if ($divideByAvailability && $partIsInstock) {
                        $optionIsInStock = in_array($option->id, $optionsInstock);
                        $optionWrapperAttrs['data-instock'] = $optionIsInStock ? 'true' : 'false';
                        if (!$optionIsInStock) {
                            $group->set('hasOutOfStockParts', true);
                            if ($onlyInstockByDefault) {
                                $optionWrapperAttrs['class'] .= ' hp-option--disabled';
                            }
                        }
                    }
                    ?>
                    <div <?= $this->hyper['helper']['html']->buildAttrs($optionWrapperAttrs) ?>>
                        <div>
                            <input <?= $this->hyper['helper']['html']->buildAttrs($attrs) ?>>

                            <?php if ($divideByAvailability) : ?>
                                <span class="hp-option__availability uk-icon"><?= $iconMark ?></span>
                            <?php endif; ?>

                            <span class="uk-link-muted">
                                <span class="hp-option__name">
                                    <?= $option->getConfigurationName() ?>
                                </span>

                                <?php if (!$isMobile) : ?>
                                    <?php if ($needInfoBtn) : ?>
                                        <a <?= $this->hyper['helper']['html']->buildAttrs($linkAttrs) ?>>
                                            <?= $iconInfoSmall ?>
                                        </a>
                                    <?php endif; ?>

                                    <?php if ($needCompareBtn) : ?>
                                        <a <?= $this->hyper['helper']['html']->buildAttrs($compareBtnAttr) ?>>
                                            <span class="uk-icon">
                                                <?= $iconCompareAdd ?>
                                            </span>
                                        </a>
                                    <?php endif; ?>
                                <?php endif; ?>

                            </span>

                        </div>

                        <em class="hp-configurator-option-price uk-text-nowrap uk-text-small">
                            <span class="jsOptionPrice">
                                <?php if (!$isDefault) :
                                    $priceDiff = $option->getSalePrice()->multiply($defPartQuantity, true);
                                    if (!$partIsMultiply) {
                                        $priceDiff->add('-' . $groupTotal);
                                    }
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
                                        (<?= $priceDiffText ?>)
                                    <?php endif; ?>
                                <?php endif; ?>
                            </span>
                        </em>

                        <?php if ($isMobile) :
                            if ($needInfoBtn || $needCompareBtn) : ?>
                                <span class="jsPreventCheck hp-option__more-btn uk-icon">
                                    <?= $iconMoreSmall ?>
                                </span>
                                <div class="jsPreventCheck uk-dropdown" uk-dropdown="mode: click; offset: 5; pos: bottom-right">
                                    <ul class="uk-nav uk-dropdown-nav tm-dropdown-nav-iconnav">
                                        <?php if ($divideByAvailability) :
                                            $availabilityText = $optionIsInStock ?
                                                Text::_('COM_HYPERPC_CONFIGURATOR_PART_AVAILABILITY_TEXT_INSTOCK') :
                                                Text::_('COM_HYPERPC_CONFIGURATOR_PART_AVAILABILITY_TEXT_PREORDER');
                                            ?>
                                            <li class="uk-margin-small"><?= $availabilityText ?></li>
                                            <li class="uk-nav-divider"></li>
                                        <?php endif; ?>
                                        <?php if ($needInfoBtn) : ?>
                                            <li>
                                                <a href="<?= $option->getViewUrl() ?>" class="jsLoadIframe">
                                                    <span class="uk-icon">
                                                        <?= $iconInfo ?>
                                                    </span>
                                                    <?= Text::_('COM_HYPERPC_CONFIGURATOR_PART_INFO') ?>
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                        <?php if ($needCompareBtn) : ?>
                                            <li>
                                                <a <?= $this->hyper['helper']['html']->buildAttrs($compareBtnAttr) ?>>
                                                    <span class="uk-icon">
                                                        <?= $iconCompareAdd ?>
                                                    </span>
                                                    <span class="hp-compare-btn-text">
                                                        <?= Text::_($linkText) ?>
                                                    </span>
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</li>
