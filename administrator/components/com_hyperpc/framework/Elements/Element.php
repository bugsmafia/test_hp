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

namespace HYPERPC\Elements;

use HYPERPC\App;
use JBZoo\Utils\FS;
use JBZoo\Utils\Str;
use Cake\Utility\Hash;
use HYPERPC\Data\JSON;
use JBZoo\Utils\Filter;
use Joomla\CMS\Factory;
use HYPERPC\ORM\Table\Table;
use Joomla\CMS\Language\Text;
use HYPERPC\Joomla\Form\Form;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Form\FormFactoryInterface;

/**
 * Element class.
 *
 * @package     HYPERPC\Elements
 *
 * @since       2.0
 */
class Element
{

    const ACTION_PREFIX = 'action';

    /**
     * HYPERPC Application.
     *
     * @var     App
     *
     * @since   2.0
     */
    public $hyper;

    /**
     * Action map.
     *
     * @var     array
     *
     * @since   2.0
     */
    protected $_actionMap = [];

    /**
     * Element config.
     *
     * @var     JSON
     *
     * @since   2.0
     */
    protected $_config;

    /**
     * Form control name.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $_formControl;

    /**
     * Element group.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $_group;

    /**
     * Element meta data.
     *
     * @var     JSON
     *
     * @since   2.0
     */
    protected $_metaData;

    /**
     * Class methods.
     *
     * @var     array
     *
     * @since   2.0
     */
    protected $_methods = [];

    /**
     * Element type.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $_type;

    /**
     * Element constructor.
     *
     * @param   string  $type
     * @param   string  $group
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function __construct($type, $group)
    {
        $this->hyper  = App::getInstance();
        $this->_type  = Str::low($type);
        $this->_group = Str::low($group);

        $xMethods   = get_class_methods('HYPERPC\\Elements\\Element');
        $reflection = new \ReflectionClass($this);
        $rMethods   = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);

        $language = Factory::getApplication()->getLanguage();
        if (!array_key_exists(HP_OPTION, $language->getPaths())) {
            $language->load(HP_OPTION);
        }

        foreach ($rMethods as $rMethod) {
            $mName = $rMethod->getName();
            if (!in_array($mName, $xMethods)) {
                $this->_methods[] = strtolower($mName);
                //  Auto register the methods as action.
                $this->_actionMap[strtolower($mName)] = $mName;
            }
        }

        $this->_loadManifest();
        $this->initialize();
    }

    /**
     * Get element config.
     *
     * @param   null|string  $key      Value key.
     * @param   mixed        $default  Default value.
     * @param   null|string  $filter   Value filter.
     *
     * @return  JSON|mixed
     *
     * @since   2.0
     */
    public function getConfig($key = null, $default = null, $filter = null)
    {
        if ($key !== null) {
            $app   = $this->hyper;
            $value = $this->_config->find($key, $default, $filter);

            $contentLangs = LanguageHelper::getContentLanguages();
            $langSefs = array_map(function ($langData) {
                return $langData->sef;
            }, $contentLangs);

            $langSef = $app->getLanguageSef();

            if (is_array($value)) {
                if (array_key_exists($langSef, $value)) {
                    $value = $value[$langSef];
                } elseif (array_intersect($langSefs, array_keys($value))) {
                    $value = $value[$langSef] ?? '';
                }
            }

            return $value;
        }

        return $this->_config;
    }

    /**
     * Get title of element
     *
     * @return string
     *
     * @since  2.0
     */
    public function getTitle()
    {
        $elementTitle = $this->getConfig('title');
        $elementName  = $this->getConfig('name');

        return !empty($elementTitle) ? $elementTitle : $elementName;
    }

