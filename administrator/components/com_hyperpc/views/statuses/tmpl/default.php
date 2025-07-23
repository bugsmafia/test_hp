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
 *
 * @var         HyperPcViewWorkers $this
 * @var         \HYPERPC\Joomla\Model\Entity\Status $item
 */

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;

defined('_JEXEC') or die('Restricted access');

$formAction = $this->hyper['route']->build([
    'view' => 'statuses',
]);
?>
<form action="<?= $formAction ?>" method="post" name="adminForm" id="adminForm" class="main-card">
    <div class="row main-card-columns">
        <div id="j-main-container" class="col-12">
            <?= LayoutHelper::render('joomla.searchtools.default', ['view' => $this]); ?>
            <table class="table table-striped table-hover align-middle" id="partList">
                <thead>
                <tr>
                    <th width="1%" class="center">
                        <?= HTMLHelper::_('grid.checkall') ?>
                    </th>
                    <th class="nowrap">
                        <?= Text::_('COM_HYPERPC_STATUS_NAME') ?>
                    </th>
                    <th width="1%" class="nowrap center">
                        <?= Text::_('COM_HYPERPC_AMO_PIPELINE_TITLE') ?>
                    </th>
                    <th width="1%" class="nowrap center">
                        <?= Text::_('COM_HYPERPC_AMO_STATUS_ID_TITLE') ?>
                    </th>
                    <th width="1%" class="nowrap center">
                        <?= Text::_('JSTATUS') ?>
                    </th>
                    <th width="1%" class="nowrap center">
                        <?= Text::_('ID') ?>
                    </th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($this->items as $i => $item) :
                    $editUrl = $this->hyper['route']->build([
                        'layout' => 'edit',
                        'id'     => $item->id,
                        'view'   => 'status'
                    ]);
                    ?>
                    <tr>
                        <td>
                            <?= HTMLHelper::_('grid.id', $i, $item->id) ?>
                        </td>
                        <td>
                            <a href="<?= $editUrl ?>">
                                <?= $item->name ?>
                            </a>
                        </td>
                        <td class="center">
                        <span class="badge bg-warning">
                            <?= $item->getPipelineName() ?>
                        </span>
                        </td>
                        <td class="center">
                            <?php if ($item->params->get('amo_status_id')) :
                                $statusColor = $item->params->get('color', '#fff');
                                $badgeAttrs  = ['class' => 'badge bg-info'];
                                if (!empty($statusColor) && $statusColor !== '#fff' && $statusColor !== '#ffffff') {
                                    $badgeAttrs['style'] = 'background: ' . $statusColor . ';';
                                }
                                ?>
                                <span <?= $this->hyper['helper']['html']->buildAttrs($badgeAttrs) ?>>
                                <?= $item->params->get('amo_status_id') ?>
                            </span>
                            <?php endif; ?>
                        </td>
                        <td class="center">
                            <?= HTMLHelper::_('jgrid.published', $item->published, $i, 'statuses.') ?>
                        </td>
                        <td class="center">
                            <span class="badge bg-info"><?= $item->id ?></span>
                        </td>
                    </tr>
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
        </div>
    </div>

    <input type="hidden" name="task" />
    <input type="hidden" name="boxchecked" />
    <?= HTMLHelper::_('form.token'); ?>
</form>