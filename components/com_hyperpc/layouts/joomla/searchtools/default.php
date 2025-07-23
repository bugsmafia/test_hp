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

use HYPERPC\Joomla\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\HTML\LayoutHelper;

defined('_JEXEC') or die('Restricted access');

$data = $displayData;

// Receive overridable options
$data['options'] = !empty($data['options']) ? $data['options'] : [];

$noResultsText     = '';
$hideActiveFilters = false;
$showFilterButton  = false;
$showSelector      = false;
$selectorFieldName = isset($data['options']['selectorFieldName']) ? $data['options']['selectorFieldName'] : 'client_id';

//  If a filter form exists.
if (isset($data['view']->filterForm) && !empty($data['view']->filterForm)) {
    //  Checks if a selector (e.g. client_id) exists.
    if ($selectorField = $data['view']->filterForm->getField($selectorFieldName)) {
        $showSelector = $selectorField->getAttribute('filtermode', '') == 'selector' ? true : $showSelector;

        //  Checks if a selector should be shown in the current layout.
        if (isset($data['view']->layout)) {
            $showSelector = $selectorField->getAttribute('layout', 'default') != $data['view']->layout ? false : $showSelector;
        }

        //  Unset the selector field from active filters group.
        unset($data['view']->activeFilters[$selectorFieldName]);
    }

    //  Checks if the filters button should exist.
    $filters = $data['view']->filterForm->getGroup('filter');
    $showFilterButton = isset($filters['filter_search']) && count($filters) === 1 ? false : true;

    //  Checks if it should show the be hidden.
    $hideActiveFilters = empty($data['view']->activeFilters);

    //  Check if the no results message should appear.
    if (isset($data['view']->total) && (int) $data['view']->total === 0) {
        $noResults = $data['view']->filterForm->getFieldAttribute('search', 'noresults', '', 'filter');
        if (!empty($noResults)) {
            $noResultsText = Text::_($noResults);
        }
    }
}

//  Set some basic options.
$customOptions = [
    'showSelector'        => $showSelector,
    'searchFieldSelector' => '#filter_search',
    'selectorFieldName'   => $selectorFieldName,
    'orderFieldSelector'  => '#list_fullordering',
    'showNoResults'       => !empty($noResultsText) ? true : false,
    'noResultsText'       => !empty($noResultsText) ? $noResultsText : '',
    'formSelector'        => !empty($data['options']['formSelector']) ? $data['options']['formSelector'] : '#adminForm',
    'filterButton'        => isset($data['options']['filterButton']) && $data['options']['filterButton'] ? $data['options']['filterButton'] : $showFilterButton,
    'filtersHidden'       => isset($data['options']['filtersHidden']) && $data['options']['filtersHidden'] ? $data['options']['filtersHidden'] : $hideActiveFilters,
    'defaultLimit'        => isset($data['options']['defaultLimit']) ? $data['options']['defaultLimit'] : Factory::getApplication()->get('list_limit', 20)
];

//  Merge custom options in the options array.
$data['options'] = array_merge($customOptions, $data['options']);

//  Add class to hide the active filters if needed.
$filtersActiveClass = $hideActiveFilters ? '' : ' js-stools-container-filters-visible';

//  Load search tools
HTMLHelper::_('searchtools.form', $data['options']['formSelector'], $data['options']);
?>
<div class="js-stools clearfix">
    <div class="clearfix">
        <?php if ($data['options']['showSelector']) : ?>
            <div class="js-stools-container-selector">
                <?= LayoutHelper::render('joomla.searchtools.default.selector', $data); ?>
            </div>
        <?php endif; ?>
        <div class="js-stools-container-bar">
            <?php echo $this->sublayout('bar', $data); ?>
        </div>
        <div class="js-stools-container-list hidden-phone hidden-tablet">
            <?= $this->sublayout('list', $data); ?>
        </div>
    </div>
</div>
<?php if ($data['options']['showNoResults']) : ?>
    <?php echo $this->sublayout('noitems', $data); ?>
<?php endif; ?>