    /**
     * Gets the control name for given name.
     *
     * @param   string  $name
     * @param   bool    $array
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getControlName($name, $array = false)
    {
        return 'jform[elements][' . $this->getIdentifier() . '][' . $name . ']' . ($array ? '[]' : '');
    }

    /**
     * Get current CRM value.
     *
     * @return  mixed
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function getCrmValue()
    {
        return $this->getValue();
    }

    /**
     * Get element description.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getDescription()
    {
        return Text::_('HYPER_ELEMENT_' . Str::up($this->_group) . '_' . Str::up($this->_type) . '_DESCRIPTION');
    }

    /**
     * Get edit html params.
     *
     * @return  string
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function getEditParams()
    {
        $output = [];
        $form   = $this->getForm();

        $params = Hash::merge(
            $this->_commonEditFields(),
            (array) $this->_metaData->get('params', [])
        );

        $paramKeys = array_keys($params);
        foreach ($paramKeys as $key) {
            $output[] = $form->renderField($key);
        }

        return implode(PHP_EOL, $output);
    }

    /**
     * Get element form.
     *
     * @return  Form
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function getForm()
    {
        $fields = ['<form>'];

        $xml = '<?xml version="1.0" encoding="UTF-8" ?>' . "\n";

        $params = Hash::merge(
            $this->_commonEditFields(),
            (array) $this->_metaData->get('params', [])
        );

        foreach ($params as $name => $attrs) {
            if (is_callable($attrs)) {
                $attrs = call_user_func_array($attrs, ['element' => $this]);
            }

            $attrs['name'] = $name;
            $paramLangKey  = implode('_', [
                'HYPER_ELEMENT',
                Str::up($this->_group),
                Str::up($this->_type),
                'PARAM',
                Str::up($name)
            ]);

            if (!array_key_exists('label', $attrs)) {
                $attrs['label'] = $paramLangKey . '_LABEL';
            }

            if (!array_key_exists('description', $attrs) && Factory::getLanguage()->hasKey($paramLangKey . '_DESC')) {
                $attrs['description'] = $paramLangKey . '_DESC';
            }

            if (!array_key_exists('hint', $attrs) && Factory::getLanguage()->hasKey($paramLangKey . '_HINT')) {
                $attrs['hint'] = $paramLangKey . '_HINT';
            }

            $labelClass = 'control-label';
            if (array_key_exists('labelclass', $attrs)) {
                $labelClass .= ' ' . $attrs['labelclass'];
            }

            $attrs['labelclass'] = $labelClass;

            if (array_key_exists('options', $attrs)) {
                $options = $attrs['options'];
                unset($attrs['options']);

                $_options = [];
                foreach ((array) $options as $val => $title) {
                    $_options[] = '<option value="' . $val . '">' . $title . '</option>';
                }

                $fields[] = implode(PHP_EOL, [
                    '<field ' . $this->hyper['helper']['html']->buildAttrs($attrs) . '>',
                    implode(PHP_EOL, $_options),
                    '</field>'
                ]);
            } else {
                $fields[] = '<field ' . $this->hyper['helper']['html']->buildAttrs($attrs) . ' />';
            }
        }
        $fields[] = '</form>';

        $xml .= implode(PHP_EOL, $fields);

        $formControl = $this->_formControl . "[{$this->getIdentifier()}]";

        FormHelper::addFieldPath(JPATH_ROOT . '/administrator/components/com_hyperpc/models/fields');

        $fieldPaths = (array) $this->_addFieldPath();
        foreach ($fieldPaths as $fieldPath) {
            if (!is_dir($fieldPath)) {
                $fullPath = FS::clean(JPATH_ROOT . '/' . $fieldPath);
                if (is_dir($fullPath)) {
                    FormHelper::addFieldPath($fullPath);
                }
            } else {
                FormHelper::addFieldPath($fieldPath);
            }
        }

        /** @var FormFactoryInterface */
        $formFactory = Factory::getContainer()->get(FormFactoryInterface::class);
        $form = $formFactory->createForm('element.' . $this->_group . '.' . $this->_type, [
            'control' => $formControl
        ]);

