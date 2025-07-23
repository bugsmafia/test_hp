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
 * @author      Roman Evsyukov
 * @author      Artem Vyshnevskiy
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\Input\Input;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Layout\LayoutHelper;

/**
 * @var HyperPcViewPositions $this
 */

/** @var Input $input */
$input = $this->hyper['input'];

$showOptions = $input->getBool('show_options', true);
$hideElements = $input->getBool('hide_elements', false);
$folderId = $this->folderId;

$formToken = Session::getFormToken();
$formAction = $this->hyper['helper']['route']->url([
    'view'      => 'positions',
    'layout'    => 'modal',
    'tmpl'      => 'component',
    $formToken  => '1'
]);

/** @todo exlude parts by id */
?>
<div class="hp-wrapper clearfix layout-modal">
    <form action="<?= $formAction ?>" method="post" name="adminForm" id="adminForm">
        <div class="row main-card-columns">
            <?php if (!$hideElements) : ?>
                <?= $this->sidebar ?>
            <?php endif; ?>
            <div class="col-lg-<?= $hideElements ? 12 : 9 ?>">
                <?php
                echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]);
                if (!$hideElements) {
                    echo $this->loadTemplate('breadcrumbs');
                }
                ?>
                <?php if (empty($this->items)) : ?>
                    <div class="alert alert-info">
                        <span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
                        <?= Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
                    </div>
                <?php else : ?>
                    <table class="table table-striped table-hover align-middle" id="partList">
                        <thead>
                            <tr>
                                <th class="w-1 text-center">
                                    <?= HTMLHelper::_('grid.checkall') ?>
                                </th>
                                <th>
                                    <?= Text::_('JGLOBAL_TITLE') ?>
                                </th>
                                <th class="d-none d-md-table-cell">
                                    <?= Text::_('COM_HYPERPC_MOYSKLAD_POSITION_TYPE') ?>
                                </th>
                                <th class="w-10 text-nowrap">
                                    <?= Text::_('COM_HYPERPC_HEADING_PRICE') ?>
                                </th>
                                <th class="w-10 text-nowrap">
                                    <?= Text::_('COM_HYPERPC_SALE_PRICE') ?>
                                </th>
                                <th class="w-1 text-center">
                                    <?= Text::_('JSTATUS') ?>
                                </th>
                                <th class="w-1 text-center d-none d-md-table-cell">
                                    <?= Text::_('JGRID_HEADING_ID') ?>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (count($this->folders) > 0) {
                                echo $this->loadTemplate('folders');
                            }
                            echo $this->loadTemplate('items');
                            ?>
                        </tbody>
                    </table>

                    <?= $this->pagination->getListFooter(); ?>
                <?php endif; ?>
            </div>
        </div>

        <input type="hidden" name="folder_id" value="<?= $folderId  ?>">
        <input type="hidden" name="show_options" value="<?= (int) $showOptions ?>">
        <input type="hidden" name="hide_elements" value="<?= (int) $hideElements ?>">

        <input type="hidden" name="task" value="">
        <input type="hidden" name="boxchecked" value="0">
        <?= HTMLHelper::_('form.token'); ?>
    </form>
</div>

