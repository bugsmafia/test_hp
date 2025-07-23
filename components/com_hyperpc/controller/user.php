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

defined('_JEXEC') or die('Restricted access');

use JBZoo\Utils\Str;
use HYPERPC\Data\JSON;
use Joomla\CMS\Factory;
use Joomla\Event\Event;
use HYPERPC\ORM\Entity\User;
use HYPERPC\Elements\Element;
use HYPERPC\Elements\Manager;
use HYPERPC\Joomla\Form\Form;
use Joomla\CMS\Language\Text;
use HYPERPC\Helper\UserHelper;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Object\CMSObject;
use HYPERPC\Helper\SessionHelper;
use HYPERPC\Elements\ElementAuth;
use HYPERPC\Joomla\Model\ModelForm;
use Joomla\CMS\Plugin\PluginHelper;
use HYPERPC\Joomla\Controller\ControllerLegacy;

/**
 * HyperPcControllerUser controller class for Users.
 *
 * @property    SessionHelper   $_session
 *
 * @since  2.0
 */
class HyperPcControllerUser extends ControllerLegacy
{

    /**
     * Hook on initialize controller.
     *
     * @param   array $config
     *
     * @return  void
     *
     * @since   2.0
     *
     * @SuppressWarnings("unused")
     */
    public function initialize(array $config)
    {
        $this->_session = $this->hyper['helper']['session'];

        $this->_session
            ->setType(SessionHelper::TYPE_COOKIE)
            ->setNamespace(UserHelper::SESSION_NAMESPACE);

        $this
            ->registerTask('ajax-login', 'ajaxLogin')
            ->registerTask('check-user', 'checkUser')
            ->registerTask('ajax-registration', 'ajaxRegistration')
            ->registerTask('ajax-edit-user-value', 'ajaxEditUserValue')
            ->registerTask('ajax-edit-merge-confirm', 'ajaxEditMergeConfirm')
            ->registerTask('ajax-edit-user-check-value', 'ajaxEditUserCheckValue');
    }

    /**
     * Confirmation of merge user accounts.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function ajaxEditMergeConfirm()
    {
        $this->hyper['cms']->setHeader('Content-Type', 'application/json');

        $output = new JSON([
            'message' => ''
        ]);

        if (!$this->hyper['user']->id) {
            JError::raiseError(401, JText::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN'));
        }

        $elements       = $this->_getAuthElements();
        $elementType    = $this->_getRequestElementType();
        $elementTypeTry = (int) $this->_session->get()->$elementType;
        $captchaCount   = (int) $this->_session->get()->captcha;

        if (empty($this->hyper['input']->get('g-recaptcha-response')) && $elementTypeTry === 0 && $captchaCount === 0) {
            $this->_forceSetCaptcha($elements[$elementType], $output);
            $this->hyper['cms']->close($output->write());
        } else {
            $this->_session->set('captcha', 0);
        }

        /** @var ElementAuth $element */
        $element = $elements[$elementType];

        $newPassword = $this->hyper['helper']['auth']->getRandomCode();
        $element->setNewPassword($newPassword);

        /** @var HyperPcModelUser_Code $cModel */
        $cModel = ModelForm::getInstance('User_Code');

        $isSave = $cModel->save([
            'id'      => null,
            'code'    => $newPassword,
            'user_id' => $this->hyper['user']->id
        ]);

        if (!$isSave) {
            $output->set('message', Text::_('COM_HYPERPC_AUTH_SIGN_IN_SAVE_CODE_ERROR'));
            $this->hyper['cms']->close($output->write());
        }

        if (!$element->sendEditCode($output)) {
            $this->hyper['cms']->close($output->write());
        }

        $output->set('result', true);
        $output->set('user', base64_encode($this->hyper['user']->id . '::' . $cModel->getDbo()->insertid()));

