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

use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;
use HYPERPC\Joomla\Controller\ControllerLegacy;

defined('_JEXEC') or die('Restricted access');

/**
 * HyperPcControllerRegistration controller class for Users.
 *
 * @since  1.6
 */
class HyperPcControllerRegistration extends ControllerLegacy
{

    /**
     * Method to activate a user.
     *
     * @return  boolean  True on success, false on failure.
     *
     * @since   1.6
     */
    public function activate()
    {
        $user = JFactory::getUser();
        $input = JFactory::getApplication()->input;
        $uParams = JComponentHelper::getParams('com_users');

        // Check for admin activation. Don't allow non-super-admin to delete a super admin
        if ($uParams->get('useractivation') != 2 && $user->get('id')) {
            $this->setRedirect('index.php');

            return true;
        }

        // If user registration or account activation is disabled, throw a 403.
        if ($uParams->get('useractivation') == 0 || $uParams->get('allowUserRegistration') == 0) {
            JError::raiseError(403, JText::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN'));

            return false;
        }

        $model = $this->getModel('Registration', 'UsersModel');
        $token = $input->getAlnum('token');

        // Check that the token is in a valid format.
        if ($token === null || strlen($token) !== 32) {
            JError::raiseError(403, JText::_('JINVALID_TOKEN'));

            return false;
        }

        // Get the User ID
        $userIdToActivate = $model->getUserIdFromToken($token);

        if (!$userIdToActivate) {
            JError::raiseError(403, JText::_('COM_USERS_ACTIVATION_TOKEN_NOT_FOUND'));

            return false;
        }

        // Get the user we want to activate
        $userToActivate = JFactory::getUser($userIdToActivate);

        // Admin activation is on and admin is activating the account
        if (($uParams->get('useractivation') == 2) && $userToActivate->getParam('activate', 0)) {
            // If a user admin is not logged in, redirect them to the login page with an error message
            if (!$user->authorise('core.create', 'com_users')) {
                $activationUrl = 'index.php?option=com_users&task=registration.activate&token=' . $token;
                $loginUrl = 'index.php?option=com_users&view=login&return=' . base64_encode($activationUrl);

                // In case we still run into this in the second step the user does not have the right permissions
                $message = JText::_('COM_USERS_REGISTRATION_ACL_ADMIN_ACTIVATION_PERMISSIONS');

                // When we are not logged in we should login
                if ($user->guest) {
                    $message = JText::_('COM_USERS_REGISTRATION_ACL_ADMIN_ACTIVATION');
                }

                $this->setMessage($message);
                $this->setRedirect(JRoute::_($loginUrl, false));

                return false;
            }
        }

        // Attempt to activate the user.
        $return = $model->activate($token);

        // Check for errors.
        if ($return === false) {
            // Redirect back to the home page.
            $this->setMessage(JText::sprintf('COM_USERS_REGISTRATION_SAVE_FAILED', $model->getError()), 'error');
            $this->setRedirect('index.php');

            return false;
        }

        $useractivation = $uParams->get('useractivation');

        // Redirect to the login screen.
        if ($useractivation == 0) {
            $this->setMessage(JText::_('COM_USERS_REGISTRATION_SAVE_SUCCESS'));
            $this->setRedirect(JRoute::_('index.php?option=com_users&view=login', false));
        } elseif ($useractivation == 1) {
            $this->setMessage(JText::_('COM_USERS_REGISTRATION_ACTIVATE_SUCCESS'));
            $this->setRedirect(JRoute::_('index.php?option=com_users&view=login', false));
        } elseif ($return->getParam('activate')) {
            $this->setMessage(JText::_('COM_USERS_REGISTRATION_VERIFY_SUCCESS'));
            $this->setRedirect(JRoute::_('index.php?option=com_users&view=registration&layout=complete', false));
        } else {
            $this->setMessage(JText::_('COM_USERS_REGISTRATION_ADMINACTIVATE_SUCCESS'));
            $this->setRedirect(JRoute::_('index.php?option=com_users&view=registration&layout=complete', false));
        }

        return true;
    }

    /**
     * Method to register a user.
     *
     * @return  boolean  True on success, false on failure.
     *
     * @since   1.6
     */
    public function register()
    {
        //  Check for request forgeries.
        $this->checkToken();

        //  If registration is disabled - Redirect to login page.
        if (ComponentHelper::getParams('com_users')->get('allowUserRegistration') == 0) {
            $this->setRedirect($this->hyper['route']->build([
                'option' => 'com_users',
                'view'   => 'login'
            ]));
            return false;
        }

        /** @var HyperPcModelRegistration $model */
        $model = $this->getModel('Registration', HP_MODEL_CLASS_PREFIX);

        //  Get the user data.
        $requestData = $this->input->post->get('jform', [], 'array');

        //  Validate the posted data.
        $form = $model->getForm();
        if (!$form) {
            JError::raiseError(500, $model->getError());
            return false;
        }

        $data = $model->validate($form, $requestData);

        //  Check for validation errors.
        if ($data === false) {
            //  Get the validation messages.
            $errors = $model->getErrors();

            //  Push up to three validation messages out to the user.
            for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++) {
                if ($errors[$i] instanceof Exception) {
                    $this->hyper['app']->enqueueMessage($errors[$i]->getMessage(), 'error');
                } else {
                    $this->hyper['app']->enqueueMessage($errors[$i], 'error');
                }
            }

            //  Save the data in the session.
            $this->hyper['app']->setUserState('com_users.registration.data', $requestData);

            //  Redirect back to the registration screen.
            $this->setRedirect($this->hyper['route']->build([
                'option' => 'com_users',
                'view'   => 'registration'
            ]));

            return false;
        }

        //  Attempt to save the data.
        $return = $model->register($data);

        //  Check for errors.
        if ($return === false) {
            //  Save the data in the session.
            $this->hyper['app']->setUserState('com_users.registration.data', $data);

            //  Redirect back to the edit screen.
            $this->setMessage($model->getError(), 'error');
            $this->setRedirect($this->hyper['route']->build([
                'option' => 'com_users',
                'view'   => 'registration'
            ]));

            return false;
        }

        //  Flush the data from the session.
        $this->hyper['app']->setUserState('com_users.registration.data', null);

        //  Redirect to the profile screen.
        if ($return === 'adminactivate') {
            $this->setMessage(Text::_('COM_USERS_REGISTRATION_COMPLETE_VERIFY'));
            $this->setRedirect($this->hyper['route']->build([
                'option' => 'com_users',
                'view'   => 'registration',
                'layout' => 'complete'
            ]));
        }
        elseif ($return === 'useractivate')
        {
            $this->setMessage(Text::_('COM_USERS_REGISTRATION_COMPLETE_ACTIVATE'));
            $this->setRedirect($this->hyper['route']->build([
                'option' => 'com_users',
                'view'   => 'registration',
                'layout' => 'complete'
            ]));
        }
        elseif ($return === 'mobileactivate' && $model->getRegisterUser()->id)
        {
            $this->setMessage(Text::_('COM_USERS_REGISTRATION_COMPLETE_ACTIVATE'));
            $this->setRedirect($this->hyper['route']->build([
                'option' => 'com_users',
                'view'   => 'registration',
                'layout' => 'mobile_activate',
                'token'  => $model->getRegisterUser()->activation
            ]));
        }
        else {
            $this->setMessage(Text::_('COM_USERS_REGISTRATION_SAVE_SUCCESS'));
            $this->setRedirect($this->hyper['route']->build([
                'option' => 'com_users',
                'view'   => 'login'
            ]));
        }

        return true;
    }
}
