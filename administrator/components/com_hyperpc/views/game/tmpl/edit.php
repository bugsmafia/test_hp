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
 * @author      Sergey Kalistratov <kalistratov.s.m@gmail.com>
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;

/**
 * @var HyperPcViewGame $this
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
        <?= HTMLHelper::_('uitab.addTab', 'myTab', 'general', Text::_('COM_HYPERPC_TAB_PARAMS')) ?>

        <div class="row">
            <div class="col-lg-9">
                <?= $this->form->renderField('image', 'params') ?>
                <?= $this->form->renderField('short_desc', 'params') ?>
            </div>

            <div class="col-lg-3">
                <?= LayoutHelper::render('joomla.edit.global', $this) ?>
            </div>
        </div>

        <?= HTMLHelper::_('uitab.endTab') ?>

        <?= HTMLHelper::_('uitab.addTab', 'myTab', 'fps', Text::_('FPS')) ?>

        <div class="row">
            <div class="col-12">
                <?= $this->form->getInput('fps', 'params') ?>
            </div>
        </div>

        <?= HTMLHelper::_('uitab.endTab') ?>
        <?= HTMLHelper::_('uitab.endTabSet') ?>
    </div>

    <input type="hidden" name="task" />
    <?= HTMLHelper::_('form.token'); ?>
</form>
