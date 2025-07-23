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
use Joomla\CMS\Layout\LayoutHelper;

/**
 * @var HyperPcViewCompatibility $this
 */

$formAction = $this->hyper['route']->build([
    'id'     => '%id',
    'view'   => '%view',
    'layout' => '%layout'
]);
?>

<form action="<?= $formAction ?>" method="post" name="adminForm" id="item-form" class="form-validate">
    <?= LayoutHelper::render('joomla.edit.part.title_alias', $this) ?>

    <div class="main-card">
        <?= HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'general']) ?>
        <?= HTMLHelper::_('uitab.addTab', 'myTab', 'general', Text::_('COM_HYPERPC_TAB_COMPATIBILITY')) ?>

        <div class="row">
            <div class="col-lg-9">
                <?= $this->hyper['helper']['layout']->renderFieldset($this, ['fields' => ['legacy']]) ?>
                <hr>
                <?= $this->hyper['helper']['layout']->renderFieldset($this, ['fields' => [
                    'moysklad_separator',
                    'moysklad'
                ]]); ?>
            </div>
            <div class="col-lg-3">
                <?= LayoutHelper::render('joomla.edit.global', $this) ?>
            </div>
        </div>
        <?= HTMLHelper::_('uitab.endTab') ?>

        <?= HTMLHelper::_('uitab.addTab', 'myTab', 'publishing_data', Text::_('COM_HYPERPC_TAB_PUBLISHING')) ?>
        <div class="row">
            <div class="col-12">
                <?= LayoutHelper::render('joomla.edit.publishingdata', $this) ?>
            </div>
        </div>
        <?= HTMLHelper::_('uitab.endTab') ?>

        <?= HTMLHelper::_('uitab.endTabSet') ?>
    </div>

    <input type="hidden" name="task" />
    <?= HTMLHelper::_('form.token'); ?>
</form>
