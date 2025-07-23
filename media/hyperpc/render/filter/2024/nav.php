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

use HYPERPC\Filters\Filter;
use Joomla\CMS\Language\Text;
use HYPERPC\Helper\RenderHelper;

/**
 * @var RenderHelper $this
 * @var Filter $filter
 */

$state = $filter->getState();
$activeFilters = $state['current'];

$isMobile           = $this->hyper['detect']->isMobile();
$hasActiveFilters   = !empty($activeFilters);
$activeFiltersCount = count($activeFilters);
?>
<div class="hp-group__filters-nav uk-navbar-container uk-navbar-transparent uk-background-default uk-margin-bottom"
     uk-sticky="offset: 51px; bottom: section">
    <div class="uk-navbar uk-container uk-container-large">
        <div class="uk-navbar-left">
            <div class="uk-navbar-item uk-padding-remove">
                <ul class="uk-subnav uk-subnav-divider">
                    <li>
                        <a uk-toggle="target: .hp-group__filters; animation: uk-animation-slide-left;">
                            <span class="uk-icon uk-text-top" style="margin-inline-end: 5px">
                                <?= $this->hyper['helper']['html']->svgIcon('list') ?>
                            </span>
                            <?= Text::_('COM_HYPERPC_FILTERS') ?>
                            <span class="jsActiveFiltersCount"<?= $activeFiltersCount === 0 ? ' hidden' : '' ?>>
                                (<?= $activeFiltersCount ?>)
                            </span>
                        </a>
                    </li>
                    <?php if (!$isMobile) : ?>
                        <li class="jsClearAllFilters"<?= $hasActiveFilters ? '' : ' hidden' ?>>
                            <a>
                                <?= Text::_('COM_HYPERPC_RESET') ?>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</div>
