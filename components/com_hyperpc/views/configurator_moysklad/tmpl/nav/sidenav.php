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
use HYPERPC\Helper\RenderHelper;
use HYPERPC\Helper\ConfiguratorHelper;
use HYPERPC\Joomla\Model\Entity\Interfaces\ProductMarker;
use HYPERPC\Joomla\Model\Entity\Interfaces\CategoryMarker;

/**
 * @var RenderHelper    $this
 * @var CategoryMarker  $group
 * @var CategoryMarker  $child
 * @var array           $parts
 * @var array           $groups
 * @var ProductMarker   $product
 * @var CategoryMarker  $category
 */

/** @var ConfiguratorHelper */
$configuratorHelper = $this->hyper['helper']['configurator'];
?>
<div class="uk-position-z-index" uk-sticky="offset: 61; bottom: true;">

    <?php if ($configuratorHelper->getInstockTogglerPosition() === 'sidenav') : ?>
        <?php $initState = $configuratorHelper->inStockOnlyInitState($product); ?>
        <div class="uk-margin-small-top" style="margin-inline-start: -30px">
            <label class="tm-toggle uk-flex-between">
                <span class="tm-toggle__label uk-text-emphasis">
                    <?= Text::_('COM_HYPERPC_ONLY_INSTOCK') ?>
                </span>
                <input type="checkbox" class="tm-toggle__checkbox jsOnlyInstockGlobal"<?= $initState ? ' checked' : '' ?>>
                <span class="tm-toggle__switch">
                    <span class="tm-toggle__knob"></span>
                </span>
            </label>
            <div class="uk-text-small uk-text-muted" style="margin-top: 6px">
                <?= Text::_('COM_HYPERPC_DONT_SHOW_PREORDER_PARTS') ?>
            </div>

            <hr class="uk-margin-small">
        </div>
    <?php endif; ?>

    <ul class="hp-group-nav uk-nav uk-nav-default" uk-nav="collapsible:false" uk-scrollspy-nav="closest: li; overflow: true; offset: 10; scroll: true">
        <?php if ($category->params->get('configurator_complectations', false, 'bool')) : ?>
            <li class="uk-parent">
                <a class="uk-text-uppercase">
                    <?= Text::_('COM_HYPERPC_PLATFORM') ?>
                </a>
                <ul class="uk-nav-sub" hidden>
                    <li>
                        <a href="#complectation" >
                            <span class="uk-margin-small-right" uk-icon="icon: cog;"></span>
                            <?= Text::_('COM_HYPERPC_COMPLECTATION') ?>
                        </a>
                    </li>
                </ul>
            </li>
        <?php endif; ?>
        <?php foreach ($groups as $group) :
            if (!$configuratorHelper->hasPartsInTree($group, $parts)) {
                continue;
            }
            $children = $group->get('children');
            $hasChildren = false;
            foreach ((array) $children as $child) {
                if ($configuratorHelper->hasPartsInTree($child, $parts) &&
                    !$child->isService() &&
                    $child->showInConfig() &&
                    $configuratorHelper->anyActionsAvailable($product, $child)
                ) {
                    $hasChildren = true;
                    break;
                }
            }
            ?>

            <?php if ($hasChildren) : ?>
                <li class="uk-parent">
                    <a class="uk-text-uppercase">
                        <?= $group->title ?>
                    </a>
                    <ul class="uk-nav-sub" hidden>
                        <?php foreach ((array) $children as $child) :
                            if (!$configuratorHelper->hasPartsInTree($child, $parts) ||
                                $child->isService() ||
                                !$child->showInConfig() ||
                                !$configuratorHelper->anyActionsAvailable($product, $child)) {
                                continue;
                            }
                            ?>
                            <li>
                                <a href="#<?= $child->alias ?>" >
                                    <span class="uk-margin-small-right" uk-icon="icon: hp-<?= $child->alias; ?>;"></span>
                                    <?= $child->title; ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </li>
            <?php endif; ?>
        <?php endforeach; ?>
    </ul>
</div>
