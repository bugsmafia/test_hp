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
 * @desc        This class overrides the Joomla! Form standard class.
 */

namespace HYPERPC\Joomla\Form;

use HYPERPC\App;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Form\Form as JForm;

/**
 * Class Form
 *
 * @package HYPERPC\Joomla
 *
 * @since   2.0
 */
class Form extends JForm
{

    const TYPE_DISABLED  = 'disabled';
    const TYPE_READ_ONLY = 'readonly';

    /**
     * Hold hidden fields.
     *
     * @var     array
     *
     * @since   2.0
     */
    protected static $_hiddenFields = [];

    /**
     * Get hidden fields.
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getHiddenFields()
    {
        return self::$_hiddenFields;
    }

    /**
     * Method to get an instance of a form.
     *
     * @param   string $name The name of the form.
     * @param   string $data The name of an XML file or string to load as the form definition.
     * @param   array $options An array of form options.
     * @param   boolean $replace Flag to toggle whether form fields should be replaced if a field already exists with the same group/name.
     * @param   string|boolean  $xpath An optional xpath to search for the fields.
     *
     * @return  Form|JForm JForm instance.
     *
     * @throws  \InvalidArgumentException if no data provided.
     * @throws  \RuntimeException if the form could not be loaded.
     *
     * @since   2.0
     */
    public static function getInstance($name, $data = null, $options = [], $replace = true, $xpath = false)
    {
        return parent::getInstance($name, $data, $options, $replace, $xpath);
    }

    /**
     * Setup hidden fields.
     *
     * @param   array $fields
     *
     * @since   2.0
     */
    public function setHiddenFields(array $fields)
    {
        if (!count(self::$_hiddenFields)) {
            self::$_hiddenFields = $fields;
        }
    }

    /**
     * Method to load, setup and return a JFormField object based on field data.
     *
     * @param   string $element The XML element object representation of the form field.
     * @param   string $group The optional dot-separated form group path on which to find the field.
     * @param   mixed $value The optional value to use as the default for the field.
     *
     * @return  FormField|boolean The JFormField object for the field or boolean false on error.
     *
     * @throws \Exception
     *
     * @since   2.0
     */
    protected function loadField($element, $group = null, $value = null)
    {
        //  Make sure there is a valid SimpleXMLElement.
        if (!($element instanceof \SimpleXMLElement)) {
            return false;
        }

        //  Get the field type.
        $hiddenFields = self::$_hiddenFields;
        $elName       = (string) $element['name'];
        $type         = $element['type'] ? (string) $element['type'] : 'text';

        if (!App::isDevUser() && count($hiddenFields)) {
            foreach ($hiddenFields as $fieldKey => $fieldType) {
                $fieldGroup = '';
                if (preg_match('/\./', $fieldKey)) {
                    list ($fieldGroup, $fieldKey) = explode('.', $fieldKey, 2);
                }

                if ($elName === $fieldKey && $fieldGroup === $group) {
                    if ($fieldType === self::TYPE_DISABLED) {
                        if ($element->attributes()->disabled === null) {
                            $element->addAttribute(self::TYPE_DISABLED, 'disabled');
                        }
                    } elseif ($fieldType === self::TYPE_READ_ONLY) {
                        $element->attributes()->class = ' readonly';
                        $element->addAttribute(self::TYPE_READ_ONLY, 'readonly');
                    } else {
                        $element->attributes()->type = $type = $fieldType;
                    }
                }
            }
        }

        //  Load the JFormField object for the field.
        $field = FormHelper::loadFieldType($type);

        // If the object could not be loaded, get a text field object.
        if ($field === false) {
            $field = FormHelper::loadFieldType('text');
        }

        /**
         * Get the value for the form field if not set.
         * Default to the translated version of the 'default' attribute
         * if 'translate_default' attribute if set to 'true' or '1'
         * else the value of the 'default' attribute for the field.
         */
        if ($value === null) {
            $default = (string) ($element['default'] ? $element['default'] : $element->default);

            if (($translate = $element['translate_default']) && ((string) $translate == 'true' || (string) $translate == '1')) {
                $lang = Factory::getLanguage();

                if ($lang->hasKey($default)) {
                    $debug = $lang->setDebug(false);
                    $default = Text::_($default);
                    $lang->setDebug($debug);
                } else {
                    $default = Text::_($default);
                }
            }

            $value = $this->getValue((string) $element['name'], $group, $default);
        }

        //  Setup the JFormField object.
        $field->setForm($this);

        if ($field->setup($element, $value, $group)) {
            return $field;
        }

        return false;
    }
}
