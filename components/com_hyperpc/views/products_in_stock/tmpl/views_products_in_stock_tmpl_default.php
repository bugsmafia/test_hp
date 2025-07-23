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

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use HYPERPC\Helper\UikitHelper;
use HYPERPC\Helper\FilterHelper;
use HYPERPC\Helper\RenderHelper;
use Joomla\CMS\Component\ComponentHelper;

/**
 * @var HyperPcViewProducts_In_Stock    $this
 */

/** @var FilterHelper */
$filterHelper = $this->filterData->helper;
/** @var RenderHelper */
$renderHelper = $this->hyper['helper']['render'];
/** @var UikitHelper */
$uikitHelper = $this->hyper['helper']['uikit'];

$activeMenuItem  = $this->hyper['app']->getMenu()->getActive();
if ($activeMenuItem) {
    $showPageHeading = $activeMenuItem->getParams()->get('show_page_heading');
}

if (empty($showPageHeading)) {
    $comMenusParams  = ComponentHelper::getParams('com_menus');
    $showPageHeading = $comMenusParams->get('show_page_heading');
}

$countProducts    = count($this->products);
$hasFilterRenders = $filterHelper->hasFilterRender();
$showBody         = (bool) $countProducts;

$cols           = HP_DEFAULT_ROW_COLS;
$gridClass      = $uikitHelper->getProductsResponsiveClassByCols($hasFilterRenders ? $cols - 1 : $cols);
$containerClass = $uikitHelper->getProductsContainerClassByCols($cols);

//  Is filter url query.
if ($this->filterData->getUrlQueryData()->count() > 0) {
    $showBody = true;
}

$this->filterData->filter->setFieldOptionsCount();

$type = $this->filterData->filter->getType();

$wrapAttrs = [
    'id'    => 'buy',
    'class' => 'jsInstockProducts uk-section uk-section-small hp-filter-wrapper',
    'data'  => [
        'ajax'     => json_encode([
            'context' => $this->filterData->filter->context,
            'option'  => HP_OPTION,
            'tmpl'    => 'component',
            'type'    => $type,
            'task'    => 'moysklad_product.ajax-filter'
        ]),
        'filters' => json_encode($this->filterData->filter->getInitState())
    ]
];

$currentFilters = $this->filterData->filter->getCurrentFilters();
$currentFiltersCount = $currentFilters->count();
if (empty($this->hyper['input']->get('store'))) {
    $currentFiltersCount += 1;
}
?>
<div>
    <?php if ((int) $showPageHeading) : ?>
        <h1 class="uk-text-center">
            <?= Text::_('COM_HYPERPC_PRODUCTS_IN_STOCK_HEADER') ?>
        </h1>
    <?php endif; ?>

    <?= HTMLHelper::_('content.prepare', $this->description) ?>

    <div <?= $this->hyper['helper']['html']->buildAttrs($wrapAttrs) ?>>
        <?php
        if ($showBody === true && $hasFilterRenders) {
            echo $filterHelper->renderNavBar($currentFiltersCount);
        }
        ?>

        <div class="<?= $containerClass ?>">
            <?php if ($showBody) : ?>
                <?php if ($hasFilterRenders && $filterHelper->isDebugMode()) : ?>
                    <div class="uk-grid uk-grid-small">
                        <div class="uk-width-1-1 jsFilterQueryDump">
                            <?= $this->filterData->filter->getQueryDump() ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="uk-grid uk-grid-small">
                    <?php
                    if ($hasFilterRenders) {
                        echo $filterHelper->render(
                            $this->filterData->getRenderElements(),
                            $currentFiltersCount
                        );
                    }
                    ?>
                    <div class="uk-width-expand">
                        <div class="jsGroupItems tm-products-grid uk-grid-match">
                            <?php
                            if ($countProducts > 0) {
                                echo $renderHelper->render('product/teaser/2024/default', [
                                    'showConfBtn'       => false,
                                    'showDesc'          => false,
                                    'showFullConfig'    => true,
                                    'linkToPage'        => true,
                                    'teaserType'        => 'default',
                                    'groups'            => $this->groups,
                                    'options'           => $this->options,
                                    'showFps'           => $this->showFps,
                                    'products'          => $this->products
                                ], 'renderer', false);
                            } else {
                                echo $renderHelper->render('filter/common/no_found');
                            }
                            ?>
                        </div>
                    </div>
                </div>
            <?php else : ?>
                <div class="uk-alert uk-alert-warning">
                    <?= Text::_('COM_HYPERPC_PRODUCTS_IN_STOCK_EMPTY') ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
