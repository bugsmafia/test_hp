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
use HYPERPC\Elements\Manager;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\Language\LanguageHelper;

defined('_JEXEC') or die('Restricted access');

/**
 * Class JFormFieldElements
 *
 * @since 2.0
 */
class JFormFieldElements extends ListField
{

    /**
     * Hold payment element data.
     *
     * @var     JSON
     *
     * @since   2.0
     */
    protected $_elements;

    /**
     * The form field type.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $type = 'Elements';

    /**
     * Method to get the field input markup for a generic list.
     * Use the multiple attribute to enable multiselect.
     *
     * @return  string
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public function getInput()
    {
        $this->_elements = $this->_getElements();
        if (!count($this->_elements->getArrayCopy())) {
            return sprintf(
                '<em style="color: red;">%s</em>',
                Text::_('HYPER_ELEMENT_ORDER_PAYMENTS_NOT_FIND_PAYMENTS_ELEMENTS')
            );
        }

        return parent::getInput();
    }

    /**
     * Method to get the field options.
     *
     * @return  array
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    protected function getOptions()
    {
        $options = [[
            'value' => 1,
            'text'  => Text::_('COM_HYPERPC_ELEMENTS_SELECT_DEFAULT')
        ]];

        $app = App::getInstance();

        $contentLangs = LanguageHelper::getContentLanguages();
        $langSefs = array_map(function ($langData) {
            return $langData->sef;
        }, $contentLangs);

        $langSef = $app->getLanguageSef();

        $this->_elements = $this->_getElements();
        foreach ((array) $this->_elements->getArrayCopy() as $data) {
            $data = new JSON((array) $data);

            if (is_array($data->get('name'))) {
                if (array_key_exists($langSef, $data->get('name'))) {
                    $text = $data->get('name')[$langSef];
                } elseif (array_intersect($langSefs, array_keys($data->get('name')))) {
                    $text = $data->get('name')[$langSef] ?? '';
                }
            } else {
                $text = $data->get('name');
            }

            $options[$data->get('type')]['value'] = $data->get('type');
            $options[$data->get('type')]['text']  = $text;
        }

        return array_merge(parent::getOptions(), $options);
    }

    /**
     * Get saved elements by group.
     *
     * @return  JSON
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    protected function _getElements()
    {
        $app = App::getInstance();
        return new JSON((array) $app['params']->get($this->_getElementGroup()));
    }

    /**
     * Get element group.
     *
     * @return  string
     *
     * @since   2.0
     */
    protected function _getElementGroup()
    {
        return (isset($this->element['group'])) ? (string) $this->element['group'] : Manager::ELEMENT_TYPE_ORDER;
    }
}
