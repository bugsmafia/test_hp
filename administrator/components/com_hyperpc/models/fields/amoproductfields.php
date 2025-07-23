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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\Field\ListField;

defined('_JEXEC') or die('Restricted access');

/**
 * Class JFormFieldAmoProductFields
 *
 * @since 2.0
 */
class JFormFieldAmoProductFields extends ListField
{

    /**
     * The form field type.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $type = 'AmoProductFields';

    /**
     * Data file name.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $_dataFileName = 'amo_crm_prroduct_custom_fields.php';

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
        $options = [
            [
                'value' => 0,
                'text'  => Text::_('COM_HYPERPC_AMO_CRM_LEAD_SELECT_CUSTOM_FIELDS_OPTION_LABEL')
            ]
        ];

        $app = App::getInstance();

        /** @noinspection PhpIncludeInspection */
        $fields = (array) include $app['path']->get('admin:models/fields/data/' . $this->_dataFileName);

        foreach ($fields as $id => $name) {
            $options[$id]['value'] = $id;
            $options[$id]['text']  = $name;
        }

        return array_merge(parent::getOptions(), $options);
    }
}
