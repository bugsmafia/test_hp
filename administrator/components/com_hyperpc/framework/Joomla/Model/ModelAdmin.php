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
 */

namespace HYPERPC\Joomla\Model;

use HYPERPC\App;
use JBZoo\Data\Data;
use Joomla\CMS\Factory;
use Cake\Utility\Inflector;
use HYPERPC\ORM\Table\Table;
use Joomla\CMS\Language\Text;
use HYPERPC\Helper\AppHelper;
use HYPERPC\Joomla\Form\Form;
use Joomla\Registry\Registry;
use Joomla\String\StringHelper;
use Joomla\CMS\Object\CMSObject;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\MVC\Model\AdminModel;
use HYPERPC\Joomla\Model\Entity\Entity;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * Class ModelAdmin
 *
 * @property    Form $form
 * @property    Form $forms
 *
 * @package     HYPERPC\Joomla\Model
 *
 * @since       2.0
 */
abstract class ModelAdmin extends AdminModel
{

    /**
     * Hold HYPERPC application object.
     *
     * @var     App
     *
     * @since   2.0
     *
     * @deprecated  Use only $this->>hyper
     */
    public $app;

    /**
     * Hold HYPERPC application object.
     *
     * @var     App
     *
     * @since   2.0
     */
    public $hyper;

    /**
     * Create auto alias.
     *
     * @var     bool
     *
     * @since   2.0
     */
    protected $_autoAlias = false;

    /**
     * Hold model helper object.
     *
     * @var     AppHelper
     *
     * @since   2.0
     */
    protected $_helper;

    /**
     * ModelAdmin constructor.

     * @param   array $config
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->app = $this->hyper = App::getInstance();
        $this->setDbo($this->hyper['db']);
        $this->initialize($config);
    }

    /**
     * Getting the form from the model.
     *
     * @param   array   $data
     * @param   bool    $loadData
     *
     * @return  bool|Form
     *
     * @throws  \Exception
     * @throws  \InvalidArgumentException
     *
     * @since   2.0
     */
    public function getForm($data = [], $loadData = true)
    {
        $name = $this->getName();
        $form = $this->loadForm(HP_OPTION . '.' . $name, $name, [
            'control'   => 'jform',
            'load_data' => $loadData
        ]);

        if ($form === null) {
            return false;
        }

        return $form;
    }

    /**
     * Get model helper.
     *
     * @return  AppHelper|null
     *
     * @since   2.0
     */
    public function getHelper()
    {
        return $this->_helper;
    }

    /**
     * Get hidden fields.
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getHiddenFields()
    {
        return [];
    }

    /**
     * Returns a Model object, always creating it.
     *
     * @param   string  $type       The model type to instantiate
     * @param   string  $prefix     Prefix for the model class name. Optional.
     * @param   array   $config     Configuration array for model. Optional.
     *
     * @return  BaseDatabaseModel|boolean
     *
     * @since   2.0
     */
    public static function getInstance($type, $prefix = HP_MODEL_CLASS_PREFIX, $config = [])
    {
        return parent::getInstance($type, $prefix, $config);
    }

    /**
     * Method to get a single record.
     *
     * @param   null|int $pk
     *
     * @return  bool|object|Entity
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getItem($pk = null)
    {
        $pk = (!empty($pk)) ? $pk : (int) $this->getState($this->getName() . '.id');
        /** @var Table $table */
        $table = $this->getTable();

        if ($pk > 0) {
            //  Attempt to load the row.
            $return = $table->load($pk);
            //  Check for a table object error.
            if ($return === false && $table->getError()) {
                $this->setError($table->getError());
                return false;
            }
        }

        //  Convert to the JObject before adding other data.
        $properties = $table->getProperties(1);
        $entity = $table->getEntity();
        if ($entity === 'JObject') {
            $item = ArrayHelper::toObject($properties, 'JObject');

            if (property_exists($item, 'params')) {
                $registry = new Registry($item->params);
                $item->params = $registry->toArray();
            }

            if (property_exists($item, 'metadata')) {
                $registry = new Registry($item->metadata);
                $item->metadata = $registry->toArray();
            }

            return $item;
        }

