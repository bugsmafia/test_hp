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

use HYPERPC\Joomla\View\ViewLegacy;
use HYPERPC\Joomla\Model\Entity\Status;

/**
 * Class HyperPcViewStatus
 *
 * @property    array $amoPipelines
 * @property    Status[] $unmappedSiteStatuses
 *
 * @since       2.0
 */
class HyperPcViewStatus extends ViewLegacy
{

    /**
     * Default display view action.
     *
     * @param   null|string $tpl
     *
     * @return  void
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public function display($tpl = null)
    {
        /** @var Status[] $statusList */
        $statusList = $this->hyper['helper']['status']->getStatusList();
        $tmpData    = $this->hyper['helper']['crm']->getPipelineTmpData();

        $allowedStatuses = $this->hyper['params']->get('account_allowed_status', [], 'arr');
        $successStatuces = $this->hyper['params']->get('account_sell_status', [], 'arr');
        $cancelStatuses  = $this->hyper['params']->get('account_cancel_status', [], 'arr');

        $moyskladStatuses = $this->hyper['helper']['moysklad']->getStatusList();

        $this->amoPipelines = [];
        foreach ($tmpData as $pipelineId => $pipelineData) {
            $this->amoPipelines[$pipelineId] = [
                'name' => $pipelineData['name']
            ];

            $statuses = [];
            foreach ($pipelineData['statuses'] as $amoStatusId => $statusData) {
                $statuses[$amoStatusId] = [
                    'name' => $statusData['name'],
                    'moyskladStatus' => '-',
                    'siteStatusId' => '-',
                    'siteName' => '-',
                    'description' => '',
                    'color' => 'transparent',
                    'isAllowed' => false,
                    'isSuccess' => false,
                    'isCancel'  => false
                ];

                $siteStatus = array_filter($statusList, function ($status) use ($pipelineId, $amoStatusId) {
                    /** @var Status $status */
                    return $status->pipeline_id === $pipelineId && $status->params->get('amo_status_id', 0, 'int') === $amoStatusId;
                });

                if (count($siteStatus)) {
                    $siteStatus = array_shift($siteStatus);
                    if ($siteStatus->params->get('moysklad_uuid')) {
                        foreach ($moyskladStatuses as $moyskladStatus) {
                            if ($moyskladStatus->id === $siteStatus->params->get('moysklad_uuid')) {
                                $statuses[$amoStatusId]['moyskladStatus'] = $moyskladStatus->name;
                                break;
                            }
                        }
                    }

                    $statuses[$amoStatusId]['siteStatusId'] = $siteStatus->id;
                    $statuses[$amoStatusId]['siteName'] = $siteStatus->name;
                    $statuses[$amoStatusId]['color'] = $siteStatus->params->get('color', 'transparent');
                    $statuses[$amoStatusId]['description'] = $siteStatus->params->get('description', '');
                    $statuses[$amoStatusId]['isAllowed'] = in_array($siteStatus->id, $allowedStatuses);
                    $statuses[$amoStatusId]['isSuccess'] = in_array($siteStatus->id, $successStatuces);
                    $statuses[$amoStatusId]['isCancel'] = in_array($siteStatus->id, $cancelStatuses);

                    unset($statusList[$siteStatus->id]);
                }
            }

            $this->amoPipelines[$pipelineId]['statuses'] = $statuses;
        }

        $this->unmappedSiteStatuses = $statusList;

        $this->hyper['doc']->setMetaData('robots', 'noindex');

        parent::display($tpl);
    }
}
