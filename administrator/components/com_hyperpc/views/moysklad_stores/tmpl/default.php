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
use HYPERPC\Joomla\Model\Entity\MoyskladStore;

/**
 * @var HyperPcViewMoysklad_Stores $this
 * @var MoyskladStore $item
 */

$listOrder  = $this->escape($this->state->get('list.ordering', 'a.lft'));
$listDir    = $this->escape($this->state->get('list.direction', 'asc'));
$formAction = $this->hyper['route']->build(['view' => $this->getName()]);
$saveOrder  = $listOrder === 'a.lft';

if ($saveOrder) {
    $saveOrderingUrl = $this->hyper['helper']['route']->url([
        'task' => 'moysklad_stores.saveOrderAjax',
        'tmpl' => 'component'
    ], false);
    HTMLHelper::_('draggablelist.draggable', 'categoryList', 'adminForm', strtolower($listDir), $saveOrderingUrl, false, true);
}
?>
<form action="<?= $formAction ?>" method="post" name="adminForm" id="adminForm">
    <div class="row">
        <div class="col-md-12">
            <div id="j-main-container" class="j-main-container">
                <?= LayoutHelper::render('joomla.searchtools.default', ['view' => $this]); ?>
                <?php if (empty($this->items)) : ?>
                    <div class="alert alert-info">
                        <span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?= Text::_('INFO'); ?></span>
                        <?= Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
                    </div>
                <?php else : ?>
                    <table class="table table-striped" id="categoryList">
                        <thead>
                            <tr>
                                <th class="w-1 text-center">
                                    <?= HTMLHelper::_('searchtools.sort', '', 'a.lft', $listDir, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2') ?>
                                </th>
                                <th class="w-1 text-center">
                                    <?= HTMLHelper::_('grid.checkall') ?>
                                </th>
                                <th class="w-1 text-center">
                                    <?= Text::_('JSTATUS') ?>
                                </th>
                                <th>
                                    <?= Text::_('COM_HYPERPC_STORE_NAME') ?>
                                </th>
                                <th class="w-1 text-center">
                                    <?= Text::_('JGRID_HEADING_ID') ?>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($this->items as $i => $item) :
                                if ($item->alias === 'root') {
                                    $item->set('name', Text::_('COM_HYPERPC_ROOT_CATEGORY_TITLE'));
                                }

                                $orderKey = array_search($item->id, $this->ordering[$item->parent_id]);
                                //  Get the parents of item for sorting.
                                if ($item->level > 1) {
                                    $parentsStr = '';
                                    $_currentParentId = $item->parent_id;
                                    $parentsStr = ' ' . $_currentParentId;
                                    for ($i2 = 0; $i2 < $item->level; $i2++) {
                                        foreach ($this->ordering as $k => $v) {
                                            $v = implode('-', $v);
                                            $v = '-' . $v . '-';
                                            if (strpos($v, '-' . $_currentParentId . '-') !== false) {
                                                $parentsStr .= ' ' . $k;
                                                $_currentParentId = $k;
                                                break;
                                            }
                                        }
                                    }
                                } else {
                                    $parentsStr = '';
                                }
                                ?>
                                <?php if (!$item->isRoot()) : ?>
                                    <tr data-draggable-group="<?= $item->parent_id; ?>" data-transitions data-item-id="<?= $item->id ?>"
                                        data-level="<?= $item->level ?>" data-parents="<?= $parentsStr ?>"
                                    >
                                        <td>
                                            <span class="sortable-handler <?= $saveOrder ? 'active' : 'inactive'; ?> tip-top">
                                                <span class="icon-menu"></span>
                                            </span>
                                            <input type="text" name="order[]" size="5" value="<?= $orderKey + 1; ?>" class="width-20 text-area-order hidden" />
                                        </td>
                                        <td>
                                            <?= HtmlHelper::_('grid.id', $i, $item->id) ?>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group">
                                                <?= HTMLHelper::_('jgrid.published', $item->published, $i, 'moysklad_stores.') ?>
                                            </div>
                                        </td>
                                        <td>
                                            <?= LayoutHelper::render('joomla.html.hptreeprefix', ['level' => $item->level]); ?>
                                            <a class="hasTooltip" title="<?= Text::_('JACTION_EDIT') ?>"
                                                href="<?= $item->getEditUrl() ?>">
                                                <?= $this->escape($item->name); ?>
                                            </a>
                                        </td>
                                        <td><?= $item->id ?></td>
                                    </tr>
                                <?php else : ?>
                                    <tr>
                                        <td colspan="3"></td>
                                        <td>
                                            <?= LayoutHelper::render('joomla.html.hptreeprefix', ['level' => $item->level]); ?>
                                            <a class="hasTooltip" title="<?= Text::_('JACTION_EDIT') ?>"
                                                href="<?= $item->getEditUrl() ?>">
                                                <?= $this->escape($item->name); ?>
                                            </a>
                                        </td>
                                        <td><?= $item->id ?></td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5">
                                    <?= $this->pagination->getListFooter() ?>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <input type="hidden" name="task" />
    <input type="hidden" name="boxchecked" />
    <?= HTMLHelper::_('form.token'); ?>
</form>
