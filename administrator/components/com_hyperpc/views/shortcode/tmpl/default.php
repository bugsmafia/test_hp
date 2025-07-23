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
 * @author      Roman Evsyukov <roman_e@hyperpc.ru>
 * @author      Artem Vyshnevskiy
 */

defined('_JEXEC') or die;

use HYPERPC\Joomla\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

$app = Factory::getApplication();

$formAction = Route::_('index.php?' . Uri::buildQuery([
    'option' => HP_OPTION,
    'view'   => 'shortcode',
    'tmpl'   => 'component'
]));

$editor = $app->getInput()->getCmd('editor', '');
if (!empty($editor)) {
    $app->getDocument()->addScriptOptions('xtd-shortcode', array('editor' => $editor));
}

$wa = $app->getDocument()->getWebAssetManager();
$wa->useScript('views.shortcode');
?>

<div class="container-popup">
    <ul class="nav nav-tabs" id="shortcode-tab" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" aria-selected="true" href="#tabLoadPositions" data-bs-toggle="tab" data-bs-target="#tabLoadPositions" type="button" role="tab" aria-controls="tabLoadPositions" aria-selected="true">
                <?= Text::_('COM_HYPERPC_SHORTCODE_LOADPOSITIONS_TAB') ?>
            </a>
        </li>
    </ul>
    <div class="tab-content p-3" id="shortcode-tabContent">
        <div id="tabLoadPositions" class="tab-pane show active" role="tabpanel">
            <form action="<?= $formAction ?>" method="post" name="adminForm" id="adminForm" class="form-inline main-card">
                <?= $this->form->renderField('type'); ?>
                <?= $this->form->renderField('platform'); ?>
                <?= $this->form->renderField('instock'); ?>
                <?= $this->form->renderField('product-folder-ids'); ?>
                <?= $this->form->renderField('position-product-ids'); ?>
                <?= $this->form->renderField('position-part-ids'); ?>
                <?= $this->form->renderField('position-service-ids'); ?>
                <?= $this->form->renderField('field'); ?>
                <?= $this->form->renderField('field-value'); ?>
                <?= $this->form->renderField('order'); ?>
                <?= $this->form->renderField('config'); ?>
                <?= $this->form->renderField('show-fps'); ?>
                <?= $this->form->renderField('game'); ?>
                <?= $this->form->renderField('product-layout'); ?>
                <?= $this->form->renderField('price-range'); ?>
                <?= $this->form->renderField('initial-amount'); ?>
                <?= $this->form->renderField('limit'); ?>
                <?= $this->form->renderField('load-unavailable'); ?>
                <div class="control-group">
                    <div class="controls">
                        <input type="submit" id="positions-submit" class="btn btn-success" data-target="positions" value="<?= Text::_('COM_HYPERPC_SHORTCODE_SAVE_BTN') ?>" />
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
