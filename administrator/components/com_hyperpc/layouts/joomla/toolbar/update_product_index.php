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
 *
 * @var         array   $displayData
 */

use HYPERPC\Data\JSON;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die('Restricted access');

$data = new JSON($displayData);
$uniq = uniqid('modal-');
?>
<style>
    .btn-toolbar .modal-body {
        font-size: 14px;
    }
</style>

<div class="<?= $data->get('uniq') ?>">
    <button class="btn btn-small jsToggleModalBtn" data-bs-target="#<?= $uniq ?>">
        <span class="icon-refresh"></span>
        <?= $data->get('title') ?>
    </button>
    <div class="modal fade" id="<?= $uniq ?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header pt-3 pb-3 d-flex align-items-center">
                    <h3 class="m-0">
                        <?= $data->get('title') ?>
                    </h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= Text::_('JLIB_HTML_BEHAVIOR_CLOSE') ?>"></button>
                </div>
                <div class="modal-body">
                    <div style="padding: 10px;">
                        <div class="alert alert-info m-0">
                            <?= Text::_('COM_HYPERPC_PRODUCT_INDEX_COMPLETE_ALERT_INFO') ?>
                        </div>
                        <div class="progress progress-striped active mt-3 mb-3">
                            <div class="progress-bar bg-success" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100">
                                <span class="sr-only">
                                    <span class="jsProgressTask"></span>
                                    <span class="jsProgressVal"></span>
                                    <?= Text::_('COM_HYPERPC_PRODUCT_INDEX_COMPLETE_LABEL') ?>
                                </span>
                            </div>
                        </div>
                        <ul class="list-striped">
                            <li class="jsInStockProductsInfo"
                                data-task-desc="<?= Text::_('COM_HYPERPC_PRODUCT_INDEX_COMPLETE_ITEMS_IN_STOCK_TASK_DESC') ?>">
                                <strong>
                                    <?= Text::_('COM_HYPERPC_PRODUCT_INDEX_COMPLETE_ITEMS_IN_STOCK_LABEL') ?>
                                </strong>
                                <span class="jsProcessItem">0</span> / <span class="jsTotalItems">0</span>
                            </li>
                            <li class="jsCatalogProductsInfo hidden"
                                data-task-desc="<?= Text::_('COM_HYPERPC_PRODUCT_INDEX_COMPLETE_ITEMS_CATALOG_TASK_DESC') ?>">
                                <strong>
                                    <?= Text::_('COM_HYPERPC_PRODUCT_INDEX_COMPLETE_ITEMS_CATALOG_LABEL') ?>
                                </strong>
                                <span class="jsProcessItem">0</span> / <span class="jsTotalItems">0</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