        $form->load($xml);
        $form->bind($this->_loadFormData());

        return $form;
    }

    /**
     * Get element group.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getGroup()
    {
        return $this->_group;
    }

    /**
     * Get element Identifier.
     *
     * @return  mixed
     *
     * @since   2.0
     */
    public function getIdentifier()
    {
        return $this->_config->get('identifier');
    }

    /**
     * Get element layout.
     *
     * @param   string|null  $layout
     *
     * @return  null|string
     *
     * @since   2.0
     */
    public function getLayout($layout = null)
    {
        if (empty(FS::ext($layout))) {
            $layout .= '.php';
        }

        return $this->hyper['path']->get("elements:{$this->_group}/{$this->_type}/layout/{$layout}");
    }

    /**
     * Get element manager.
     *
     * @return  Manager
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getManager()
    {
        return Manager::getInstance();
    }

    /**
     * Get element meta data.
     *
     * @param   null|string  $key
     * @param   null|string  $default
     * @param   null|string  $filter
     *
     * @return  JSON|mixed|null
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function getMetaData($key = null, $default = null, $filter = null)
    {
        return ($key !== null) ? $this->_metaData->find($key, $default, $filter) : $this->_metaData;
    }

    /**
     * Get element path.
     *
     * @param   bool         $isUrl
     * @param   string|null  $source
     *
     * @return  null|string
     *
     * @since   2.0
     */
    public function getPath($source = null, $isUrl = false)
    {
        $method = ($isUrl) ? 'url' : 'get';
        if ($source === null) {
            return $this->hyper['path']->$method('elements:' . $this->_group . '/' . $this->_type);
        }

        $source = $this->hyper['helper']['macros']
            ->setData([
                'type'       => $this->_type,
                'group'      => $this->_group,
                'identifier' => $this->getIdentifier()
            ])
            ->text($source);

        return $this->hyper['path']->$method('elements:' . $this->_group . '/' . $this->_type . '/' . $source);
    }

    /**
     * Get element type.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Get element type name.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getTypeName()
    {
        return Text::_('HYPER_ELEMENT_' . Str::up($this->_group) . '_' . Str::up($this->_type) . '_NAME') ;
    }

    /**
     * Get site cart form data value by identifier.
     *
     * @return  mixed
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function getValue()
    {
        $savedValue = $this->getConfig('data.value');
        if (!empty($savedValue)) {
            return $savedValue;
        }

        static $session;
        if ($session === null) {
            $session = $this->hyper['helper']['session']->get();
        }

        return $session->find('form.' . $this->getIdentifier() . '.value');
    }

    /**
     * Check allowed action.
     *
     * @param   string|null  $action
     *
     * @return  bool|mixed
     *
     * @since   2.0
     */
    public function hasAction($action = null)
    {
        $action = strtolower($action);
        if (isset($this->_actionMap[$action])) {
            return $this->_actionMap[$action];
        }

        return false;
    }

    /**
     * Check has error.
     *
     * @return  bool|string
     *
     * @since   2.0
     */
    public function hasError()
    {
        $errors = (array) $this->hyper['cms']->getMessageQueue();
        foreach ($errors as $error) {
            $error   = new JSON($error);
            $langKey = Text::_('HYPER_ELEMENT_' . Str::up($this->_group) . '_' . Str::up($this->_type) . '_VALIDATE_ERROR');
            if ($error->get('message') === $langKey) {
                return $error->get('message');
            }
        }

        return false;
    }

    /**
     * Initialize method.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function initialize()
    {
        $this->_loadLang();
    }

    /**
     * Check is core.
     *
     * @return  bool
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function isCore()
    {
        return Filter::bool($this->getMetaData('core'));
    }

    /**
     * Check manager flag.
     *
     * @return  JSON|mixed
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function isForManager()
    {
        return $this->getConfig('for_manager', false, 'int');
    }

    /**
     * Check is hidden.
     *
     * @return  bool
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function isHidden()
    {
        return Filter::bool($this->getMetaData('hidden'));
    }

    /**
     * Check is required field.
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function isRequired()
    {
        return $this->_config->get('required', false, 'bool');
    }

    /**
     * Load assets.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function loadAssets()
    {
    }

    /**
     * Callback after create element.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function onAfterCreate()
    {
    }

    /**
     * Callback before on save item.
     *
     * @param   Table  $table
     * @param   bool   $return
     * @param   bool   $isNew
     *
     * @return  void
     *
     * @since   2.0
     */
    public function onAfterSaveItem(Table &$table, &$return, $isNew)
    {
    }

    /**
     * Callback before on save item.
     *
     * @param   Table  $table
     * @param   bool   $isNew
     *
     * @return  void
     *
     * @since   2.0
     */
    public function onBeforeSaveItem(Table &$table, $isNew)
    {
    }

    /**
     * Render layout partial.
     *
     * @param   string  $name
     * @param   array   $args
     *
     * @return  null|string
     *
     * @since   2.0
     */
    public function partial($name, array $args = [])
    {
        $layout  = $this->_config->get('layout', 'default');
        $partial = $this->getLayout($layout . '/' . $name);

        if ($partial) {
            return $this->_renderLayout($partial, $args);
        }

        return null;
    }

    /**
     * Register element action.
     *
     * @param   string  $action
     *
     * @return  $this
     *
     * @since   2.0
     */
    public function registerAction($action)
    {
        $method = self::ACTION_PREFIX . ucfirst($action);
        if (in_array(strtolower($method), $this->_methods)) {
            $this->_actionMap[strtolower($action)] = $method;
        }

        return $this;
    }

    /**
     * Render action.
     *
     * @param   array  $params
     *
     * @return  null|string
     *
     * @since   2.0
     */
    public function render(array $params = [])
    {
        $params = new JSON($params);
        $layout = $params->get('layout');
        if (!$layout) {
            $layout = $this->_config->get('layout', 'default');
        }

        if ($layout = $this->getLayout($layout)) {
            $this->loadAssets();
            return $this->_renderLayout($layout, [
                'params' => $params
            ]);
        }

        return null;
    }

    /**
     * Render admin action.
     *
     * @return  null|string
     *
     * @since   2.0
     */
    public function renderAdmin()
    {
        if ($layout = $this->getLayout('admin')) {
            return $this->_renderLayout($layout, []);
        }

        return null;
    }

    /**
     * Set element config.
     *
     * @param   array|JSON  $config
     *
     * @return  $this
     *
     * @since   2.0
     */
    public function setConfig($config)
    {
        if (!$this->_config instanceof JSON) {
            $this->_config = new JSON([]);
        }

        $app     = $this->hyper;
        $langSef = $app->getLanguageSef();

        if (is_array($config)) {
            foreach ($config as $key => $value) {
                if ($this->hyper['cms']->isClient('site') && is_array($value) && array_key_exists($langSef, $value)) {
                    $value = $value[$langSef];
                }

                $this->_config->set($key, $value);
            }
        }

        return $this;
    }

    /**
     * Setup form control.
     *
     * @param   string  $name
     *
     * @return  $this
     *
     * @since   2.0
     */
    public function setFormControl($name)
    {
        $this->_formControl = Str::low($name);
        return $this;
    }

    /**
     * Validate data.
     *
     * @param   array $data
     *
     * @return  bool|\RuntimeException
     *
     * @since   2.0
     */
    public function validate(array $data)
    {
        $data = new JSON($data);
        if ($this->isRequired() && !$data->get('value')) {
            $message = Text::sprintf('JLIB_FORM_VALIDATE_FIELD_REQUIRED', $this->_config->get('type'));
            return new \RuntimeException($message);
        }

        return true;
    }

    /**
     * Add form field path.
     *
     * @return  null
     *
     * @since   2.0
     */
    protected function _addFieldPath()
    {
        $addFieldPath = $this->_metaData->get('addFieldPath');
        if (is_callable($addFieldPath)) {
            return call_user_func_array($addFieldPath, ['element' => $this]);
        }

        return $addFieldPath;
    }

    /**
     * Element edit common fields.
     *
     * @return  array
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    protected function _commonEditFields()
    {
        $commonFields = $this->_getCommonDefaultEditFields();

        if ($this->_group === Manager::ELEMENT_TYPE_CORE) {
            $this->_onCommonFieldsForCore($commonFields);
        } elseif ($this->_group === Manager::ELEMENT_TYPE_AUTH) {
            $this->_onCommonFieldsForAuth($commonFields);
        } elseif ($this->_group === Manager::ELEMENT_TYPE_MARKETPLACE) {
            $this->_onCommonFieldsForMarketPlace($commonFields);
        } elseif ($this->_group === Manager::ELEMENT_TYPE_CREDIT_CALCULATE) {
            $this->_onCommonFieldsForCreditCalculate($commonFields);
        } elseif ($this->_group === Manager::ELEMENT_TYPE_CREDIT) {
            $this->_onCommonFieldsForCredit($commonFields);
        } elseif ($this->_group === Manager::ELEMENT_TYPE_CONFIGURATION_ACTIONS) {
            $this->_onCommonFieldsForConfiguratorActions($commonFields);
        } elseif ($this->_group === Manager::ELEMENT_TYPE_PAYMENT) {
            $this->_onCommonFieldsForPayment($commonFields);
        }

        return $commonFields;
    }

    /**
     * Get common default params.
     *
     * @return  array
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    protected function _getCommonDefaultEditFields()
    {
        $identifier = [
            'type'        => 'text',
            'class'       => 'readonly',
            'readonly'    => 'readonly',
            'label'       => 'COM_HYPERPC_ELEMENT_IDENTIFIER_LABEL',
            'description' => 'COM_HYPERPC_ELEMENT_IDENTIFIER_DESC',
            'hint'        => 'COM_HYPERPC_ELEMENT_IDENTIFIER_HINT',
            'default'     => $this->getIdentifier()
        ];

        if ($this->_group === Manager::ELEMENT_TYPE_CORE || $this->getMetaData('identifier', 0, 'bool')) {
            unset($identifier['class'], $identifier['readonly']);
        }

        return [
            'type' => [
                'type'    => 'hidden',
                'default' => $this->getType()
            ],
            'group' => [
                'type'    => 'hidden',
                'default' => $this->getGroup()
            ],
            'identifier' => $identifier,
            'name' => [
                'type'        => 'hptext',
                'class'       => 'hp-element-name',
                'label'       => 'COM_HYPERPC_ELEMENT_NAME_LABEL',
                'hint'        => 'COM_HYPERPC_ELEMENT_NAME_HINT'
            ],
            'for_manager' => [
                'type'    => 'radio',
                'default' => HP_STATUS_UNPUBLISHED,
                'class'   => 'btn-group btn-group-yesno',
                'label'   => 'HYPER_ELEMENT_CORE_PARAM_FOR_MANAGER_LABEL',
                'options' => [
                    0 => 'JNO',
                    1 => 'JYES'
                ]
            ]
        ];
    }

    /**
     * Load Form data.
     *
     * @return  array
     *
     * @since   2.0
     */
    protected function _loadFormData()
    {
        return $this->_config->getArrayCopy();
    }

    /**
     * Load element language.
     *
     * @return  void
     *
     * @since   2.0
     */
    protected function _loadLang()
    {
        Factory::getLanguage()->load('el_' . $this->getGroup() . '_' .  $this->getType(), $this->getPath(), null, true);
    }

    /**
     * Load element manifest.
     *
     * @return  void
     *
     * @since   2.0
     */
    protected function _loadManifest()
    {
        static $metaData = [];
        $className = get_class($this);
        if (!array_key_exists($className, $metaData)) {
            $path = $this->getPath() . '/' . Manager::ELEMENT_MANIFEST_FILE;
            if (FS::isFile($path)) {
                /** @noinspection PhpIncludeInspection */
                $data = require_once $path;

                $data = new JSON((array) $data);

                if (empty($data->get('type'))) {
                    $data->set('type', 'Unknown');
                }

                if (empty($data->get('group'))) {
                    $data->set('group', 'Unknown');
                }

                if ($data->get('core') === null) {
                    $data->set('core', false);
                }

                $metaData[$className] = $data;
            }
        }

        $this->_metaData = $metaData[$className];
    }

    /**
     * On common fields form auth elements.
     *
     * @param   array  $commonFields
     *
     * @return  void
     *
     * @since   2,0
     */
    protected function _onCommonFieldsForAuth(array &$commonFields)
    {
        unset($commonFields['for_manager']);

        $commonFields['is_enable'] = [
            'default' => HP_STATUS_PUBLISHED,
            'type'    => 'radio',
            'class'   => 'btn-group btn-group-yesno',
            'label'   => 'COM_HYPERPC_CART_ELEMENT_IS_ENABLE_SELECTED',
            'options' => [
                0 => 'JNO',
                1 => 'JYES'
            ]
        ];
    }

    /**
     * On common fields form configuration action elements.
     *
     * @param   array  $commonFields
     *
     * @return  void
     *
     * @since   2,0
     */
    protected function _onCommonFieldsForConfiguratorActions(array &$commonFields)
    {
        unset($commonFields['for_manager']);

        $commonFields['is_enable'] = [
            'default' => HP_STATUS_PUBLISHED,
            'type'    => 'radio',
            'class'   => 'btn-group btn-group-yesno',
            'label'   => 'COM_HYPERPC_CART_ELEMENT_IS_ENABLE_SELECTED',
            'options' => [
                0 => 'JNO',
                1 => 'JYES'
            ]
        ];

        $commonFields['enabled_groups'] = [
            'type'      => 'usergrouplist',
            'multiple'  => true,
            'label'     => 'HYPER_ELEMENT_CONFIGURATION_ACTIONS_ENABLED_GROUPS_LABEL'
        ];

        $commonFields['action_icon'] = [
            'type'  => 'text',
            'label' => 'HYPER_ELEMENT_CONFIGURATION_ACTIONS_ACTION_ICON_LABEL'
        ];

        $commonFields['account_action_name'] = [
            'type'  => 'multilanguagetext',
            'label' => 'HYPER_ELEMENT_CONFIGURATION_ACTIONS_ACTION_NAME_LABEL'
        ];
    }

    /**
     * On common fields form configuration action elements.
     *
     * @param   array  $commonFields
     *
     * @return  void
     *
     * @since   2,0
     */
    protected function _onCommonFieldsForPayment(array &$commonFields)
    {
        $commonFields['title'] = [
            'type'    => 'multilanguagetext',
            'class'   => 'hp-element-name',
            'label'       => 'COM_HYPERPC_ELEMENT_PAYMENT_TITLE_LABEL',
            'description' => 'COM_HYPERPC_ELEMENT_PAYMENT_TITLE_DESC',
            'hint'        => 'COM_HYPERPC_ELEMENT_PAYMENT_TITLE_HINT'
        ];

        $commonFields['description'] = [
            'type'        => 'multilanguagetextarea',
            'label'       => 'COM_HYPERPC_ELEMENT_DESCRIPTION_LABEL',
            'description' => 'COM_HYPERPC_ELEMENT_DESCRIPTION_DESC',
            'hint'        => 'COM_HYPERPC_ELEMENT_DESCRIPTION_HINT'
        ];
    }

    /**
     * On common fields form core elements.
     *
     * @param   array  $commonFields
     *
     * @return  void
     *
     * @since   2,0
     */
    protected function _onCommonFieldsForCore(&$commonFields)
    {
        $commonFields['title'] = [
            'type'    => 'multilanguagetext',
            'class'   => 'hp-element-name',
            'label'       => 'COM_HYPERPC_ELEMENT_CORE_TITLE_LABEL',
            'description' => 'COM_HYPERPC_ELEMENT_CORE_TITLE_DESC',
            'hint'        => 'COM_HYPERPC_ELEMENT_CORE_TITLE_HINT'
        ];

        $commonFields['layout'] = [
            'type'  => 'elementlayout',
            'path'  => $this->getPath() . '/layout',
            'label' => 'COM_HYPERPC_ELEMENTS_LAYOUT',
        ];
    }

    /**
     * On common fields form credit elements.
     *
     * @param   array  $commonFields
     *
     * @return  void
     *
     * @since   2,0
     */
    protected function _onCommonFieldsForCredit(&$commonFields)
    {

        $commonFields['title'] = [
            'type'    => 'multilanguagetext',
            'class'   => 'hp-element-name',
            'label'       => 'COM_HYPERPC_ELEMENT_CREDIT_TITLE_LABEL',
            'description' => 'COM_HYPERPC_ELEMENT_CREDIT_TITLE_DESC',
            'hint'        => 'COM_HYPERPC_ELEMENT_CREDIT_TITLE_HINT'
        ];

        $commonFields['is_enable'] = [
            'default' => HP_STATUS_PUBLISHED,
            'type'    => 'radio',
            'class'   => 'btn-group btn-group-yesno',
            'label'   => 'COM_HYPERPC_CART_ELEMENT_IS_ENABLE_SELECTED',
            'options' => [
                0 => 'JNO',
                1 => 'JYES'
            ]
        ];

        $commonFields['debug'] = [
            'default'       => 1,
            'type'          => 'radio',
            'class'         => 'btn-group btn-group-yesno',
            'label'         => 'COM_HYPERPC_CREDIT_DEBUG_MODE_LABEL',
            'options' => [
                0 => 'JNO',
                1 => 'JYES'
            ]
        ];

        $commonFields['loan_type_logic'] = [
            'type'        => 'radio',
            'default'     => 'credit',
            'class'       => 'btn-group btn-group-yesno',
            'label'       => 'HYPER_ELEMENT_CORE_PARAM_LOAN_TYPE_LABEL',
            'description' => 'HYPER_ELEMENT_CORE_PARAM_LOAN_TYPE_DESC',
            'options'     => [
                'credit'      => 'HYPER_ELEMENT_CORE_PARAM_LOAN_TYPE_CREDIT',
                'installment' => 'HYPER_ELEMENT_CORE_PARAM_LOAN_TYPE_INSTALLMENT'
            ]
        ];

        $commonFields['loan_type'] = [
            'type'        => 'multilanguagetext',
            'label'       => 'HYPER_ELEMENT_CORE_PARAM_LOAN_TYPE_TEXT_LABEL',
            'description' => 'HYPER_ELEMENT_CORE_PARAM_LOAN_TYPE_TEXT_DESC',
        ];

        $commonFields['max_price'] = [
            'type'        => 'number',
            'min'         => '0',
            'step'        => '1000',
            'label'       => 'HYPER_ELEMENT_CREDIT_PARAM_MAX_PRICE_LABEL',
            'description' => 'HYPER_ELEMENT_CREDIT_PARAM_MAX_PRICE_DESC',
        ];

        $commonFields['period'] = [
            'type'  => 'multilanguagetextarea',
            'label' => 'HYPER_ELEMENT_CREDIT_PARAM_PERIOD_LABEL'
        ];

        $commonFields['crm_tag_default'] = [
            'type'          => 'text',
            'label'         => 'HYPER_ELEMENT_CREDIT_PARAM_CRM_TAG_DEFAULT_LABEL',
            'description'   => 'HYPER_ELEMENT_CREDIT_PARAM_CRM_TAG_DEFAULT_DESC'
        ];

        $commonFields['crm_tag_success'] = [
            'type'          => 'text',
            'label'         => 'HYPER_ELEMENT_CREDIT_PARAM_CRM_TAG_SUCCESS_LABEL',
            'description'   => 'HYPER_ELEMENT_CREDIT_PARAM_CRM_TAG_SUCCESS_DESC'
        ];
    }

    /**
     * On common fields form credit calculate elements.
     *
     * @param   array  $commonFields
     *
     * @return  void
     *
     * @since   2,0
     */
    protected function _onCommonFieldsForCreditCalculate(&$commonFields)
    {
        unset($commonFields['for_manager']);

        $commonFields['title'] = [
            'type'    => 'multilanguagetext',
            'class'   => 'hp-element-name',
            'label'       => 'COM_HYPERPC_ELEMENT_CREDIT_CALCULATE_TITLE_LABEL',
            'description' => 'COM_HYPERPC_ELEMENT_CREDIT_CALCULATE_TITLE_DESC',
            'hint'        => 'COM_HYPERPC_ELEMENT_CREDIT_CALCULATE_TITLE_HINT'
        ];

        $commonFields['description'] = [
            'type'        => 'multilanguagetextarea',
            'label'       => 'COM_HYPERPC_ELEMENT_DESCRIPTION_LABEL',
            'description' => 'COM_HYPERPC_ELEMENT_DESCRIPTION_DESC',
            'hint'        => 'COM_HYPERPC_ELEMENT_DESCRIPTION_HINT'
        ];

        $commonFields['is_default'] = [
            'default' => HP_STATUS_UNPUBLISHED,
            'type'    => 'radio',
            'class'   => 'btn-group btn-group-yesno',
            'label'   => 'COM_HYPERPC_CART_ELEMENT_DEFAULT_SELECTED',
            'options' => [
                0 => 'JNO',
                1 => 'JYES'
            ]
        ];

        $commonFields['term'] = [
            'type'  => 'text',
            'label' => 'HYPER_ELEMENT_CREDIT_CALCULATE_PARAM_TERM_LABEL',
            'desc'  => 'HYPER_ELEMENT_CREDIT_CALCULATE_PARAM_TERM_DESC'
        ];

        $commonFields['rate'] = [
            'type'  => 'text',
            'label' => 'HYPER_ELEMENT_CREDIT_CALCULATE_PARAM_RATE_LABEL'
        ];

        $commonFields['discount'] = [
            'type'  => 'text',
            'label' => 'HYPER_ELEMENT_CREDIT_CALCULATE_PARAM_DISCOUNT_LABEL'
        ];
    }

    /**
     * On common fields form market place elements.
     *
     * @param   array  $commonFields
     *
     * @return  void
     *
     * @since   2,0
     */
    protected function _onCommonFieldsForMarketPlace(&$commonFields)
    {
        unset($commonFields['for_manager']);
        $commonFields['test_mode'] = [
            'type'    => 'radio',
            'default' => HP_STATUS_PUBLISHED,
            'class'   => 'btn-group btn-group-yesno',
            'label'   => 'HYPER_ELEMENT_CORE_PARAM_TEST_MODE_LABEL',
            'options' => [
                0 => 'JNO',
                1 => 'JYES'
            ]
        ];
    }

    /**
     * Render layout.
     *
     * @param   string  $layoutPath
     * @param   array   $args
     *
     * @return  null|string
     *
     * @since   2.0
     */
    protected function _renderLayout($layoutPath, array $args = [])
    {
        if ($layoutPath !== null) {
            extract($args, null);
            ob_start();
            /** @noinspection PhpIncludeInspection */
            include($layoutPath);
            $output = ob_get_contents();
            ob_end_clean();
            return $output;
        }

        return null;
    }
}
