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
use HYPERPC\Object\MiniConfigurator\ProductPartData;
use HYPERPC\Object\MiniConfigurator\ProductServiceData;

/**
 * @var     RenderHelper $this
 * @var     ProductServiceData[]|ProductPartData[]  $itemsData
 */

$i = 0;
$defaultIndex = 0;
?>

<div class="jsChangeGroupPart uk-slider uk-slider-container uk-margin" style="user-select: none">
    <div class="uk-position-relative uk-container uk-container-expand">
        <ul class="uk-slider-items uk-grid uk-grid-small uk-grid-match">
            <?php foreach ($itemsData as $item) :
                $i++;
                if ($item->isDefault) {
                    $defaultIndex = $i;
                }
                ?>
                <li class="uk-width-5-6 uk-width-3-5@s uk-width-2-5@m uk-width-1-3@l<?= $item->isDefault ? ' jsIsDefault' : '' ?>">
                    <div class="hp-group-slider-part uk-card uk-card-default uk-card-small uk-overflow-hidden uk-flex uk-flex-column">
                        <div class="uk-card-media-top uk-margin-top">
                            <?= $this->hyper['helper']['html']->image($item->image['thumb']->getUrl(), [
                                'fullBase'  => false,
                                'alt'       => $item->name,
                                'width'     => $item->image['thumb']->getWidth(),
                                'height'    => $item->image['thumb']->getHeight(),
                                'style'     => 'filter: contrast(0.89) brightness(1.1)'
                            ]) ?>
                        </div>
                        <div class="uk-card-body uk-width-expand">
                            <div class="hp-group-slider-part__name uk-text-emphasis uk-text-bold tm-margin-16">
                                <?= $item->name ?>
                            </div>
                            <?php if ($item->priceDifference->val() || count($item->fields) || count($item->advantages)) : ?>
                                <ul class="uk-list uk-list-divider uk-text-muted tm-text-size-14 tm-margin-16-top">
                                    <?php foreach ($item->fields as $field) : ?>
                                        <li>
                                            <span class="uk-text-muted">
                                                <?= $field['title'] . ': ' ?>
                                            </span>
                                            <span class="uk-text-emphasis">
                                                <?= !empty($field['value']) ? $field['value'] : '&mdash;' ?>
                                            </span>
                                        </li>
                                    <?php endforeach; ?>
                                    <?php foreach ((array) $item->advantages as $advantage) : ?>
                                        <li>
                                            <?= $advantage ?>
                                        </li>
                                    <?php endforeach; ?>
                                    <li class="uk-hidden"></li>
                                    <?php if ($item->priceDifference->val()) : ?>
                                        <li>
                                            <span class="hp-group-slider-part__price-label">
                                                <?= Text::_('COM_HYPERPC_PRODUCT_QUICK_CONFIGURATOR_PRICE_DIFFERENCE') ?>:
                                            </span>
                                            <span class="uk-text-emphasis uk-text-nowrap">
                                                <?= $item->priceDifference->val() > 0 ? '+' : '-' ?>
                                                <?= $item->priceDifference->abs()->html() ?>
                                            </span>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                        <div class="uk-card-footer uk-text-center">
                            <?php
                            if (empty($item->priceValue)) {
                                $item->priceValue = $this->hyper['helper']['money']->get(0);
                            }

                            $pickBtnAttrs = [
                                'data'   => [
                                    'name'            => $item->name,
                                    'itemkey'         => $item->itemKey,
                                    'is-reload'       => $item->isContentOverriden,
                                    'price'           => $item->priceValue->abs()->val(),
                                    'override-params' => !empty($item->overrideParams) ? json_encode($item->overrideParams) : '{}'
                                ],
                                'hidden' => $item->isDefault ? 'hidden' : false,
                                'class'  => ['uk-width-1-1 uk-width-auto@s uk-button-primary uk-button jsItemChoose']
                            ]

                            ?>
                            <span <?= $this->hyper['helper']['html']->buildAttrs($pickBtnAttrs) ?>>
                                <?= Text::_('COM_HYPERPC_PRODUCT_QUICK_CONFIGURATOR_PART_PICK') ?>
                            </span>
                            <span class="uk-width-1-1 uk-width-auto@s uk-button-default uk-button uk-disabled"<?= ($item->isDefault) ? '' : ' hidden' ?>>
                                <?= Text::_('COM_HYPERPC_PRODUCT_QUICK_CONFIGURATOR_PART_PICKED') ?>
                            </span>
                        </div>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>

        <a class="uk-position-center-left uk-position-small uk-slidenav-large uk-visible@s uk-icon uk-slidenav-previous uk-slidenav" uk-slidenav-previous uk-slider-item="previous"></a>
        <a class="uk-position-center-right uk-position-small uk-slidenav-large uk-visible@s uk-icon uk-slidenav-next uk-slidenav" uk-slidenav-next uk-slider-item="next"></a>

        <div class="uk-slidenav-container uk-flex-middle uk-flex-center tm-margin-16">
            <a class="uk-icon uk-slidenav-previous uk-slidenav" uk-slidenav-previous uk-slider-item="previous"></a>
            <span>
                <?= Text::sprintf('COM_HYPERPC_RESULTS_OF', "<span class=\"jsSliderNavActiveRange\">{$defaultIndex}</span>", count((array) $itemsData)) ?>
            </span>
            <a class="uk-icon uk-slidenav-next uk-slidenav" uk-slidenav-next uk-slider-item="next"></a>
        </div>
    </div>
</div>
