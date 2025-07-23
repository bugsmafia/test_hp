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

use JBZoo\Data\Data;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use HYPERPC\Helper\ConfiguratorHelper;
use HYPERPC\Joomla\Model\Entity\Field;
use HyperPcViewConfigurator_Moysklad as View;
use HYPERPC\Joomla\Model\Entity\Interfaces\PartMarker;
use HYPERPC\Joomla\Model\Entity\Interfaces\CategoryMarker;

/**
 * @var View            $this
 * @var PartMarker      $part
 * @var Field           $field
 * @var CategoryMarker  $group
 * @var CategoryMarker  $child
 */

/** @var ConfiguratorHelper $helper */
$helper = $this->hyper['helper']['configurator'];

$isMobile = $this->hyper['detect']->isMobile();

$subGroups       = [];
$configurationId = $this->product->saved_configuration ? $this->product->saved_configuration : 0;

$backUrl  = $this->product->getViewUrl();
$category = $this->category;

if ($category->params->get('teasers_type', 'default') === 'lumen') {
    $backUrl = $category->getViewUrl();
}

$nothingSelectedImgSrc = $helper->getNothingSelectedImageSrc();

$userIsOwner = $this->userIsOwner();
$saveEnabled = !($userIsOwner);

/** @todo get managers configurator for moysklad product */
$isManagersConfigurator = $this->product->id === $this->hyper['params']->get('managers_configurator', '', 'int');

$iconViewList = '<svg width="32" height="32" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><rect x="6" y="4" width="12" height="1"></rect><rect x="6" y="9" width="12" height="1"></rect><rect x="6" y="14" width="12" height="1"></rect><rect x="2" y="4" width="2" height="1"></rect><rect x="2" y="9" width="2" height="1"></rect><rect x="2" y="14" width="2" height="1"></rect></svg>';
$iconViewThumbnails = '<svg width="32" height="32" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><rect fill="none" stroke="#000" x="3.5" y="3.5" width="5" height="5"></rect><rect fill="none" stroke="#000" x="11.5" y="3.5" width="5" height="5"></rect><rect fill="none" stroke="#000" x="11.5" y="11.5" width="5" height="5"></rect><rect fill="none" stroke="#000" x="3.5" y="11.5" width="5" height="5"></rect></svg>';

