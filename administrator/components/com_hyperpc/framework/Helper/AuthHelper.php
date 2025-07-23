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

namespace HYPERPC\Helper;

use HYPERPC\Data\JSON;
use Joomla\Event\Event;
use JBZoo\Utils\Filter;
use Joomla\CMS\Factory;
use Joomla\CMS\Date\Date;
use HYPERPC\ORM\Table\Table;
use Joomla\Registry\Registry;
use HYPERPC\Joomla\Form\Form;
use HYPERPC\ORM\Entity\Plugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Plugin\PluginHelper as JPluginHelper;

/**
 * Class AuthHelper
 *
 * @property    SessionHelper $_session
 *
 * @package     HYPERPC\Helper
 *
 * @since       2.0
 */
class AuthHelper extends AppHelper
{

    const FORM_STEP_FIRST   = 'auth';
    const SESSION_KEY       = 'auth';
    const SESSION_NAMESPACE = 'authorize';
    const DEFAULT_WAIT_TIME = 60;
    const PASS_CODE_LENGTH  = 4;

    /**
     * Get auth form.
     *
     * @param   string  $name
     *
     * @return  Form|\Joomla\CMS\Form\Form
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getAuthForm($name = self::FORM_STEP_FIRST)
    {
        Form::addFormPath(JPATH_ROOT . '/components/' . HP_OPTION . '/models/forms');

        $form = Form::getInstance(HP_OPTION . '.' . $name, $name, [
            'load_data' => true,
            'control'   => JOOMLA_FORM_CONTROL
        ]);

        $formData    = [];
        $sessionData = $this->getSession();
        if ($sessionData->get('user_id')) {
            $user = Factory::getUser($sessionData->get('user_id'));
            if ($user->id) {
                $formData['email'] = $user->email;
            }
        }

        if ($sessionData->get('code_id')) {
            $formData['code_id'] = $sessionData->get('code_id');
        }

        $form->bind($formData);

        $this->_preprocessForm($form, []);

        return $form;
    }

    /**
     * Get custom registration fields.
     *
     * @param   Form  $form
     *
     * @throws  \Exception
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getCustomRegistrationFields($form)
    {
        $params = ComponentHelper::getParams('com_users');

        $customFields = [];
        $option       = ($params->get('use_hyperpc_component')) ? HP_OPTION : 'com_users';

        if ($option === HP_OPTION) {
            $userPlugin = new Plugin((array) PluginHelper::getPlugin('user', 'hyperpc'));
            $allFields  = $form->getFieldset();

            if ($userPlugin->id) {
                $customRegFields = (array) $userPlugin->params->get('registration_fields');
                foreach ($customRegFields as $name) {
                    $fieldId = $form->getFormControl() . '_' . $name;
                    if (array_key_exists($fieldId, $allFields)) {
                        $customFields[] = $allFields[$fieldId];
                    }
                }
            }
        }

        return $customFields;
    }

    /**
     * Get allowed auth types from slogin component.
     *
     * @return  array
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getLoginAuthTypes()
    {
        if (!ComponentHelper::isEnabled('com_slogin')) {
            throw new Exception('Setup com_slogin', 500);
        }

        JPluginHelper::importPlugin('slogin_auth');

        $plugins    = [];
        $config     = ComponentHelper::getParams('com_slogin');

        if ($config->get('service_auth', 0)) {
            \modSLoginHelper::loadLinks($plugins, '', new Registry());
        } else {
            $dispatcher = Factory::getApplication()->getDispatcher();
            $event      = new Event('onCreateSloginLink', [&$plugins, '']);
            $dispatcher->dispatch('onCreateSloginLink', $event);
        }

        Factory::getLanguage()->load('com_slogin');

        return $plugins;
    }

    /**
     * Get random code.
     *
     * @return  int
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getRandomCode()
    {
        $min = pow(10, static::PASS_CODE_LENGTH - 1);
        $max = pow(10, static::PASS_CODE_LENGTH) - 1;
        return random_int($min, $max);
    }

    /**
     * Get com_users reload registration form.
     *
     * @throws  \Exception
     *
     * @return   Form|\Joomla\CMS\Form\Form
     *
     * @since    2.0
     */
    public function getRegistrationForm()
    {
        static $form;

        if (!$form) {
            Form::addFormPath(JPATH_ROOT . '/components/com_users/models/forms');

            $form = Form::getInstance('com_users.registration', 'registration', [
                'load_data' => true,
                'control'   => JOOMLA_FORM_CONTROL
            ]);

            $this->_preprocessForm($form, []);
        }

        return $form;
    }

    /**
     * Get session data.
     *
     * @return  JSON
     *
     * @since   2.0
     */
    public function getSession()
    {
        $session = $this->_session->get();
        return new JSON((array)  $session->get(self::SESSION_KEY, []));
    }

    /**
     * Get wait time.
     *
     * @return  int
     *
     * @since   2.0
     */
    public function getWaitTime()
    {
        return Filter::int($this->hyper['params']->get('wait_time', self::DEFAULT_WAIT_TIME));
    }

    /**
     * Initialize helper.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function initialize()
    {
        $this->_session = clone $this->hyper['helper']['session'];

        $this->_session
            ->setNamespace(self::SESSION_NAMESPACE)
            ->setType(SessionHelper::TYPE_COOKIE);
    }

    /**
     * Check is banned user by ip.
     *
     * @return  bool
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function isUserBanned()
    {
        if ($this->hyper->getUserIp() === $this->hyper['config']->get('office_ip')) {
            return false;
        }

        /** @var \HyperPcTableBanned_Ids $table */
        $table  = Table::getInstance('Banned_Ids');
        $object = $table->findByIp(null, ['a.*']);

        if ($object->get('banned_down')) {
            $nowDate  = new Date();
            $downDate = new Date($object->get('banned_down'));
            return ($downDate->getTimestamp() > $nowDate->getTimestamp());
        }

        return false;
    }

    /**
     * Render login social buttons from slogin.
     *
     * @param   string $layout
     *
     * @return  string
     *
     * @since   2.0
     */
    public function renderSocialButtons($layout = 'social_buttons')
    {
        return $this->hyper['helper']['render']->render('login/' . $layout);
    }

    /**
     * Set session data.
     *
     * @param   mixed $userId
     *
     * @return  void
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function setSession($userId)
    {
        $this->_session->set(self::SESSION_KEY, (array) [
            'user_id' => (int) $userId
        ]);
    }

    /**
     * Method to allow derived classes to preprocess the form.
     *
     * @param   Form    $form   A \JForm object.
     * @param   mixed   $data   The data expected for the form.
     * @param   string  $group  The name of the plugin group to import (defaults to "content").
     *
     * @return  void
     *
     * @throws  \Exception if there is an error in the form event.
     *
     * @see     \JFormField
     *
     * @since   2.0
     */
    protected function _preprocessForm($form, $data, $group = 'user')
    {
        //  Import the appropriate plugin group.
        PluginHelper::importPlugin($group);

        //  Get the dispatcher.
        $dispatcher = Factory::getApplication()->getDispatcher();

        //  Trigger the form preparation event.
        $results = new Event('onContentPrepareForm', [$form, $data]);

        /** @todo check how it worked and what it is for */

        //  Check for errors encountered while preparing the form.
//        if (count($results) && in_array(false, $results, true)){
//            //  Get the last error.
//            $error = $dispatcher->getError();
//
//            if (!($error instanceof \Exception)) {
//                throw new \Exception($error);
//            }
//        }
    }
}
