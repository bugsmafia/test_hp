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
 * @author      Roman Evsyukov
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;

/**
 * @var HyperPcViewMoysklad_Store $this
 */

$formAction = $this->hyper['helper']['route']->url([
    'view'   => 'moysklad_store',
    'layout' => 'edit',
    'id'     => $this->hyper['input']->get('id', 0)
]);
?>
<form action="<?= $formAction ?>" method="post" name="adminForm" id="item-form" class="form-validate hp-wrapper-form">
    <div class="row">
        <?= LayoutHelper::render('joomla.edit.title_alias', $this) ?>
    </div>

    <div class="main-card">
        <?= HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'general']); ?>
        <?= HTMLHelper::_('uitab.addTab', 'myTab', 'general', Text::_('COM_HYPERPC_STORE_NAME')) ?>
        <div class="row">
            <div class="col-12 col-lg-9">
                <?php
                $fields = [
                    'city',
                    'address',
                    'schedule_string',
                    'map_link',
                    'schedule',
                    'primary',
                ];
                echo $this->hyper['helper']['layout']->renderFieldset($this, ['fields' => $fields]);
                ?>
            </div>
            <div class="col-12 col-lg-3">
                <?= LayoutHelper::render('joomla.edit.global', $this);?>
            </div>
        </div>
        <?= HTMLHelper::_('uitab.endTab') ?>

        <?= HTMLHelper::_('uitab.endTabSet') ?>

        <input type="hidden" name="task" />
        <input type="hidden" name="boxchecked" />
        <?= HTMLHelper::_('form.token'); ?>
    </div>
</form>

