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

use HYPERPC\App;
use HYPERPC\Data\JSON;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\Field\ListField;
use HYPERPC\Joomla\Model\Entity\Status;

defined('_JEXEC') or die('Restricted access');

/**
 * Class JFormFieldOrderStatus
 *
 * @since   2.0
 */
class JFormFieldOrderStatus extends ListField
{

    /**
     * The form field type.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $type = 'OrderStatus';

    /**
     * Method to get the field options.
     *
     * @return  array
     *
     * @throws  \Exception
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    protected function getOptions()
    {
        $app = App::getInstance();

        $statusList = [];
        $statuses   = $app['helper']['status']->getStatusList();

        /** @var Status $status */
        foreach ($statuses as $status) {
            if (!isset($statusList[$status->pipeline_id][$status->id])) {
                $statusList[$status->pipeline_id][$status->id]= $status;
            }
        }

        $options = [
            'no' => [
                'value' => '',
                'text'  => Text::_('COM_HYPERPC_SELECT_ORDER_STATUS')
            ]
        ];

        /** @var JSON $pipelines */
        $pipelines = $app['helper']['crm']->getPipelineTmpData();

        foreach ($statusList as $pipelineId => $statuses) {
            $pipeLineName = $pipelines->find($pipelineId . '.name');
            if ($pipeLineName && count($statuses)) {

                /** @todo refactore optgroup */
                $options[$pipelineId . ':start']['value'] = '<OPTGROUP>';
                $options[$pipelineId . ':start']['text']  = '-=' . $pipeLineName . '=-';

                foreach ($statuses as $status) {
                    $options[$status->id] = [
                        'value' => $status->id,
                        'text'  => $status->name
                    ];
                }

                $options[$pipelineId . ':end']['value'] = '</OPTGROUP>';
                $options[$pipelineId . ':end']['text']  = $pipeLineName;
            }
        }

        return array_merge(parent::getOptions(), $options);
    }
}
