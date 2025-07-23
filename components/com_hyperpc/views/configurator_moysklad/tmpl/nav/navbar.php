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

use HYPERPC\App;
use Joomla\CMS\Language\Text;
use HYPERPC\Helper\RenderHelper;
use HYPERPC\Helper\ConfiguratorHelper;
use HYPERPC\Joomla\Model\Entity\SaveConfiguration;
use HYPERPC\Joomla\Model\Entity\Interfaces\ProductMarker;
use HYPERPC\Joomla\Model\Entity\Interfaces\CategoryMarker;

/**
 * @var         RenderHelper        $this
 * @var         CategoryMarker      $group
 * @var         string              $backUrl
 * @var         ProductMarker       $product
 * @var         SaveConfiguration   $configuration
 */

/** @var ConfiguratorHelper */
$configuratorHelper = $this->hyper['helper']['configurator'];

$cartModuleId       = $this->hyper['params']->get('configurator_cart');
$loadConfigModuleId = $this->hyper['params']->get('configurator_load_config');
?>

<div id="hp-product-nav" class="uk-navbar-container uk-background-secondary" uk-sticky>
    <nav class="uk-container uk-container-large uk-flex-between uk-navbar" uk-navbar>
        <div class="uk-navbar-left">

            <div class="uk-navbar-item">
                <a class="jsLeaveConfigurator uk-button uk-button-small uk-button-primary"
                    href="<?= $backUrl ?>">
                    <span uk-icon="icon: close"></span>
                    <span class="uk-visible@m"><?= Text::_('COM_HYPERPC_CONFIGURATOR_CLOSE') ?></span>
                </a>

                <?php if (false) : /** @todo copy configuration */ ?>
                    <span class="jsActionConfig jsActionConfigOnly uk-button uk-button-small uk-button-default<?= (!$configuration->id) ? ' uk-hidden' : '' ?>"
                          data-action="copy">
                        <span uk-icon="icon: copy"></span>
                        <span class="uk-visible@m"><?= Text::_('COM_HYPERPC_COPY') ?></span>
                    </span>
                <?php endif; ?>

                <?php if (false) : /** @todo compare for products */ ?>
                <a class="jsCompareAdd uk-button uk-button-small uk-button-default" data-option-id="<?= $configuration->id ?>"
                   data-id="<?= $product->id ?>" data-type="product" uk-icon="icon: list"></a>
                <?php endif; ?>
            </div>

        </div>

        <?php if ($configuratorHelper->getInstockTogglerPosition() === 'navbar') : ?>
            <div class="uk-navbar-center">
                <?php $initState = $configuratorHelper->inStockOnlyInitState($product); ?>
                <label class="tm-toggle uk-flex-between">
                    <input type="checkbox" class="tm-toggle__checkbox jsOnlyInstockGlobal"<?= $initState ? ' checked' : '' ?>>
                    <span class="tm-toggle__label tm-toggle__label--off">
                        <?= Text::_('COM_HYPERPC_ALL_POSITIONS') ?>
                    </span>
                    <span class="tm-toggle__switch">
                        <span class="tm-toggle__knob"></span>
                    </span>
                    <span class="tm-toggle__label tm-toggle__label--on uk-margin-small-left">
                        <?= Text::_('COM_HYPERPC_ONLY_INSTOCK') ?>
                    </span>
                </label>
            </div>
        <?php endif; ?>

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

            <div>
                <button class="uk-navbar-toggle uk-navbar-toggle-icon uk-icon" type="button" uk-toggle="target: #hp-offcanvas-menu" uk-navbar-toggle-icon></button>
            </div>
        </div>
    </nav>
</div>
