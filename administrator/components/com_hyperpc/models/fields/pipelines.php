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
 * Class JFormFieldPipelines
 *
 * @since 2.0
 */
class JFormFieldPipelines extends ListField
{

    const CONNECTOR_TYPE_FILE  = 'file';
    const CONNECTOR_TYPE_TABLE = 'table';

    /**
     * The form field type.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $type = 'Pipelines';

    /**
     * Name of the layout being used to render the field
     *
     * @var    string
     *
     * @since  2.0
     */
    protected $layout = 'joomla.form.field.list-fancy-select';

    /**
     * Method to get the field options.
     *
     * @return  array
     *
     * @throws  \Exception
     * @throws  \RuntimeException
     *
     * @return  array
     *
     * @since   2.0
     */
    protected function getOptions()
    {
        $connector = ((string)$this->element['connector'] !== '') ? (string) $this->element['connector'] : self::CONNECTOR_TYPE_FILE;

        $options = [
            [
                'value' => '',
                'text'  => Text::_('COM_HYPERPC_SELECT_PIPELINE_STATUS')
            ]
        ];

        if ($connector === self::CONNECTOR_TYPE_FILE) {
            $options = array_merge($options, $this->_getFromFile());
        } elseif ($connector === self::CONNECTOR_TYPE_TABLE) {
            $options = array_merge($options, $this->_getFromTable());
        }

        return array_merge(parent::getOptions(), $options);
    }

    /**
     * Get status list from table.
     *
     * @return  array
     *
     * @throws  \Exception
     * @throws  \RuntimeException
     *
     * @return  array
     *
     * @since   2.0
     */
    protected function _getFromTable()
    {
        $app = App::getInstance();
        $db  = $app['db'];

        $statusList = $app['helper']['status']->findAll([
            'conditions' => [$db->quoteName('a.published') . ' = ' . $db->quote(HP_STATUS_PUBLISHED)]
        ]);

        $statuses = [];
        if (count($statusList)) {
            /** @var Status $status */
            foreach ($statusList as $status) {
                if (!isset($statuses[$status->pipeline_id][$status->id])) {
                    $statuses[$status->pipeline_id][$status->id] = $status;
                }
            }
        }

        /** @var JSON $pipelines */
        $pipelines = $app['helper']['crm']->getPipelineTmpData();

        $options = [];
        if (count($statuses)) {
            foreach ($statuses as $pipelineId => $_statusList) {
                if (count($_statusList)) {
                    $pipelineName = $pipelines->find($pipelineId . '.name');

                    /** @todo refactore optgroup */
                    $options[$pipelineId . ':start']['value'] = '<OPTGROUP>';
                    $options[$pipelineId . ':start']['text']  = '-=' . $pipelineName . '=-';
                    /** @var Status $_status */
                    foreach ($_statusList as $_status) {
                        $value = $_status->id;
                        $options[$pipelineId . ':' . $value]['value'] = $value;
                        $options[$pipelineId . ':' . $value]['text']  = $_status->name;
                    }
                    $options[$pipelineId . ':end']['value'] = '</OPTGROUP>';
                    $options[$pipelineId . ':end']['text']  = $pipelineName;
                }
            }
        }

        return $options;
    }

    /**
     * Get status list from file.
     *
     * @return  array
     *
     * @throws  \Exception
     * @throws  \RuntimeException
     *
     * @return  array
     *
     * @since   2.0
     */
    protected function _getFromFile()
    {
        $app = App::getInstance();

        $filePath = $app['helper']['crm']->getTmpPipelineFilePath();
        if (!is_file($filePath)) {
            return [];
        }

        $data = new JSON(file_get_contents($filePath));

        $options = [];

        $pipelines = $data->getArrayCopy();
        foreach ($pipelines as $pipelineId => $pipeline) {
            $options[$pipelineId . ':start']['value'] = '<OPTGROUP>';
            $options[$pipelineId . ':start']['text']  = "-= {$pipeline['name']} =-";

            if (isset($pipeline['statuses']) && is_array($pipeline['statuses'])) {
                foreach ($pipeline['statuses'] as $status) {
                    $value = "{$pipeline['id']}:{$status['id']}";

                    $options[$value]['value'] = $value;
                    $options[$value]['text']  = "{$pipeline['name']}: {$status['name']}";
                }
            }

            $options[$pipelineId . ':end']['value'] = '</OPTGROUP>';
            $options[$pipelineId . ':end']['text']  = $pipeline['name'];
        }

        return $options;
    }
}