$cacheProduct = $this->product->saved_configuration ? false : $this->product->getCacheGroup();
?>
<div class="hp-configurator" data-mobile="<?= $isMobile ? 'true' : 'false' ?>">

    <?php
    if ($isMobile) {
        echo $this->renderLayout('nav/navbar_mobile', [
            'backUrl'         => $backUrl,
            'parts'           => $this->parts,
            'groups'          => $this->groups,
            'product'         => $this->product,
            'configuration'   => $this->configuration,
            'configurationId' => $configurationId,
            'category'        => $this->category
        ], false);
    } else {
        echo $this->renderLayout('nav/navbar', [
            'backUrl'       => $backUrl,
            'product'       => $this->product,
            'configuration' => $this->configuration
        ], false);
    }
    ?>

    <?php if ($isMobile) : ?>
        <div class="uk-navbar-container uk-position-fixed uk-position-bottom">
            <nav class="uk-navbar uk-container uk-container-large">
                <div class="uk-navbar-right">
                    <div class="hp-product-purchase uk-navbar-item uk-padding-remove uk-text-nowrap">

                        <?= $this->hyper['helper']['render']->render('common/price/item-price', [
                            'price'      => $this->productPrice,
                            'entity'     => $this->product,
                            'htmlPrices' => true
                        ]); ?>

                        <?= $this->product->getRender()->cartBtnForConfigurator(); ?>

                        <button type="button" class="tm-tapbar-button-more">
                            <span uk-icon="icon: more" class="uk-icon"></span>
                            <br>
                            <?= Text::_('COM_HYPERPC_MORE') ?>
                        </button>

                        <div class="uk-dropdown" uk-dropdown="pos: top-right; offset: 12">
                            <ul class="uk-nav uk-dropdown-nav tm-dropdown-nav-iconnav">
                                <li>
                                    <?php $initState = $helper->inStockOnlyInitState($this->product); ?>
                                    <label class="tm-toggle uk-flex-between" style="min-width:220px">
                                        <span class="tm-toggle__label tm-text-medium uk-text-emphasis">
                                            <?= Text::_('COM_HYPERPC_ONLY_INSTOCK') ?>
                                        </span>
                                        <input type="checkbox" class="tm-toggle__checkbox jsOnlyInstockGlobal"<?= $initState ? ' checked' : '' ?>>
                                        <span class="tm-toggle__switch">
                                            <span class="tm-toggle__knob"></span>
                                        </span>
                                    </label>
                                    <div class="uk-text-small uk-text-muted">
                                        <?= Text::_('COM_HYPERPC_DONT_SHOW_PREORDER_PARTS') ?>
                                    </div>
                                </li>
                                <li class="uk-nav-divider"></li>
                                <li>
                                    <a role="button" class="jsSaveConfig<?= $saveEnabled ? '' : ' uk-disabled' ?>"
                                        title="<?= Text::_('COM_HYPERPC_CONFIG_SAVE_BUTTON_HINT') ?>">
                                        <span class="uk-icon" uk-icon="icon: hp-diskette; ratio: .9"></span>
                                        <?= Text::_('JSAVE') ?>
                                    </a>
                                </li>
                                <li>
                                    <a role="button" class="jsConfigReset uk-disabled" title="<?= Text::_('COM_HYPERPC_CONFIGURATOR_RESET_TITLE') ?>">
                                        <span class="uk-icon" uk-icon="refresh"></span>
                                        <?= Text::_('COM_HYPERPC_CONFIGURATOR_RESET') ?>
                                    </a>
                                </li>
                                <li>
                                    <a role="button" uk-toggle="target: #load-configuration-modal">
                                        <span class="uk-icon" uk-icon="upload"></span>
                                        <?= Text::_('COM_HYPERPC_LOAD') ?>
                                    </a>
                                </li>
                                <?php if ($this->hyper['params']->get('conf_save_email', 1)) : ?>
                                    <li>
                                        <a role="button" uk-toggle="target: #save_email">
                                            <span class="uk-icon" uk-icon="mail"></span>
                                            <?= Text::_('COM_HYPERPC_CONFIG_EMAIL_SEND') ?>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                <li>
                                    <a role="button" class="jsShowFullSpecs">
                                        <span class="uk-icon" uk-icon="file-text"></span>
                                        <?= Text::_('COM_HYPERPC_SPECIFICATION') ?>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </nav>
        </div>
    <?php endif; ?>

    <form name="configurator" method="post" class="jsConfiguratorForm">
        <input type="hidden" class="jsSavedConfigurationId" name="saved_configuration" value="<?= $configurationId ?>" />

        <div class="uk-container uk-container-large">
            <div class="uk-grid uk-grid-small uk-child-width-expand uk-flex-between uk-margin-medium-top">
                <?php if (!$isMobile) : ?>
                    <div class="uk-width-auto uk-visible@l" style="width:230px">
                        <?php
                        echo $this->renderLayout('nav/sidenav', [
                            'parts'    => $this->parts,
                            'groups'   => $this->groups,
                            'product'  => $this->product,
                            'category' => $this->category
                        ], $cacheProduct);
                        ?>
                    </div>
                <?php endif; ?>

                <?php
                echo $this->renderLayout('specs_box/box', [
                    'parts'               => $this->parts,
                    'groups'              => $this->groups,
                    'product'             => $this->product,
                    'productParts'        => $this->productParts,
                    'productPrice'        => $this->productPrice,
                    'groupList'           => $this->groupList,
                    'configurationId'     => $configurationId,
                    'saveEnabled'         => $saveEnabled,
                    'hasUnavailableParts' => isset($this->configurationCheckData) ? count($this->configurationCheckData->unavalableParts) : false
                ], false);
                ?>

                <div class="uk-width-expand">
                    <?php if (count($this->complectations)) : ?>
                        <div id="complectation">
                            <div class="sub-group-description">
                                <div class="uk-flex uk-flex-middle uk-flex-wrap">
                                    <span class="uk-text-muted uk-icon" uk-icon="icon: cog; ratio:2"></span>
                                    <span class="uk-h3 uk-text-truncate"><?= Text::_('COM_HYPERPC_COMPLECTATION') ?></span>
                                </div>
                            </div>

                            <div class="uk-background-muted uk-padding-small">
                                <div>
                                    <?php
                                    echo $this->renderLayout('group/platform', [
                                        'product'        => $this->product,
                                        'complectations' => $this->complectations,
                                        'productParts'   => $this->productParts,
                                        'groupList'      => $this->groupList,
                                    ], $cacheProduct) ?>
                                </div>

                                <div id="change-platform-modal" class="uk-modal uk-modal-container" uk-modal>
                                    <div class="uk-modal-dialog">

                                        <button class="uk-modal-close-default uk-close-large" type="button" uk-close></button>

                                        <div class="uk-modal-header uk-h4">
                                            <?= Text::_('COM_HYPERPC_CHANGE_PLATFORM') ?>
                                        </div>

                                        <div class="uk-modal-body" uk-overflow-auto>
                                            <ul class="uk-grid uk-grid-small uk-child-width-1-2@s uk-child-width-1-3@xl uk-grid-match" uk-grid uk-height-match=".uk-card-body">
                                                <?php
                                                array_unshift($this->complectations, (clone $this->product));
                                                foreach ($this->complectations as $complectation) : ?>
                                                    <?= $this->renderLayout('group/complectation', [
                                                        'complectation' => $complectation,
                                                        'product'       => $this->product,
                                                        'groups'        => $this->configGroups,
                                                    ], $complectation->getCacheGroup()) ?>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>

                                        <div class="uk-modal-footer uk-text-right">
                                            <button class="uk-button uk-button-default uk-modal-close uk-margin-small-right" type="button">
                                                <?= Text::_('JCANCEL') ?>
                                            </button>
                                            <button class="jsProceedToLoadComplectation uk-button uk-button-primary" type="button" disabled>
                                                <?= Text::_('COM_HYPERPC_CONTINUE') ?>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php foreach ($this->configGroups as $group) :
                        $required            = !$helper->isCanDeselected($this->product, $group);
                        $anyActionsAvailable = $helper->anyActionsAvailable($this->product, $group);
                        $hiddenGroup         = $group->isService() || (!$isManagersConfigurator && (!$group->showInConfig() || !$anyActionsAvailable));
                        $groupTotal          = $this->_getGroupTotal($group->id);

                        if ($group->params->get('configurator_layout') === 'design') :
                            $groupAttr = [
                                'data-id' => $group->id,
                                'id'      => $group->alias,
                                'class'   => 'sub-group-content hp-group-design hp-group-' . $group->id,
                                'data'    => [
                                    'group-total' => $groupTotal
                                ]
                            ];

                            if ($hiddenGroup) {
                                $groupAttr['hidden'] = 'hidden';
                            }
                            ?>

                            <div <?= $this->hyper['helper']['html']->buildAttrs($groupAttr) ?>>
                                <?= $this->renderLayout('group/heading', [
                                        'group' => $group,
                                    ], true);
                                ?>

                                <div class="uk-background-muted uk-padding-small uk-overflow-hidden">

                                    <div class="hp-conf-group__image-box uk-text-center">
                                        <img src="<?= $nothingSelectedImgSrc ?>" class="jsCheckedItemImage" alt="" />
                                    </div>

                                    <ul class="hp-configurator-parts uk-list">
                                        <?php foreach ($this->groupParts[$group->id] as $part) : ?>
                                            <?php
                                            echo $this->renderLayout('group/part-swatches', [
                                                'part'       => $part,
                                                'group'      => $group,
                                                'groupTotal' => $groupTotal,
                                                'product'    => $this->product,
                                            ], false);
                                            ?>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                        <?php else :
                            $groupLayout = new Data([
                                'default'    => $group->params->get('configurator_layout'),
                                'columns'    => $group->params->get('configurator_cols', 1, 'int'),
                                'switchable' => $group->params->get('configurator_enable_toggle', 0, 'int')
                            ]);

                            $viewClass      = ($groupLayout->get('default') === 'column' && !$isMobile) ? ' hp-view-thumbnails' : '';
                            $showGroupImage = !$isMobile && ($groupLayout->get('switchable') || $groupLayout->get('default') !== 'column');

                            $isMultiply = $helper->isMultiplyPartsSelect($this->product, $group->id);

                            $divideByAvailability = $group->params->get('configurator_divide_by_availability', 0, 'bool');

                            $gridClass = 'uk-grid ';
                            if ($groupLayout->get('default') === 'column' && !$isMobile) {
                                $gridClass .= 'uk-grid-small';
                                switch ($groupLayout->get('columns')) {
                                    case 2:
                                        $gridClass .= ' uk-child-width-1-1 uk-child-width-1-2@s';
                                        break;
                                    case 3:
                                        $gridClass .= ' uk-child-width-1-2 uk-child-width-1-3@s uk-child-width-1-2@m uk-child-width-1-3@xl';
                                        break;
                                    case 4:
                                        $gridClass .= ' uk-child-width-1-2 uk-child-width-1-3@s uk-child-width-1-4@m uk-child-width-1-3@l uk-child-width-1-4@xl';
                                        break;
                                }
                            } else {
                                $gridClass .= 'uk-child-width-1-1 uk-grid-collapse';
                            }

                            $groupAttr = [
                                'data-id'   => $group->id,
                                'id'        => $group->alias,
                                'class'     => 'sub-group-content hp-group-' . $group->id . $viewClass,
                                'data'      => [
                                    'group-total' => $groupTotal,
                                    'divide-by-availability' => $divideByAvailability ? 'true' : false
                                ]
                            ];

                            if ($hiddenGroup) {
                                $groupAttr['hidden'] = 'hidden';
                            }

                            if ($isMultiply) {
                                $groupAttr['data-multiply'] = true;
                            }

                            ?>
                            <div <?= $this->hyper['helper']['html']->buildAttrs($groupAttr) ?>>

                                <?= $this->renderLayout('group/heading', [
                                        'group' => $group,
                                    ], true);
                                ?>

                                <div class="uk-background-muted uk-padding-small">
                                    <?php if ($groupLayout->get('switchable') === 1 && !$isMobile) : ?>
                                        <div class="jsChangeView hp-conf-group__view-toggle uk-iconnav">
                                            <a href="#" class="jsListView uk-icon<?= $groupLayout->get('default') === 'list' ? ' uk-disabled' : '' ?>"
                                                title="<?= Text::_('COM_HYPERPC_CONFIGURATOR_VIEW_LIST') ?>">
                                                <?= $iconViewList ?>
                                            </a>

                                            <a href="#" class="jsThumbView uk-icon<?= $groupLayout->get('default') === 'column' ? ' uk-disabled' : '' ?>"
                                                title="<?= Text::_('COM_HYPERPC_CONFIGURATOR_VIEW_THUMBS') ?>"
                                                data-columns="<?= $groupLayout->get('columns') ?>">
                                                <?= $iconViewThumbnails ?>
                                            </a>
                                        </div>
                                    <?php endif ?>

                                    <?php // Generate group parts html
                                    $groupPartsHtml = [];

                                    $compatibilityFieldIds = [];
                                    foreach ($this->compabilities as $compatibilityData) {
                                        if ($compatibilityData->leftGroup === $group->id) {
                                            $compatibilityFieldIds[] = $compatibilityData->leftField;
                                        } elseif ($compatibilityData->rightGroup === $group->id) {
                                            $compatibilityFieldIds[] = $compatibilityData->rightField;
                                        }
                                    }

                                    /** @var PartMarker $part */
                                    foreach ($this->groupParts[$group->id] as $part) {
                                        $groupPartsHtml[] = $this->renderLayout('group/part', [
                                            'product'               => $this->product,
                                            'part'                  => $part,
                                            'group'                 => $group,
                                            'groupTotal'            => $groupTotal,
                                            'layout'                => $groupLayout,
                                            'isMobile'              => $isMobile,
                                            'compareItems'          => $this->compareItems,
                                            'required'              => $required,
                                            'partIsMultiply'        => $isMultiply,
                                            'divideByAvailability'  => $divideByAvailability,
                                            'compatibilityFieldIds' => $compatibilityFieldIds
                                        ], false);
                                    }
                                    ?>

                                    <?= $this->renderLayout('group/filter', [
                                        'group'                => $group,
                                        'product'              => $this->product,
                                        'divideByAvailability' => $divideByAvailability
                                    ], false);
                                    ?>

                                    <div class="uk-grid uk-grid-small" uk-grid>
                                        <?php if ($showGroupImage) :  ?>
                                            <div class="uk-width-1-4@xl hp-conf-group__image-box">
                                                <img src="<?= $nothingSelectedImgSrc ?>" class="jsCheckedItemImage" width="314" alt="" />
                                            </div>
                                        <?php endif; ?>

                                        <div class="uk-width-expand">
                                            <?php if (array_key_exists($group->id, $this->groupParts)) : ?>
                                                <ul class="hp-configurator-parts <?= $gridClass ?>">
                                                    <?= implode(PHP_EOL, $groupPartsHtml) ?>
                                                </ul>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>

            </div>
        </div>

        <div class="jsFormToken">
            <?= HTMLHelper::_('form.token') ?>
        </div>
    </form>
</div>

<?php if (isset($this->configurationCheckData)) {
    echo $this->renderLayout('_warning', [
        'configuration'          => $this->configuration,
        'configurationCheckData' => $this->configurationCheckData,
    ], false);
} ?>

<?php if ($this->hyper['params']->get('conf_save_email', 1)) : ?>
    <div id="save_email" class="uk-modal uk-flex-top" uk-modal>
        <div class="uk-width-2xlarge uk-modal-dialog uk-modal-body uk-padding uk-margin-auto-vertical tm-background-gray-5">
            <button class="uk-modal-close-default uk-close-large" type="button" uk-close></button>

            <div class="uk-h3"><?= Text::_('COM_HYPERPC_CONFIG_SAVE_SEND_EMAIL') ?></div>
            <?= $this->hyper['helper']['render']->render('configurator_moysklad/tmpl/save/email_form', [
                'form' => $this->leadForm
            ], 'views');
            ?>
        </div>
    </div>
<?php endif;
