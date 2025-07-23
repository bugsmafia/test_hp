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
use JBZoo\Data\PHPArray;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Form\Field\ListField;

defined('_JEXEC') or die('Restricted access');

/**
 * Class JFormFieldAmoLeadCustomFields
 *
 * @since 2.0
 */
class JFormFieldAmoLeadCustomFields extends ListField
{

    /**
     * The form field type.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $type = 'AmoLeadCustomFields';

    /**
     * Data file name.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $_dataFileName = 'amo_crm_lead_custom_fields.php';

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
     * @return  array  The field option objects.
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    protected function getOptions()
    {
        $app  = App::getInstance();
        $file = $app['path']->get('admin:models/fields/data') . '/' . $this->_dataFileName;

        if (!File::exists($file)) {
            $customFields = $app['helper']['crm']->getLeadCustomFieldsList();
            $customFields = new PHPArray((array) $customFields->getArrayCopy());

            File::write($file, $customFields->write());
        }

        /** @noinspection PhpIncludeInspection */
        $fields = (array) include $file;

        $options = [
            [
                'value' => 0,
                'text'  => Text::_('COM_HYPERPC_AMO_CRM_LEAD_SELECT_CUSTOM_FIELDS_OPTION_LABEL')
            ]
        ];

        foreach ($fields as $field) {
            $field = new JSON($field);
            $options[$field->get('id')]['value'] = $field->get('id');
            $options[$field->get('id')]['text']  = $field->get('name');
        }

        return array_merge(parent::getOptions(), $options);
    }
}
