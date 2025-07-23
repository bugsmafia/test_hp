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

defined('_JEXEC') or die('Restricted access');

use HYPERPC\Data\JSON;
use Joomla\CMS\Factory;
use Joomla\Event\Event;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use HYPERPC\Joomla\Controller\ControllerForm;

/**
 * Class HyperPcControllerSubscription
 *
 * @since   2.0
 */
class HyperPcControllerSubscription extends ControllerForm
{

    /**
     * The context for storing internal data, e.g. record.
     *
     * @var    string
     *
     * @since  2.0
     */
    protected $context = 'COM_HYPERPC_LEAD';

    /**
     * Method to save a record.
     *
     * @param   string  $key     The name of the primary key of the URL variable.
     * @param   string  $urlVar  The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
     *
     * @return  boolean  True if successful, false otherwise.
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function save($key = null, $urlVar = null)
    {
        $output = new JSON([
            'result'  => false,
            'message' => null
        ]);

        // Check for request forgeries.
        if (!Session::checkToken()) {
            $output->set('message', Text::_('JINVALID_TOKEN'));
            $this->hyper['cms']->close($output->write());
        }

        $app = Factory::getApplication();
        $moduleId = $this->hyper['input']->get('module_id');

        /** @var \JBZoo\Data\Data $module */
        $module = $this->hyper['helper']['module']->findById($moduleId);
        if ($module->get('module') !== 'mod_hp_subscription') {
            $output->set('message', Text::_('COM_HYPERPC_ERROR_MODULE_NOT_FOUND'));
            $this->hyper['cms']->close($output->write());
        }

        $moduleParams = new JSON($module->get('params'));

        /** @var \HyperPcModelLead $model */
        $model = $this->getModel();
        /** @var \HyperPcTableLeads $table */
        $table = $model->getTable();

        $data    = $this->input->get('jform', [], 'array');
        $checkin = property_exists($table, $table->getColumnAlias('checked_out'));

        //  Determine the name of the primary key for the data.
        if (empty($key)) {
            $key = $table->getKeyName();
        }

        //  To avoid data collisions the urlVar may be different from the primary key.
        if (empty($urlVar)) {
            $urlVar = $key;
        }

        $recordId = $this->input->getInt($urlVar);

        // Populate the row id from the session.
        $data[$key] = $recordId;

        //  Access check.
        if (!$this->allowSave($data, $key)) {
            $output->set('message', Text::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'));
            $this->hyper['cms']->close($output->write());
        }

        //  Validate the posted data.
        $form = $model->getForm($data, false);
        if (!$form) {
            $output->set('message', $model->getError());
            $this->hyper['cms']->close($output->write());
        }

        //  Send an object which can be modified through the plugin event
        $objData = (object) $data;

        $dispatcher = Factory::getApplication()->getDispatcher();
        $event      = new Event('onContentNormaliseRequestData', [$this->option . '.' . $this->context, $objData, $form]);
        $dispatcher->dispatch('onContentNormaliseRequestData', $event);

        $data = (array) $objData;

        //  Test whether the data is valid.
        $validData = $model->validate($form, $data);

        //  Check for validation errors.
        if ($validData === false) {
            //  Get the validation messages.
            $messages = [];
            $errors   = $model->getErrors();
            //  Push up to three validation messages out to the user.
            for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++) {
                if ($errors[$i] instanceof \Exception) {
                    $messages[] = $errors[$i]->getMessage();
                } else {
                    $messages[] = $errors[$i];
                }
            }

            $output->set('message', implode('<br />', $messages));
            $this->hyper['cms']->close($output->write());
        }

        if (!isset($validData['tags'])) {
            $validData['tags'] = null;
        }

        $data = new JSON($validData);
        $lead = $this->hyper['helper']['lead']->getBy('email', $data->get('email'));

        $history = [];
        if ($lead->id) {
            $validData['id'] = $lead->id;
            $history = (new JSON($lead->history))->getArrayCopy();
        }

        $date = Factory::getDate();;

        $history[] = [
            'type' => $moduleParams->get('leads_type'),
            'time' => $date->toSql()
        ];

        $validData['history'] = $history;

        //  Attempt to save the data.
        if (!$model->save($validData)) {
            $output->set('message', Text::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()));
            $this->hyper['cms']->close($output->write());
        }

        //  Save succeeded, so check-in the record.
        if ($checkin && $model->checkin($validData[$key]) === false) {
            $output->set('message', Text::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError()));
            $this->hyper['cms']->close($output->write());
        }

        $langKey    = $this->text_prefix . ($recordId === 0 && $app->isClient('site') ? '_SUBMIT' : '') . '_SAVE_SUCCESS';
        $prefix     = Factory::getLanguage()->hasKey($langKey) ? $this->text_prefix : 'JLIB_APPLICATION';
        $successMsg = Text::_($prefix . ($recordId === 0 && $app->isClient('site') ? '_SUBMIT' : '') . '_SAVE_SUCCESS');

        if (!empty($moduleParams->get('success_msg'))) {
            $successMsg = $moduleParams->get('success_msg');
        }

        // Invoke the postSave method to allow for the child class to access the model.
        $this->postSaveHook($model, $validData);

        $output->set('result', true)->set('message', $successMsg);
        $this->hyper['cms']->close($output->write());
    }

    /**
     * Method to get a model object, loading it if required.
     *
     * @param   string  $name    The model name. Optional.
     * @param   string  $prefix  The class prefix. Optional.
     * @param   array   $config  Configuration array for model. Optional.
     *
     * @return  \JModelLegacy  The model.
     *
     * @since   2.0
     */
    public function getModel($name = 'Lead', $prefix = '', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }

    /**
     * Method to check if you can add a new record.
     *
     * @param   array $data
     * @return  bool
     *
     * @since   2.0
     */
    protected function allowAdd($data = [])
    {
        return true;
    }
}
