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
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;
use HYPERPC\Joomla\Model\Entity\ProductFolder;

/**
 * @var HyperPcViewProduct_Folders $this
 * @var ProductFolder $item
 */

/** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('table.columns')
    ->useScript('multiselect');

$listOrder  = $this->escape($this->state->get('list.ordering', 'a.lft'));
$listDir    = $this->escape($this->state->get('list.direction', 'asc'));

$formAction = $this->hyper['helper']['route']->url(['view' => 'product_folders']);
$saveOrder  = $listOrder === 'a.lft';

if ($saveOrder) {
    $saveOrderingUrl = $this->hyper['helper']['route']->url([
        'task' => 'product_folders.saveOrderAjax',
        'tmpl' => 'component'
    ], false);

    HTMLHelper::_('draggablelist.draggable', 'categoryList', 'adminForm', strtolower($listDir), $saveOrderingUrl, false, true);
}
?>
<form action="<?= $formAction ?>" method="post" name="adminForm" id="adminForm" class="main-card">
    <div class="row main-card-columns">
        <div id="j-main-container" class="col-12">
            <div class="hp-wrapper clearfix">
                <?= LayoutHelper::render('joomla.searchtools.default', ['view' => $this]); ?>
                <?php if (count($this->items) === 0) : ?>
                    <div class="alert alert-no-items">
                        <?= Text::_('JGLOBAL_NO_MATCHING_RESULTS') ?>
                    </div>
                <?php else : ?>
                    <table class="table table-striped" id="categoryList">
                        <thead>
                        <tr>
                            <th width="1%" class="nowrap center">
                                <?= HTMLHelper::_('searchtools.sort', '', 'a.lft', $listDir, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2') ?>
                            </th>
                            <th width="1%" class="center"></th>
                            <th width="1%" class="nowrap center">
                                <?= Text::_('JSTATUS') ?>
                            </th>
                            <th width="1%"></th>
                            <th class="nowrap">
                                <?= Text::_('JGLOBAL_TITLE') ?>
                            </th>
                            <th width="1%" class="nowrap">
                                <?= Text::_('JGRID_HEADING_ID') ?>
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($this->items as $i => $item) :
                            if ($item->alias === 'root') {
                                $item->set('title', Text::_('COM_HYPERPC_ROOT_CATEGORY_TITLE'));
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

                            $productListLink = $this->hyper['helper']['route']->url([
                                'view'      => 'moysklad_products',
                                'folder_id' => $item->id
                            ]);
                            ?>
                            <?php if (!$item->isRoot()) : ?>
                            <tr data-draggable-group="<?= $item->parent_id; ?>" data-transitions data-item-id="<?= $item->id ?>"
                                data-level="<?= $item->level ?>" data-parents="<?= $parentsStr ?>">
                                <td>
                                    <span class="sortable-handler <?= $saveOrder ? 'active' : 'inactive'; ?> tip-top">
                                        <span class="icon-menu"></span>
                                    </span>
                                    <input type="text" style="display:none" name="order[]" size="5" value="<?= $orderKey + 1; ?>" />
                                </td>
                                <td class="center">
                                    <?= HtmlHelper::_('grid.id', $i, $item->id) ?>
                                </td>
                                <td class="center">
                                    <div class="btn-group">
                                        <?php
                                        if (in_array($item->published, [HP_STATUS_PUBLISHED, HP_STATUS_UNPUBLISHED])) {
                                            echo HTMLHelper::_('jgrid.published', (int) $item->published, $i, 'product_folders.');
                                        } else {
                                            echo $this->hyper['helper']['html']->published($item->published);
                                        }
                                        ?>
                                    </div>
                                </td>
                                <td class="center">
                                    <a href="<?= $productListLink ?>" class="hasTooltip"
                                       title="<?= Text::_('COM_HYPERPC_GO_TO_PRODUCT_LIST') ?>">
                                        <span class="icon-menu-3"></span>
                                    </a>
                                </td>
                                <td>
                                    <?= LayoutHelper::render('joomla.html.hptreeprefix', ['level' => $item->level]); ?>
                                    <a class="hasTooltip" title="<?= Text::_('JACTION_EDIT') ?>"
                                       href="<?= $item->getEditUrl() ?>">
                                        <?= $this->escape($item->title); ?>
                                    </a>
                                </td>
                                <td class="center"><?= $item->id ?></td>
                            </tr>
                        <?php else : ?>
                            <tr>
                                <td colspan="4"></td>
                                <td>
                                    <?= LayoutHelper::render('joomla.html.hptreeprefix', ['level' => $item->level]); ?>
                                    <a class="hasTooltip" title="<?= Text::_('JACTION_EDIT') ?>"
                                       href="<?= $item->getEditUrl() ?>">
                                        <?= $this->escape($item->title); ?>
                                    </a>
                                </td>
                                <td class="center"><?= $item->id ?></td>
                            </tr>
                        <?php endif; ?>
                        <?php endforeach; ?>
                        </tbody>
                            <tfoot>
                            <tr>
                                <td colspan="6">
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
