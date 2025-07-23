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
 *
 * @var         RenderHelper    $this
 */

use Joomla\CMS\Language\Text;
use HYPERPC\Helper\RenderHelper;
use HYPERPC\Joomla\View\Html\Data\Product\Filter;

defined('_JEXEC') or die('Restricted access');

if (!isset($filterCount)) {
    $filterCount = 0;
}

$hasActiveFilters = ($filterCount > 0);
$isMobile         = $this->hyper['detect']->isMobile();
$offset           = Filter::getUikitStickyOffset();
?>
<div class="hp-group__filters-nav uk-navbar-container uk-navbar-transparent uk-background-default uk-margin-bottom"
     uk-sticky="offset: <?= $offset ?>px; bottom: section">
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
                            <span class="jsActiveFiltersCount"<?= $filterCount === 0 ? ' hidden' : '' ?>>
                                (<?= $filterCount ?>)
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
