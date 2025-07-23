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
use Joomla\CMS\HTML\HTMLHelper;

/**
 * @var HyperPcViewOrder_Log    $this
 */

?>
<div class="row">
    <div class="col-12">
        <div class="accordion" id="accordion-wrapper">
            <?php foreach ($this->logs as $logType => $logs) : ?>
                <div class="accordion-item">
                    <div class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#type-<?= $logType ?>" aria-expanded="false" aria-controls="type-<?= $logType ?>">
                            <?= Text::_('COM_HYPERPC_' . strtoupper($logType)) ?>
                        </button>
                    </div>
                    <div id="type-<?= $logType ?>" class="accordion-collapse collapse">
                        <div class="accordion-body">
                            <?php if (count($logs) > 0) : ?>
                                <div class="accordion-inner">
                                    <div class="accordion" id="accordion-type-<?= $logType ?>">
                                        <?php foreach ($logs as $log) : ?>
                                            <div class="accordion-item">
                                                <div class="accordion-heading">
                                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#log-<?= $log->id ?>" aria-expanded="false" aria-controls="log-<?= $log->id ?>">
                                                        <?= Text::sprintf(
                                                            'COM_HYPERPC_ORDER_LOG_CREATE_TITLE',
                                                            HTMLHelper::date($log->created_time, Text::_('DATE_FORMAT_FILTER_DATETIME'))
                                                        ) ?>
                                                    </button>
                                                </div>
                                                <div id="log-<?= $log->id ?>" class="accordion-collapse collapse">
                                                    <div class="accordion-body">
                                                        <?php
                                                        echo implode(PHP_EOL, [
                                                            '<pre>',
                                                            '<code>',
                                                            json_encode(
                                                                $log->content->getArrayCopy(),
                                                                JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE
                                                            ),
                                                            '</code>',
                                                            '</pre>'
                                                        ]);
                                                        ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
