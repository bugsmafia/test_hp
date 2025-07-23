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
 * @author      Roman Evsyukov
 * @author      Artem Vyshnevskiy
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;

/**
 * @var HyperPcViewStatus $this
 */
?>
<div class="uk-container uk-container-large">
    <table class="uk-table uk-table-divider uk-table-middle">
        <thead>
            <tr>
                <th class="uk-text-center uk-text-nowrap">
                    <?= Text::_('COM_HYPERPC_STATUS_AMO_STATUS_ID') ?>
                </th>
                <th>
                    <?= Text::_('COM_HYPERPC_STATUS_NAME_CRM') ?>
                </th>
                <th>
                    <?= Text::_('COM_HYPERPC_STATUS_NAME_MOYSKLAD') ?>
                </th>
                <th class="uk-text-center uk-text-nowrap">
                    <?= Text::_('COM_HYPERPC_STATUS_SITE_ID') ?>
                </th>
                <th class="uk-text-center">
                    <?= Text::_('COM_HYPERPC_STATUS_COLOR') ?>
                </th>
                <th>
                    <?= Text::_('COM_HYPERPC_STATUS_NAME_SITE') ?>
                </th>
                <th class="uk-text-nowrap">
                    <?= Text::_('COM_HYPERPC_STATUS_AVAILABLE_IN_USER_ACCOUNT') ?>
                </th>
                <th>
                    <?= Text::_('COM_HYPERPC_STATUS_TYPE') ?>
                </th>
                <th>
                    <?= Text::_('COM_HYPERPC_STATUS_DESCRIPTION') ?>
                </th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($this->amoPipelines as $pipelineId => $pipelineData) : ?>
                <tr class="tm-background-gray-10">
                    <td colspan="9">
                        <?= $pipelineId . ': ' . $pipelineData['name'] ?>
                    </td>
                </tr>

                <?php foreach ($pipelineData['statuses'] as $amoStatusId => $statusData) : ?>
                    <tr>
                        <td class="uk-text-center">
                            <?= $amoStatusId ?>
                        </td>
                        <td>
                            <?= $statusData['name'] ?>
                        </td>
                        <td>
                            <?= $statusData['moyskladStatus'] ?>
                        </td>
                        <td class="uk-text-center">
                            <?= $statusData['siteStatusId'] ?>
                        </td>
                        <td>
                            <div style="background: <?= $statusData['color'] ?>; width: 40px; height: 15px"></div>
                        </td>
                        <td>
                            <?= $statusData['siteName'] ?>
                        </td>

                        <?php if ($statusData['siteStatusId'] === '-') : ?>
                            <td></td>
                            <td></td>
                        <?php else : ?>
                            <td class="uk-text-center">
                                <?= $statusData['isAllowed'] ?
                                    '<span class="uk-text-success" uk-icon="eye"></span>' :
                                    '<span class="uk-text-warning" uk-icon="eye-slash"></span>'
                                ?>
                            </td>
                            <td>
                                <?php if ($statusData['isSuccess'] || $statusData['isCancel']) : ?>
                                    <?= $statusData['isSuccess'] ?
                                        '<span class="uk-text-success uk-text-nowrap"><span uk-icon" uk-icon="check"></span> ' . Text::_('COM_HYPERPC_STATUS_TYPE_SUCCESS') . '</span>' : ''
                                    ?>
                                    <?= $statusData['isCancel'] ?
                                        '<span class="uk-text-danger uk-text-nowrap"><span uk-icon" uk-icon="close"></span> ' . Text::_('COM_HYPERPC_STATUS_TYPE_CANCEL') . '</span>' : ''
                                    ?>
                                <?php else : ?>
                                    <span class="uk-text-muted"><?= Text::_('COM_HYPERPC_STATUS_TYPE_DEFAULT') ?></spn >
                                <?php endif; ?>
                            </td>
                        <?php endif; ?>

                        <td>
                            <?= $statusData['description'] ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="uk-h4">
        <?= Text::_('COM_HYPERPC_STATUS_UNMAPPED_SITE_STATUSES') ?>
    </div>
    <?php if (count($this->unmappedSiteStatuses)) : ?>
        <table class="uk-table uk-table-divider uk-table-middle uk-margin-bottom">
            <thead>
                <tr>
                    <th class="uk-text-center uk-text-nowrap">
                        <?= Text::_('COM_HYPERPC_STATUS_SITE_ID') ?>
                    </th>
                    <th>
                        <?= Text::_('COM_HYPERPC_STATUS_NAME') ?>
                    </th>
                    <th class="uk-text-center">
                        <?= Text::_('COM_HYPERPC_STATUS_PIPELINE_ID') ?>
                    </th>
                    <th class="uk-text-center uk-text-nowrap">
                        <?= Text::_('COM_HYPERPC_STATUS_AMO_STATUS_ID') ?>
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($this->unmappedSiteStatuses as $id => $status) : ?>
                    <tr>
                        <td class="uk-text-center">
                            <?= $id ?>
                        </td>
                        <td>
                            <?= $status->name ?>
                        </td>
                        <td class="uk-text-center">
                            <?= $status->pipeline_id ?>
                        </td>
                        <td class="uk-text-center">
                            <?= $status->params->get('amo_status_id', '-') ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else : ?>
        <p class="uk-alert uk-alert-success uk-margin-bottom">
            <?= Text::_('COM_HYPERPC_STATUS_NO_UNMAPPED_SITE_STATUSES') ?>
        </p>
    <?php endif; ?>
</div>
