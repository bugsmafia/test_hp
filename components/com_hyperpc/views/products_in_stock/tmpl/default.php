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
use Joomla\CMS\Log\Log;

/**
 * @var HyperPcViewProducts_In_Stock    $this
 */

/** @var FilterHelper */
$filterHelper = !empty($this->filterData->helper) ? $this->filterData->helper : null;
dump($filterHelper);

/** @var FilterHelper */
$filterHelper = !empty($this->filterData->helper) && $this->filterData->helper instanceof FilterHelper ? $this->filterData->helper : null;
Log::add('filterHelper: ' . (is_object($filterHelper) ? get_class($filterHelper) : 'null'), Log::DEBUG, 'com_hyperpc');

/** @var RenderHelper */
$renderHelper = $this->hyper['helper']['render'] instanceof RenderHelper ? $this->hyper['helper']['render'] : null;
Log::add('renderHelper: ' . (is_object($renderHelper) ? get_class($renderHelper) : 'null'), Log::DEBUG, 'com_hyperpc');

/** @var UikitHelper */
$uikitHelper = $this->hyper['helper']['uikit'] instanceof UikitHelper ? $this->hyper['helper']['uikit'] : null;
Log::add('uikitHelper: ' . (is_object($uikitHelper) ? get_class($uikitHelper) : 'null'), Log::DEBUG, 'com_hyperpc');

$activeMenuItem = $this->hyper['app'] ? $this->hyper['app']->getMenu()->getActive() : null;
if ($activeMenuItem) {
    $showPageHeading = $activeMenuItem->getParams()->get('show_page_heading');
}

if (empty($showPageHeading)) {
    $comMenusParams = ComponentHelper::getParams('com_menus');
    $showPageHeading = $comMenusParams->get('show_page_heading');
}

$countProducts = count($this->products);
Log::add(__LINE__ . __DIR__ . " --- countProducts --- " . $countProducts, Log::DEBUG, 'com_hyperpc');

// Проверяем наличие filterHelper и метода hasFilter
$hasFilterRenders = $filterHelper && method_exists($filterHelper, 'hasFilter') ? $filterHelper->hasFilter() : false;
Log::add(__LINE__ . __DIR__ . " --- hasFilter --- " . print_r($hasFilterRenders, true), Log::DEBUG, 'com_hyperpc');

$showBody = (bool) $countProducts;

// Is filter url query.
if ($this->filterData && !empty($this->filterData->filter) && method_exists($this->filterData, 'getUrlQueryData') && is_object($this->filterData->getUrlQueryData()) && $this->filterData->getUrlQueryData()->count() > 0) {
    $showBody = true;
}

if ($this->filterData && !empty($this->filterData->filter)) {
    Log::add(__LINE__ . __DIR__ . " --- this->filterData->filter->setFieldOptionsCount() --- ", Log::DEBUG, 'com_hyperpc');
    $this->filterData->filter->setFieldOptionsCount();
}

$type = $this->filterData && !empty($this->filterData->filter) ? $this->filterData->filter->getType() : '';
Log::add(__LINE__ . __DIR__ . " --- this->filterData->filter->getType() --- " . $type, Log::DEBUG, 'com_hyperpc');



$wrapAttrs = [
    'id' => 'buy',
    'class' => 'jsInstockProducts uk-section uk-section-small hp-filter-wrapper',
    'data' => [
        'ajax' => json_encode([
            'context' => $this->filterData && isset($this->filterData->filter) ? $this->filterData->filter->context : '',
            'option' => HP_OPTION,
            'tmpl' => 'component',
            'type' => $type,
            'task' => 'moysklad_product.ajax-filter'
        ]),
        'filters' => json_encode($this->filterData && isset($this->filterData->filter) ? $this->filterData->filter->getInitState() : [])
    ]
];

$currentFilters = $this->filterData && isset($this->filterData->filter) ? $this->filterData->filter->getCurrentFilters() : new \Joomla\Registry\Registry();
dump(__LINE__.__DIR__." --- this->filterData->filter->getCurrentFilters() --- ");
dump($currentFilters);

$currentFiltersCount = $currentFilters->count();
Log::add(__LINE__ . __DIR__ . " --- currentFilters->count() --- " . $currentFiltersCount, Log::DEBUG, 'com_hyperpc');
if (empty($this->hyper['input']->get('store'))) {
    $currentFiltersCount += 1;
}

dump(__LINE__.__DIR__." --- currentFilters->count() --- ");
dump($currentFiltersCount);
?>
<div>
    <?php if ((int) $showPageHeading) : ?>
        <h1 class="uk-text-center">
            <?= Text::_('COM_HYPERPC_PRODUCTS_IN_STOCK_HEADER') ?>
        </h1>
    <?php endif; ?>

    <?= HTMLHelper::_('content.prepare', $this->description) ?>

    <div <?= $this->hyper['helper']['html'] ? $this->hyper['helper']['html']->buildAttrs($wrapAttrs) : '' ?>>
        <?php
        if ($showBody === true && $hasFilterRenders) {
            echo $filterHelper->renderNavBar($currentFiltersCount);
        }
        ?>

        <div class="<?= $containerClass ?>">
            <?php if ($showBody) : ?>
                <?php if ($hasFilterRenders && $filterHelper && $filterHelper->isDebugMode()) : ?>
                    <div class="uk-grid uk-grid-small">
                        <div class="uk-width-1-1 jsFilterQueryDump">
                            <?= $this->filterData && isset($this->filterData->filter) ? $this->filterData->filter->getQueryDump() : '' ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="uk-grid uk-grid-small">
                    <?php
                    if ($hasFilterRenders && $filterHelper) {
                        echo $filterHelper->render(
                            $this->filterData && method_exists($this->filterData, 'getRenderElements') ? $this->filterData->getRenderElements() : [],
                            $currentFiltersCount
                        );
                    }
                    ?>
                    <div class="uk-width-expand">
                        <div class="jsGroupItems tm-products-grid uk-grid-match">
                            <?php
                            if ($countProducts > 0 && $renderHelper) {
                                Log::add(__LINE__ . __DIR__ . " --- START renderHelper->render() --- ", Log::DEBUG, 'com_hyperpc');

                                echo $renderHelper->render('product/teaser/2024/default', [
                                    'showConfBtn' => false,
                                    'showDesc' => false,
                                    'showFullConfig' => true,
                                    'linkToPage' => true,
                                    'teaserType' => 'default',
                                    'groups' => $this->groups,
                                    'options' => $this->options,
                                    'showFps' => $this->showFps,
                                    'products' => $this->products
                                ], 'renderer', false);

                                Log::add(__LINE__ . __DIR__ . " --- DONE renderHelper->render() --- ", Log::DEBUG, 'com_hyperpc');
                            } else {
                                echo $renderHelper ? $renderHelper->render('filter/common/no_found') : Text::_('COM_HYPERPC_PRODUCTS_IN_STOCK_EMPTY');
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
