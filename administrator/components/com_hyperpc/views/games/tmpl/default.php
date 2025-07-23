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

defined('_JEXEC') or die;

use HYPERPC\Joomla\Model\Entity\Game;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Session\Session;

/**
 * @var HyperPcViewGames $this
 * @var Game $item
 */

/** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('table.columns')
    ->useScript('multiselect');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$saveOrder = $listOrder === 'a.ordering';

if ($saveOrder && !empty($this->items)) {
    $saveOrderingUrl = 'index.php?option=com_hyperpc&task=games.saveOrderAjax&tmpl=component&' . Session::getFormToken() . '=1';
    HTMLHelper::_('draggablelist.draggable');
}
?>

<form action="<?= $this->hyper['route']->build(['view' => '%view']) ?>" method="post" name="adminForm" id="adminForm">
    <div class="row">
        <div class="col-md-12">
            <div id="j-main-container" class="j-main-container">
                <?= LayoutHelper::render('joomla.searchtools.default', ['view' => $this, 'options' => $searchToolsOptions]); ?>
                <?php if (empty($this->items)) : ?>
                    <div class="alert alert-info">
                        <span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?= Text::_('INFO'); ?></span>
                        <?= Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
                    </div>
                <?php else : ?>
                    <table class="table" id="fieldList">
                        <thead>
                            <tr>
                                <td class="w-1 text-center">
                                    <?= HTMLHelper::_('grid.checkall'); ?>
                                </td>
                                <th class="w-1 text-center">
                                    <?= HTMLHelper::_('searchtools.sort', '', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-sort'); ?>
                                </th>
                                <th class="w-1 text-center">
                                    <?= Text::_('JSTATUS') ?>
                                </th>
                                <th class="w-1 text-center">
                                    <?= Text::_('JTOOLBAR_DEFAULT') ?>
                                </th>
                                <th>
                                    <?= Text::_('JGLOBAL_TITLE') ?>
                                </th>
                                <th class="w-3">
                                    <?= Text::_('JGRID_HEADING_ID') ?>
                                </th>
                            </tr>
                        </thead>
                        <tbody <?php if ($saveOrder) :
                            ?> class="js-draggable" data-url="<?= $saveOrderingUrl; ?>" data-direction="<?= strtolower($listDirn); ?>"<?php
                               endif; ?>>
                            <?php foreach ($this->items as $i => $item) :
                                $editLink = $this->hyper['route']->build([
                                    'view'   => 'game',
                                    'layout' => 'edit',
                                    'id'     => $item->id
                                ]);
                                ?>
                                <tr>
                                    <td class="center">
                                        <?= HTMLHelper::_('grid.id', $i, $item->id) ?>
                                    </td>
                                    <td class="text-center">
                                        <?php
                                        $iconClass = '';
                                        if (!$saveOrder) {
                                            $iconClass = ' inactive" title="' . Text::_('JORDERINGDISABLED');
                                        }
                                        ?>
                                        <span class="sortable-handler<?= $iconClass ?>">
                                            <span class="icon-ellipsis-v" aria-hidden="true"></span>
                                        </span>
                                        <?php if ($saveOrder) : ?>
                                            <input type="text" name="order[]" size="5" value="<?= $item->ordering ?>" class="width-20 text-area-order hidden">
                                        <?php endif; ?>
                                    </td>
                                    <td class="center">
                                        <?= HTMLHelper::_('jgrid.published', (int) $item->published, $i, 'games.') ?>
                                    </td>
                                    <td class="center">
                                        <?= HTMLHelper::_('jgrid.isDefault', (int) $item->default_game, $i, 'games.') ?>
                                    </td>
                                    <td>
                                        <a href="<?= $editLink ?>"><?= $this->escape($item->name) ?></a>
                                    </td>
                                    <td>
                                        <?= (int) $item->id; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <?php // load the pagination. ?>
                    <?= $this->pagination->getListFooter(); ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <input type="hidden" name="task" />
    <input type="hidden" name="boxchecked" />
    <?= HTMLHelper::_('form.token'); ?>
</form>
