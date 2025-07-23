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
 * @var         \HYPERPC\Joomla\Model\Entity\Worker $item
 */

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

defined('_JEXEC') or die('Restricted access');

$formAction = $this->hyper['route']->build([
    'view' => 'workers',
]);
?>
<form action="<?= $formAction ?>" method="post" name="adminForm" id="adminForm" class="main-card">
    <div class="row main-card-columns">
        <div id="j-main-container" class="col-12">
            <table class="table table-striped table-hover align-middle" id="partList">
                <thead>
                <tr>
                    <th width="1%" class="center">
                        <?= HTMLHelper::_('grid.checkall') ?>
                    </th>
                    <th class="nowrap">
                        <?= Text::_('COM_HYPERPC_WORKER_NAME') ?>
                    </th>
                    <th width="1%" class="nowrap center">
                        <?= Text::_('AmoCRM ID') ?>
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
                        'view'   => 'worker'
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
                            <?= $item->params->get('amo_responsible_user_id') ?>
                        </span>
                        </td>
                        <td class="center">
                            <?= HTMLHelper::_('jgrid.published', $item->published, $i, 'workers.') ?>
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