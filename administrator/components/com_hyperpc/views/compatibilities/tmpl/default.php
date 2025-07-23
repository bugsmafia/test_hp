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
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;
use HYPERPC\ORM\Entity\Compatibility;

/**
 * @var HyperPcViewCompatibilities  $this
 * @var Compatibility               $item
 */

$formAction = $this->hyper['route']->build(['view' => $this->getName()]);
?>

<form action="<?= $formAction ?>" method="post" name="adminForm" id="adminForm" class="main-card">
    <div class="row main-card-columns">
        <div id="j-main-container" class="col-12">
            <div class="hp-wrapper clearfix">
                <?= LayoutHelper::render('joomla.searchtools.default', ['view' => $this]); ?>

                <table class="table table-striped table-hover align-middle" id="orderList">
                    <thead>
                    <tr>
                        <th width="1%" class="center">
                            <?= HTMLHelper::_('grid.checkall') ?>
                        </th>
                        <th class="nowrap">
                            <?= Text::_('COM_HYPERPC_COMPATIBILITIES_TITLE') ?>
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
                    <?php foreach ($this->items as $i => $item) : ?>
                        <tr>
                            <td class="center">
                                <?= HTMLHelper::_('grid.id', $item->id, $item->id) ?>
                            </td>
                            <td>
                                <a href="<?= $item->getAdminEditUrl() ?>">
                                    <?= $item->name ?>
                                </a>
                            </td>
                            <td class="center">
                                <?= HTMLHelper::_('jgrid.published', $item->published, $i, $this->getName() . '.') ?>
                            </td>
                            <td class="center">
                                <?= $item->id ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                    <tr>
                        <td colspan="3">
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
