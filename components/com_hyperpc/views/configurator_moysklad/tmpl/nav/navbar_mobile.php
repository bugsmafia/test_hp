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

use JBZoo\Utils\Str;
use Joomla\CMS\Language\Text;
use HYPERPC\Helper\RenderHelper;
use HYPERPC\Helper\ConfiguratorHelper;
use HYPERPC\Joomla\Model\Entity\ProductFolder;
use HYPERPC\Joomla\Model\Entity\Interfaces\ProductMarker;

/**
 * @var         RenderHelper    $this
 * @var         ProductFolder   $group
 * @var         array           $parts
 * @var         array           $groups
 * @var         string          $backUrl
 * @var         int             $configurationId
 * @var         ProductMarker   $product
 * @var         ProductFolder   $category
 */

/** @var ConfiguratorHelper */
$configuratorHelper = $this->hyper['helper']['configurator'];
$cartModuleId       = $this->hyper['params']->get('configurator_cart');
$loadConfigModuleId = $this->hyper['params']->get('configurator_load_config');

$showComplectations = $category->params->get('configurator_complectations', false, 'bool');
?>

<div style="height:100px">
    <div class="jsConfigToolbar hp-config-toolbar">
        <div id="hp-product-nav" class="uk-navbar-container uk-background-secondary" style="border: none">
            <nav class="uk-navbar uk-container uk-container-large uk-flex-between" uk-navbar>
                <div class="uk-navbar-left">
                    <a class="jsLeaveConfigurator uk-navbar-item uk-padding-remove hp-config-toolbar__leave-button"
                        href="<?= $backUrl ?>">
                        <span uk-icon="icon: arrow-left"></span>
                        <span><?= Text::_('COM_HYPERPC_CLOSE') ?></span>
                    </a>
                </div>
                <div class="jsConfigNumberWrapper"<?= !$configurationId ? ' hidden' : '' ?>>
                    <div class="jsConfigNumber hp-config-number uk-navbar-item uk-padding-remove">
                        <?= $configurationId ? Str::zeroPad($configurationId, 7) : '' ?>
                    </div>
                </div>
                <div class="uk-navbar-right">
                    <div class="tm-navbar-phones uk-navbar-item">
                        <?= $this->hyper['helper']['render']->render('common/company_phone') ?>
                    </div>

                    <?php if (!empty($loadConfigModuleId)) : ?>
                        <div>
                            <?= $this->hyper['helper']['module']->renderById($loadConfigModuleId) ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($cartModuleId)) : ?>
                        <ul class="uk-iconnav">
                            <?= $this->hyper['helper']['module']->renderById($cartModuleId) ?>
                        </ul>
                    <?php endif; ?>

                    <div class="uk-margin-small-right">
                        <button class="uk-navbar-toggle uk-navbar-toggle-icon uk-icon" type="button"
                                uk-toggle="target: #hp-offcanvas-menu" uk-navbar-toggle-icon></button>
                    </div>
                </div>
            </nav>
        </div>

        <div class="hp-nav-groups">
            <div class="uk-position-absolute">
                <ul class="uk-navbar-nav jsScrollableNav" uk-scrollspy-nav="closest: li; scroll: true; offset: 10">
                    <?php if ($showComplectations) : ?>
                        <li>
                            <a href="#complectation"><?= Text::_('COM_HYPERPC_PLATFORM') ?></a>
                        </li>
                    <?php endif; ?>
                    <?php foreach ($groups as $group) :
                        if (!$configuratorHelper->hasPartsInTree($group, $parts)) {
                            continue;
                        }
                        $children = $group->get('children');
                        /** @var ProductFolder $child */
                        foreach ((array) $children as $child) :
                            if (!$configuratorHelper->hasPartsInTree($child, $parts) ||
                                $child->isService() ||
                                !$child->showInConfig() ||
                                !$configuratorHelper->anyActionsAvailable($product, $child)
                            ) {
                                continue;
                            }
                            ?>

                            <li>
                                <a href="#<?= $child->alias ?>"><?= $group->title ?></a>
                            </li>

                            <?php break; ?>

                        <?php endforeach; ?>

                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <div class="hp-nav-subgroups">
            <div class="uk-position-absolute">
                <ul class="uk-navbar-nav jsScrollableNav" uk-scrollspy-nav="closest: li; scroll: true; offset: 10">
                    <?php if ($showComplectations) : ?>
                        <li>
                            <a href="#complectation" class="uk-text-center uk-flex-column" style="height:auto">
                                <span uk-icon="icon: cog"></span>
                                <span class="hp-nav-group-title uk-text-nowrap"><?= Text::_('COM_HYPERPC_COMPLECTATION') ?></span>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php
                    /** @var ProductFolder $group */
                    foreach ($groups as $group) :
                        if (!$configuratorHelper->hasPartsInTree($group, $parts)) {
                            continue;
                        }
                        $children = $group->get('children');
                        /** @var ProductFolder $child */
                        foreach ((array) $children as $child) :
                            if (!$configuratorHelper->hasPartsInTree($child, $parts) ||
                                $child->isService() ||
                                !$child->showInConfig() ||
                                !$configuratorHelper->anyActionsAvailable($product, $child)) {
                                continue;
                            }
                            ?>
                            <li>
                                <a href="#<?= $child->alias ?>" class="uk-text-center uk-flex-column" style="height:auto">
                                    <span uk-icon="icon: hp-<?= $child->alias ?>"></span>
                                    <span class="hp-nav-group-title uk-text-nowrap"><?= $child->title ?></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>