        return new $entity($properties);
    }

    /**
     * Get table object.
     *
     * @param   string  $type
     * @param   string  $prefix
     * @param   array   $config
     *
     * @return  \JTable
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getTable($type = '', $prefix = HP_TABLE_CLASS_PREFIX, $config = [])
    {
        if (empty($type)) {
            $type = Inflector::camelize(Inflector::pluralize($this->getName()));
        }

        return parent::getTable($type, $prefix, $config);
    }

    /**
     * Initialize model hook method.
     *
     * @param   array $config
     *
     * @return  void
     *
     * @since   2.0
     */
    public function initialize(array $config)
    {
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  bool|CMSObject
     *
     * @throws  \Exception
     *
     * @since   2.0
     *
     * @todo    the method returns a wrong type (array instead of CMSObject)
     */
    public function loadFormData()
    {
        /** @var Entity $item */
        $item = clone $this->getItem();

        if (property_exists($item, 'published')) {
            $item->set('published', (int) $item->published);
        }

        if (property_exists($item, 'total')) {
            $item->set('total', (int) $item->total->val());
        }

        unset($item->app, $item->hyper);

        if ($item instanceof \HYPERPC\ORM\Entity\Entity) {
            $arrayData = $item->toArray();
            if (isset($arrayData['published'])) {
                $arrayData['published'] = (int) $arrayData['published'];
            }

            return $arrayData;
        }

        return $item->getArray();
    }

    /**
     * Method to save the form data.
     *
     * @param   array $data
     *
     * @return  bool
     *
     * @throws  \Exception
     * @throws  \RuntimeException
     * @throws  \InvalidArgumentException
     * @throws  \UnexpectedValueException
     *
     * @since   2.0
     */
    public function save($data)
    {
        $_data = new Data($data);
        $input = $this->hyper['input'];

        if ($this->_autoAlias === true) {
            //  Alter the title for save as copy.
            if ($input->get('task') === 'save2copy') {
                /** @var \HyperPcTableParts $origTable */
                $origTable = clone $this->getTable();
                $origTable->load($input->request->getInt('id'));

                if ($_data->get('name') === $origTable->name) {
                    list($title, $alias) = $this->_generateNewTitle($_data->get('alias'), $_data->get('name'));
                    $_data->set('name', $title);
                    $_data->set('alias', $alias);
                } else {
                    if ($_data->get('alias') === $origTable->alias) {
                        $_data->set('alias', '');
                    }
                }
            }

            $itemId = $_data->get('id');

            //  Automatic handling of alias for empty fields.
            if (in_array($input->get('task'), ['apply', 'save', 'save2new']) && (int) $itemId === 0) {
                if (empty($_data->get('alias'))) {
                    if ((int) Factory::getConfig()->get('unicodeslugs') === 1) {
                        $_data->set('alias', OutputFilter::stringURLUnicodeSlug($_data->get('name')));
                    } else {
                        $_data->set('alias', OutputFilter::stringURLSafe($_data->get('name')));
                    }

                    $table = $this->getTable();

                    if ($table->load(['alias' => $_data->get('alias')])) {
                        $msg = Text::_('COM_HYPERPC_SAVE_WARNING');
                    }

                    list($title, $alias) = $this->_generateNewTitle($_data->get('alias'), $_data->get('name'));
                    $_data->set('alias', $alias);

                    if (isset($msg)) {
                        Factory::getApplication()->enqueueMessage($msg, 'warning');
                    }
                }
            }
        }

        return parent::save($_data->getArrayCopy());
    }

    /**
     * Setup model helper.
     *
     * @param   AppHelper $helper
     *
     * @return  $this
     *
     * @since   2.0
     */
    public function setHelper(AppHelper $helper)
    {
        $this->_helper = $helper;
        return $this;
    }

    /**
     * Method to change the title & alias.
     *
     * @param   string $alias
     * @param   string $title
     *
     * @return  array
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    protected function _generateNewTitle($alias, $title)
    {
        //  Alter the title & alias
        $table = $this->getTable();

        while ($table->load(['alias' => $alias])) {
            $title = StringHelper::increment($title);
            $alias = StringHelper::increment($alias, 'dash');
        }

        return [$title, $alias];
    }

    /**
     * Method to get a form object.
     *
     * @param   string      $name       The name of the form.
     * @param   string      $source     The form source. Can be XML string if file flag is set to false.
     * @param   array       $options    Optional array of options for the form creation.
     * @param   boolean     $clear      Optional argument to force load a new form.
     * @param   string|bool $xpath      An optional xpath to search for the fields.
     *
     * @return  Form|boolean \JForm object on success, false on error.
     *
     * @throws  \InvalidArgumentException
     *
     * @since   2.0
     */
    protected function loadForm($name, $source = null, $options = [], $clear = false, $xpath = false)
    {
        //  Handle the optional arguments.
        $options['control'] = ArrayHelper::getValue((array) $options, 'control', false);

        //  Create a signature hash. But make sure, that loading the data does not create a new instance.
        $sigoptions = $options;

        if (isset($sigoptions['load_data'])) {
            unset($sigoptions['load_data']);
        }

        $hash = md5($source . serialize($sigoptions));

        //  Check if we can use a previously loaded form.
        if (!$clear && isset($this->_forms[$hash])) {
            return $this->_forms[$hash];
        }

        //  Get the form.
        Form::addFormPath(JPATH_COMPONENT   . '/models/forms');
        Form::addFieldPath(JPATH_COMPONENT  . '/models/fields');
        Form::addFormPath(JPATH_COMPONENT   . '/model/form');
        Form::addFieldPath(JPATH_COMPONENT  . '/model/field');

        try {
            $form = Form::getInstance($name, $source, $options, false, $xpath);

            /** @todo check how it worked and what it is for */
//            $form->setHiddenFields($this->getHiddenFields());

            if (isset($options['load_data']) && $options['load_data']) {
                //  Get the data for the form.
                $data = $this->loadFormData();
            } else {
                $data = [];
            }

            //  Allow for additional modification of the form, and events to be triggered.
            //  We pass the data because plugins may require it.
            $this->preprocessForm($form, $data);

            if ($data instanceof Entity) {
                unset($data->hyper, $data->app);
            }

            //  Load the data into the form after the plugins have operated.
            $form->bind($data);
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }

        //  Store the form for later.
        $this->_forms[$hash] = $form;

        return $form;
    }
}
