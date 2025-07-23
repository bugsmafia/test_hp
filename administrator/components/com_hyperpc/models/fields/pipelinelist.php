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

defined('_JEXEC') or die('Restricted access');

/**
 * Class JFormFieldPipelineList
 *
 * @since 2.0
 */
class JFormFieldPipelineList extends ListField
{

    /**
     * The form field type.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $type = 'PipelineList';

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
        $options = [
            [
                'value' => HP_STATUS_UNPUBLISHED,
                'text'  => Text::_('COM_HYPERPC_AMO_CRM_SELECT_PIPELINE_OPTION_TITLE')
            ]
        ];

        $app = App::getInstance();

        /** @var JSON $pipelines */
        $pipelines = $app['helper']['crm']->getPipelineTmpData();

        foreach ($pipelines->getArrayCopy() as $pipeline) {
            $pipeline = new JSON($pipeline);
            $options[$pipeline->get('id')]['value'] = $pipeline->get('id');
            $options[$pipeline->get('id')]['text']  = $pipeline->get('name');
        }

        return array_merge(parent::getOptions(), $options);
    }
}