        $this->hyper['cms']->close($output->write());
    }

    /**
     * Ajax action for edit auth value (second step).
     *
     * @return  void
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function ajaxEditUserCheckValue()
    {
        $this->hyper['cms']->setHeader('Content-Type', 'application/json');

        $output = new JSON([
            'result' => false,
            'message' => null
        ]);

        $userId = $this->hyper['input']->get('user_id');
        $codeId = $this->hyper['input']->get('code_id');

        /** @var HyperPcModelUser_Code $cModel */
        $cModel = ModelForm::getInstance('User_Code');

        /** @var CMSObject $code */
        $code = $cModel->getItem($codeId);

        if (!Session::checkToken() || !$this->hyper['user']->id || !$code->id) {
            $output->set('message', Text::_('COM_HYPERPC_AUTH_SIGN_IN_TOKEN_ERROR'));
            $this->hyper['cms']->close($output->write());
        }

        $pwd = implode((array) $this->hyper['input']->get('pwd', [], 'array'));

        if ((string) base64_decode($code->get('code')) !== (string)  $pwd) {
            $output->set('message', Text::_('COM_HYPERPC_AUTH_NO_CURRENT_PASSWORD_ERROR'));
            $this->hyper['cms']->close($output->write());
        }

        $elements = $this->_getAuthElements();
        $elType   = $this->_getRequestElementType();

        if (!array_key_exists($elType, $elements)) {
            $output->set('message', Text::sprintf('COM_HYPERPC_ERROR_NOT_FIND_AUTH_ELEMENT', $elType));
            $this->hyper['cms']->close($output->write());
        }

        /** @var ElementAuth $element */
        $element = $elements[$elType];

        $user      = $this->hyper['helper']['user']->findById($userId);
        $oldUserId = (string) $element->getUserByRequest()->get('id');
        if (!$element->checkEditUniqueValue($output) && $userId !== $oldUserId) {
            $reassignedPhone = $this->hyper['helper']['user']->reassignPhone($oldUserId, $userId);
            if (!empty($reassignedPhone)) {
                $output->set('phone', (string) $reassignedPhone);
            }

            $this->hyper['helper']['user']->reassignUserData($oldUserId, $userId);
        }

        $element->onSuccessEditValue($user, $output);

        $elementTypeTry = (int) $this->_session->get()->$elType;
        $this->_session->set($elType, $elementTypeTry + 1);

        $this->hyper['cms']->close($output->write());
    }

    /**
     * Ajax action for edit auth value (first step).
     *
     * @return  void
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function ajaxEditUserValue()
    {
        $this->hyper['cms']->setHeader('Content-Type', 'application/json');

        $output = new JSON([
            'result'  => false,
            'message' => null
        ]);

        if (!$this->hyper['user']->id) {
            $output->set('message', Text::_('COM_HYPERPC_ERROR_PLEASE_AUTH'));
            $this->hyper['cms']->close($output->write());
        }

        $elements       = $this->_getAuthElements();
        $elementType    = $this->_getRequestElementType();
        $elementTypeTry = (int) $this->_session->get()->$elementType;
        $captchaCount   = (int) $this->_session->get()->captcha;

        /** @var ElementAuth $element */
        $element = $elements[$elementType];
        if (
            $elementType === 'mobile' && $this->hyper['user']->getPhone() === $element->getEditRequestValue() ||
            $elementType === 'email' && $this->hyper['user']->email === $element->getEditRequestValue()
        ) {
            $output->set('message', Text::_('COM_HYPERPC_ERROR_DUPLICATED_AUTH_ELEMENT_' . Str::up($elementType)));
            $this->hyper['cms']->close($output->write());
        }

        //  Check captcha.
        if ($captchaVal = $this->hyper['input']->get('g-recaptcha-response')) {
            $element->setUseCaptcha(true);
            $form = $element->getAuthForm();

            $validData = [
                'captcha' => $captchaVal
            ];

            if ($element->getType() === 'mobile') {
                $validData['phone'] = $element->getEditRequestValue();
            } elseif ($element->getType() === 'email') {
                $validData['email'] = $element->getEditRequestValue();
            }

            $form->validate($validData);

            $formErrors = (array) $form->getErrors();
            if (count($formErrors) > 0) {
                $messages = [];
                /** @var Exception $formError */
                foreach ($formErrors as $formError) {
                    $messages[] = $formError->getMessage();
                }

                $output
                    ->set('captcha', $form->getInput('captcha'))
                    ->set('message', implode('<br />', $messages));

                $this->hyper['cms']->close($output->write());
            } else {
                $this->_session->set('captcha', $captchaCount + 1);
            }
        }

        if (empty($this->hyper['input']->get('g-recaptcha-response')) && $elementTypeTry > 0) {
            $this->_forceSetCaptcha($elements[$elementType], $output);
            $this->hyper['cms']->close($output->write());
        }

        if (!array_key_exists($elementType, $elements)) {
            $output->set('message', Text::sprintf('COM_HYPERPC_ERROR_NOT_FIND_AUTH_ELEMENT', $elementType));
            $this->hyper['cms']->close($output->write());
        }

        if (!$element->getEditRequestValue()) {
            $output->set('message', Text::_('HYPER_ELEMENT_AUTH_EMAIL_EDIT_ACCOUNT_ERROR_' . Str::up($elementType) . '_EMPTY'));
            $this->hyper['cms']->close($output->write());
        }

        $reassignUser = $this->hyper['input']->post->get('reassignUser');
        if (!$element->checkEditUniqueValue($output) && !$reassignUser) {
            $output->set('notUnique', true);
            $output->set('form', $element->getMergeConfirmForm());

            if (empty($this->hyper['input']->get('g-recaptcha-response')) && $captchaCount < $elementTypeTry) {
                $this->_forceSetCaptcha($elements[$elementType], $output);
            }

            $this->hyper['cms']->close($output->write());
        }

        $newPassword = $this->hyper['helper']['auth']->getRandomCode();

        $element->setNewPassword($newPassword);

        /** @var HyperPcModelUser_Code $cModel */
        $cModel = ModelForm::getInstance('User_Code');

        $isSave = $cModel->save([
            'id'      => null,
            'code'    => $newPassword,
            'user_id' => $this->hyper['user']->id
        ]);

        if (!$isSave) {
            $output->set('message', Text::_('COM_HYPERPC_AUTH_SIGN_IN_SAVE_CODE_ERROR'));
            $this->hyper['cms']->close($output->write());
        }

        if (!$element->sendEditCode($output)) {
            $this->hyper['cms']->close($output->write());
        }

        $output
            ->set('result', true)
            ->set('user', base64_encode($this->hyper['user']->id . '::' . $cModel->getDbo()->insertid()));

        $element->onSuccessSendEditCode($output);

        $this->hyper['cms']->close($output->write());
    }

    /**
     * Force set captcha
     *
     * @param   ElementAuth $element
     * @param   JSON $output
     *
     * @return  void
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    protected function _forceSetCaptcha(ElementAuth $element, JSON &$output)
    {
        $element->setUseCaptcha(true);
        $form = $element->getAuthForm();

        $output
            ->set('message', Text::_('COM_HYPERPC_ERROR_USE_CAPTCHA'))
            ->set('captcha', $form->getInput('captcha'));
    }

    /**
     * Ajax login action.
     *
     * @return  void
     *
     * @since   2.0
     *
     * @deprecated
     */
    public function ajaxLogin()
    {
        $output = new JSON([
            'result'  => false,
            'user'    => null,
            'message' => Text::_('COM_HYPERPC_USERS_AUTH_ERROR')
        ]);

        $dataHash = base64_decode((string) $this->hyper['input']->get('data'));
        list ($username, $password, $remember) = explode(':', $dataHash, 3);
        $password = urldecode($password);

        $isAuth = $this->hyper['cms']->login([
            'username' => $username,
            'password' => $password
        ], [
            'remember' => $remember
        ]);

        if (!$isAuth) {
            $output->set('message', Text::_('COM_HYPERPC_USERS_AUTH_ERROR_UN_CURRENT_DATA'));
            $this->hyper['cms']->close($output->write());
        }

        $output
            ->set('result', true)
            ->set('message', Text::_('COM_HYPERPC_USERS_SUCCESS_AUTH'))
            ->set('user', [
                'id'        => $this->hyper['user']->id,
                'name'      => $this->hyper['user']->name,
                'email'     => $this->hyper['user']->email,
                'username'  => $this->hyper['user']->username,
                'token'     => Session::getFormToken()
            ]);

        $this->hyper['cms']->close($output->write());
    }

    /**
     * Ajax registration action.
     *
     * @return  void
     *
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     *
     * @deprecated
     */
    public function ajaxRegistration()
    {
        $output = new JSON([
            'result'   => false,
            'user'     => null,
            'redirect' => null,
            'message'  => Text::_('COM_HYPERPC_USERS_REGISTRATION_ERROR')
        ]);

        $data = new JSON($this->hyper['input']->get('jform', [], 'array'));

        /** @var HyperPcModelRegistration $model */
        $model = $this->getModel('Registration', HP_MODEL_CLASS_PREFIX);

        //  Validate the posted data.
        $form = $model->getForm();
        if (!$form) {
            $output->set('message', $model->getError());
            $this->hyper['cms']->close($output->write());
        }

        $validationResult = $model->validate($form, $data->getArrayCopy());
        if (!$validationResult) {
            $eMessage = [];
            /** @var \Exception $error */
            foreach ($model->getErrors() as $error) {
                $eMessage[] = sprintf('<p>%s</p>', $error->getMessage());
            }

            $output->set('message', implode(PHP_EOL, $eMessage));
            $this->hyper['cms']->close($output->write());
        }

        //  Attempt to save the data.
        $registerResult = $model->register($data->getArrayCopy());
        if ($registerResult) {
            $newUser = $model->getRegisterUser();

            //  Redirect to the profile screen.
            if ($registerResult === 'adminactivate') {
                $output
                    ->set('redirect', $this->hyper['route']->build([
                        'layout' => 'complete',
                        'option' => 'com_users',
                        'view'   => 'registration'
                    ]))
                    ->set('message', Text::_('COM_USERS_REGISTRATION_COMPLETE_VERIFY'));
            } elseif ($registerResult === 'useractivate') {
                $output
                    ->set('redirect', $this->hyper['route']->build([
                        'layout' => 'complete',
                        'option' => 'com_users',
                        'view'   => 'registration'
                    ]))
                    ->set('message', Text::_('COM_USERS_REGISTRATION_COMPLETE_ACTIVATE'));
            } elseif ($registerResult === 'mobileactivate' && $newUser->id) {
                $output
                    ->set('redirect', $this->hyper['route']->build([
                        'option' => 'com_users',
                        'view'   => 'registration',
                        'layout' => 'mobile_activate',
                        'token'  => $model->getRegisterUser()->activation
                    ]))
                    ->set('message', Text::_('COM_USERS_REGISTRATION_COMPLETE_ACTIVATE'));
            }

            $output
                ->set('result', true)
                ->set('message', Text::sprintf('COM_HYPERPC_USERS_SUCCESS_REGISTRATION', sprintf(
                    '<strong>%s</strong>',
                    $newUser->email
                )))
                ->set('user', [
                    'id'        => $newUser->id,
                    'name'      => $newUser->name,
                    'email'     => $newUser->email,
                    'username'  => $newUser->username
                ]);
        }

        $this->hyper['cms']->close($output->write());
    }

    /**
     * Check user and create new.
     *
     * @return  void
     *
     * @throws  \JBZoo\Utils\Exception
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     *
     * @deprecated
     */
    public function checkUser()
    {
        Form::addFormPath(JPATH_COMPONENT . '/models/forms');

        $output = new JSON(['id' => null, 'is_new' => false]);
        $form   = Form::getInstance(HP_OPTION . '.user-check-form', 'user-check-form');

        $data = new JSON([
            'name'   => $this->hyper['input']->get('name', null, 'string'),
            'email'  => $this->hyper['input']->get('email', null, 'string'),
            'phone'  => $this->hyper['input']->get('phone', null, 'string')
        ]);

        if ($form->validate($data->getArrayCopy())) {
            /** @var User $user */
            $user = $this->hyper['helper']['user']->findByEmail($data->get('email'));
            if ($user->id) {
                $output->set('id', $user->id);
            } else {
                /** @noinspection PhpIncludeInspection */
                require_once JPATH_ROOT . '/components/com_users/models/registration.php';

                jimport('joomla.mail.helper');
                jimport('joomla.user.helper');

                Factory::getLanguage()->load('com_users', JPATH_ROOT, null, true);

                $password = UserHelper::genRandomPassword();

                $user = new JSON([
                    'block'     => 0,
                    'password1' => $password,
                    'password2' => $password,
                    'username'  => $data->get('email'),
                    'email1'    => $data->get('email'),
                    'name'      => $data->get('name')
                ]);

                $manager = Manager::getInstance();
                $element = $manager->getElement(Manager::ELEMENT_POS_ORDER_AFTER_SAVE, 'add_user');

                if ($element instanceof Element && $data->get('phone')) {
                    $mobileAlias = $element->getConfig('alias_mobile');

                    $model = new HyperPcModelRegistration();
                    $model->register($user->getArrayCopy());

                    $registerUser = $model->getRegisterUser();
                    $userId       = $registerUser->id;

                    PluginHelper::importPlugin('system');

                    $authUser  = Factory::getUser($userId);
                    $dataParam = $authUser->getProperties();

                    $dataParam['com_fields'][$mobileAlias] = $data->get('phone');

                    $dispatcher = Factory::getApplication()->getDispatcher();
                    $event      = new Event('onContentAfterSave', ['com_users.user', $authUser, false, $dataParam]);
                    $dispatcher->dispatch('onContentAfterSave', $event);

                    $output
                        ->set('is_new', true)
                        ->set('id', $userId);
                }
            }
        }

        $this->hyper['cms']->close($output->write());
    }

    /**
     * Get auth element list.
     *
     * @return  array
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    protected function _getAuthElements()
    {
        return Manager::getInstance()->getByPosition(Manager::ELEMENT_TYPE_AUTH);
    }

    /**
     * Get request element type.
     *
     * @return  string
     *
     * @since   2.0
     */
    protected function _getRequestElementType()
    {
        return Str::low($this->hyper['input']->post->get('type'));
    }
}
