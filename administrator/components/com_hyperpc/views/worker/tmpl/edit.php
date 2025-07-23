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
 * @var         HyperPcViewPromo_Codes $this
 */

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;

defined('_JEXEC') or die('Restricted access');

$this->useCoreUI = true;

$formAction = $this->hyper['route']->build([
    'id'   => '%id',
    'view' => '%view'
]);
?>
<form action="<?= $formAction ?>" method="post" name="adminForm" id="adminForm">
    <div class="row">
        <div class="col-12 col-lg-6">
            <?= LayoutHelper::render('joomla.edit.title_alias', $this) ?>
        </div>
    </div>

    <div class="main-card">
        <?= HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'params']); ?>

        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', Text::_('COM_HYPERPC_WORKER_NAME')); ?>
            <div class="row">
                <div class="col-lg-9">
                    <div>
                        <fieldset class="adminform">
                            <?= LayoutHelper::render('joomla.edit.params', $this) ?>
                        </fieldset>
                    </div>
                </div>
                <div class="col-lg-3">
                    <?php echo LayoutHelper::render('joomla.edit.global', $this); ?>
                </div>
            </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?= HTMLHelper::_('uitab.endTabSet') ?>
        <input type="hidden" name="task" />
        <input type="hidden" name="boxchecked" />
        <?= HTMLHelper::_('form.token'); ?>
    </div>
</form>

