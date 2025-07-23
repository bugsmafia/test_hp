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
 * @author      Artem Vyshnevskiy
 * @author      Roman Evsyukov
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;

/**
 * @var HyperPcViewPositions $this
 */

$formAction = $this->hyper['route']->build([
    'view'      => 'positions',
    'folder_id' => $this->folderId
]);
?>
<form action="<?= $formAction ?>" method="post" name="adminForm" id="adminForm" class="main-card">
    <div class="row main-card-columns">
        <?= $this->sidebar ?>

        <div id="j-main-container" class="col-lg-9">
            <div class="hp-wrapper clearfix">
                <?= LayoutHelper::render('joomla.searchtools.default', ['view' => $this]); ?>
                <?= $this->loadTemplate('breadcrumbs') ?>
                <table class="table table-striped" id="categoryList">
                    <thead>
                    <tr>
                        <th width="1%" class="center">
                            <?= HTMLHelper::_('grid.checkall') ?>
                        </th>
                        <th class="nowrap">
                            <?= Text::_('JGLOBAL_TITLE') ?>
                        </th>
                        <th class="nowrap">
                            <?= Text::_('COM_HYPERPC_MOYSKLAD_POSITION_TYPE') ?>
                        </th>
                        <th width="1%" class="nowrap center">
                            <?= Text::_('COM_HYPERPC_ON_SALE') ?>
                        </th>
                        <th width="7%" class="nowrap">
                            <?= Text::_('COM_HYPERPC_HEADING_PRICE') ?>
                        </th>
                        <th width="7%" class="nowrap">
                            <?= Text::_('COM_HYPERPC_SALE_PRICE') ?>
                        </th>
                        <th width="5%" class="nowrap center">
                            <?= Text::_('COM_HYPERPC_HEADING_SORTING') ?>
                        </th>
                        <th width="1%" class="nowrap center">
                            <?= Text::_('JSTATUS') ?>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (count($this->folders) > 0) : ?>
                        <?= $this->loadTemplate('folders') ?>
                    <?php elseif (count($this->items) === 0) : ?>
                        <div class="alert alert-no-items">
                            <?= Text::_('JGLOBAL_NO_MATCHING_RESULTS') ?>
                        </div>
                    <?php endif; ?>
                    <?= $this->loadTemplate('items') ?>
                    </tbody>
                    <tfoot>
                    <tr>
                        <td colspan="8">
                            <?= $this->pagination->getListFooter() ?>
                        </td>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    <input type="hidden" name="task" />
    <input type="hidden" name="boxchecked" />
    <?= HTMLHelper::_('form.token'); ?>
</form>
