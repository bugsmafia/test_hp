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

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;

/**
 * @var HyperPcViewManager $this
 */

$uniq = uniqid('modal-');
?>
<div id="j-main-container" class="col-12 hp-manager-actions">
    <div class="row">
        <div class="col-12 col-lg-2">
            <div class="thumbnail">
                <a href="<?= $this->hyper['route']->build(['task' => 'amo.update_pipelines_list']) ?>" class="link-dark">
                    <?= $this->hyper['helper']['html']->image('img:other/amo.png', ['class' => 'img-thumbnail']) ?>
                    <div class="caption mt-1">
                        <?= Text::_('COM_HYPERPC_AMO_CRM_UPDATE_PIPELINES_AND_STATUSES') ?>
                    </div>
                </a>
            </div>
        </div>
        <div class="col-12 col-lg-2">
            <div class="thumbnail">
                <a href="<?= $this->hyper['route']->build(['task' => 'moysklad.update_status_list']) ?>" class="link-dark">
                    <?= $this->hyper['helper']['html']->image('img:other/moysklad-logo.png', ['class' => 'img-thumbnail']) ?>
                    <div class="caption mt-1">
                        <?= Text::_('COM_HYPERPC_MOYSKLAD_UPDATE_STATUS_LIST') ?>
                    </div>
                </a>
            </div>
        </div>
        <div class="col-12 col-lg-2">
            <div class="thumbnail">
                <a href="<?= $this->hyper['route']->build(['task' => 'moysklad.update_characteristics']) ?>" class="link-dark">
                    <?= $this->hyper['helper']['html']->image('img:other/moysklad-logo.png', ['class' => 'img-thumbnail']) ?>
                    <div class="caption mt-1">
                        <?= Text::_('COM_HYPERPC_MOYSKLAD_UPDATE_CHARACTERISTICS_LIST') ?>
                    </div>
                </a>
            </div>
        </div>
        
        <div class="col-12 col-lg-2">
            <div class="thumbnail">
                <a href="<?= $this->hyper['route']->build(['task' => 'deal_map.create']) ?>" class="link-dark">
                    <?= $this->hyper['helper']['html']->image('img:other/data-mapping.png', ['class' => 'img-thumbnail']) ?>
                    <div class="caption mt-1">
                        <?= Text::_('COM_HYPERPC_DEAL_MAP_CREATE') ?>
                    </div>
                </a>
            </div>
        </div>
        <div class="col-12 col-lg-2">
            <div class="thumbnail">
                <a href="#" class="jsNormalizeAccount link-dark" data-toggle="modal" data-target="#<?= $uniq ?>">
                    <?= $this->hyper['helper']['html']->image('img:user/placeholder.png', ['class' => 'img-thumbnail']) ?>
                    <div class="caption mt-1">
                        <?= Text::_('COM_HYPERPC_ACCOUNT_CLEAR_NOT_ACTUAL') ?>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <div class="modal fade" id="<?= $uniq ?>" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <?= Text::_('COM_HYPERPC_ACCOUNT_CLEAR_NOT_ACTUAL_MODAL_TILE') ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= Text::_('JLIB_HTML_BEHAVIOR_CLOSE') ?>"></button>
                </div>
                <div class="modal-body">
                    <div class="p-3">
                        <div class="alert alert-info mt-0">
                            <?= Text::_('COM_HYPERPC_INFO_NO_CLOSE_ALERT') ?>
                        </div>
                        <div class="progress progress-striped active">
                            <div class="progress-bar bg-success" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100">
                                <span class="sr-only">
                                    <span class="jsProgressTask"></span>
                                    <span class="jsProgressVal"></span>
                                </span>
                            </div>
                        </div>
                        <ul class="list-striped jsNormalizeAccountInfo">
                            <li>
                                <strong>
                                    <?= Text::_('COM_HYPERPC_ACCOUNT_NORMALIZE_ACCOUNT_INFO_PROCESS_TOTAL') ?>
                                </strong>
                                <span class="jsProcessItem">0</span> / <span class="jsTotalItems">0</span>
                            </li>
                            <li>
                                <strong>
                                    <?= Text::_('COM_HYPERPC_ACCOUNT_NORMALIZE_ACCOUNT_INFO_PROCESS_DELETED') ?>
                                </strong>
                                <span class="jsDeletedItem">0</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
