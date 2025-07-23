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
use JBZoo\Utils\Filter;
use HYPERPC\Joomla\Factory;
use HYPERPC\ORM\Table\Table;
use HYPERPC\ORM\Entity\User;
use HYPERPC\Elements\Manager;
use Joomla\CMS\Language\Text;
use HYPERPC\Helper\AuthHelper;
use HYPERPC\Helper\UserHelper;
use Joomla\CMS\Session\Session;
use HYPERPC\Helper\SessionHelper;
use HYPERPC\Elements\ElementAuth;
use HYPERPC\Joomla\Model\ModelForm;
use Joomla\CMS\User\UserFactoryInterface;
use HYPERPC\Joomla\Controller\ControllerLegacy;

/**
 * Class HyperPcControllerAuth
 *
 * @property    AuthHelper      $_auth
 * @property    UserHelper      $_helper
 * @property    SessionHelper   $_session
 *
 * @since       2.0
 */
class HyperPcControllerAuth extends ControllerLegacy
{

    /**
     * Hook on initialize controller.
     *
     * @param   array  $config
     *
     * @return  void
     *
     * @since   2.0
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->_auth    = $this->hyper['helper']['auth'];
        $this->_helper  = $this->hyper['helper']['user'];
        $this->_session = $this->hyper['helper']['session'];

        $this->_session
            ->setType(SessionHelper::TYPE_COOKIE)
            ->setNamespace(AuthHelper::SESSION_NAMESPACE)
            ->setCookieLifetime(24 * 60);

        $this
            ->registerTask('step-one', 'stepOne')
            ->registerTask('step-two', 'stepTwo');

        JLoader::register('UsersModelProfile', JPATH_ROOT . '/components/com_users/models/profile.php');
    }

    /**
     * Auth action - first step.
     *
     * @return  void
     *
     * @throws  \JBZoo\Utils\Exception
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function stepOne()
    {
        $this->hyper['cms']->setHeader('Content-Type', 'application/json');

        $output = new JSON([
            'result'  => false,
            'message' => null,
            'user'    => null,
            'new'     => false
        ]);

        if ($this->app->getIdentity()->id) { // User has already been authenticated
            $output->set('message', Text::_('COM_HYPERPC_ERROR_USER_HAS_BEEN_AUTH_EARLIER'));
            $this->hyper['cms']->close($output->write());
        }

        $manager  = Manager::getInstance();
        $elements = $manager->getByPosition(Manager::ELEMENT_TYPE_AUTH);

        //  Error no auth element in component setting.
        if (!count($elements)) {
            $output->set('message', Text::_('COM_HYPERPC_ERROR_NOT_FIND_AUTH_ELEMENTS'));
            $this->hyper['cms']->close($output->write());
        }

        $data = new JSON($this->hyper['input']->get(JOOMLA_FORM_CONTROL, [], 'array'));
        $type = Str::low($data->get('type'));

        //  Error not find element type.
        if (!array_key_exists($type, $elements)) {
            $output->set('message', Text::sprintf('COM_HYPERPC_ERROR_NOT_FIND_AUTH_ELEMENT', $type));
            $this->hyper['cms']->close($output->write());
        }

        /** @var ElementAuth $element */
        $element = $elements[$type];

        // Foreign numbers disabled
        // if ($element->getType() === 'mobile') {
        //     $phone = $element->getRequestValue();
        //     if (!preg_match('/^\+7(\s\()?9(\d{2})(\)\s)?(\d{3})-?(\d{2})-?(\d{2})$/', $phone)) {
        //         $output->set('message', Text::_('COM_HYPERPC_AUTH_FOREIGN_NUMBERS_IS_NOT_AVAILABLE'));
        //         $this->hyper['cms']->close($output->write());
        //     }
        // }

        //  Error not enabled element.
        if (!$element->isEnabled()) {
            $output->set('message', Text::_('COM_HYPERPC_AUTH_SIGN_IN_ACCESS_ERROR'));
            $this->hyper['cms']->close($output->write());
        }

        //  Error can not access for action.
        if (!$element->canSignIn($output)) {
            $output->set('message', Text::_('COM_HYPERPC_AUTH_SIGN_IN_SAVE_CODE_ERROR'));
            $this->hyper['cms']->close($output->write());
        }

        if ($this->hyper->getUserIp() === $this->hyper['config']->get('office_ip')) {
            goto verificationPassed;
        }

        //  Check captcha.
        if ($data->has('g-recaptcha-response')) {
            $element->setUseCaptcha(true);
            $form = $element->getAuthForm();

            $validData = [
                'captcha' => $data->get('g-recaptcha-response')
            ];

            if ($element->getType() === 'mobile') {
                $validData['phone'] = $element->getRequestValue();
            } elseif ($element->getType() === 'email') {
                $validData['email'] = $element->getRequestValue();
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
            }
        }

        /** @var User $user */
        $user = $element->getUserByRequest();

        //  Check banned user.
        if (Filter::int($user->block) === 1) {
            $output->set('message', Text::_('COM_HYPERPC_ACCESS_TEMPORARILY_LIMITED'));
            $this->hyper['cms']->close($output->write());
        }

        //  Set captcha for new users.
        if (empty($data->get('g-recaptcha-response')) && (!$user->id || $user->lastvisitDate < $user->registerDate)) {
            $this->_forceSetCaptcha($element, $output);
            $this->hyper['cms']->close($output->write());
        }

        /** @var HyperPcTableForm_Counter $countTable */
        $countTable = Table::getInstance('Form_Counter');

        //  For quick test.
        //$countTable->setWaitTime(10);

        $countTable->checkHighActive();

        if ($countTable->isHighActive() && empty($data->get('g-recaptcha-response'))) {
            $this->_forceSetCaptcha($element, $output);

            $this->hyper['cms']->close($output->write());
        }

        $checkResult = $countTable->checkRequest($element->getRequestValue(), HP_OPTION . '.auth_' . $element->getType());
        if (!empty($countTable->getError())) {
            if (!$checkResult) {
                $output->set('message', $countTable->getError());
                $this->hyper['cms']->close($output->write());
            }

            $this->_forceSetCaptcha($element, $output);
            $output->set('message', $countTable->getError());
        }

        verificationPassed:

        if (!$user->id) {
            $element->onCreateNewUser($output);
            $user = $element->getUser();
        } else {
            $output->set('result', true);
        }

        $session = Factory::getApplication()->getSession();
        /** @var HyperPcModelUser_Code $codesModel */
        $codesModel = ModelForm::getInstance('User_Code');

        $authCode = $this->_auth->getRandomCode();
        $element->setNewPassword($authCode);

        $codeSaved = false;
        try {
            $codeSaved = $codesModel->save([
                'id'      => null,
                'user_id' => $user->id,
                'code'    => $authCode,
                'token'   => $session->getToken()
            ]);
        } catch (\Throwable $th) {
            $output
                ->set('result', false)
                ->set('message', $th->getMessage());

            $this->hyper['cms']->close($output->write());
        }

        if (!$codeSaved) {
            $output
                ->set('result', false)
                ->set('message', Text::_('COM_HYPERPC_AUTH_SIGN_IN_SAVE_CODE_ERROR'));
        }

        if ($output->get('result')) {
            $element->onFirstStepSuccess($output);
        }

        $this->hyper['cms']->close($output->write());
    }

    /**
     * Auth action - second step.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function stepTwo()
    {
        $this->hyper['cms']->setHeader('Content-Type', 'application/json');

        $output = new JSON([
            'result'  => false,
            'message' => null,
            'user'    => null
        ]);

        if ($this->app->getIdentity()->id) { // User has already been authenticated
            $output->set('message', Text::_('COM_HYPERPC_ERROR_USER_HAS_BEEN_AUTH_EARLIER'));
            $this->hyper['cms']->close($output->write());
        }

        if (!Session::checkToken()) {
            $output->set('message', Text::_('JINVALID_TOKEN'));
            $this->hyper['cms']->close($output->write());
        }

        $userId = $this->app->getInput()->getInt('user_id');
        $user = Factory::getContainer()->get(UserFactoryInterface::class)->loadUserById($userId);

        $result = $this->app->login([
            'username' => $user->username,
            'password' => implode($this->app->getInput()->get('pwd', [], 'array'))
        ], ['remember' => true]);

        $output->set('result', $result);

        if ($result) {
            $isAutoEmail = $this->hyper['helper']['string']->isAutoEmail($user->email);
            $userEmail = !$isAutoEmail ? strtolower($user->email) : '';
            $userEmailHash = !empty($userEmail) ? md5($userEmail) : '';

            $output
                ->set('message', Text::_('COM_HYPERPC_USERS_SUCCESS_AUTH'))
                ->set('user', [
                    'id'        => $user->id,
                    'name'      => $user->name,
                    'email'     => $userEmail,
                    'emailHash' => $userEmailHash,
                    'username'  => $user->username,
                    'token'     => Session::getFormToken()
                ]);
        } else {
            $output->set('message', Text::_('COM_HYPERPC_AUTH_NO_CURRENT_PASSWORD_ERROR'));
        }

        $this->hyper['cms']->close($output->write());
    }

    /**
     * Force set captcha
     *
     * @param   ElementAuth $element
     * @param   JSON  $output
     *
     * @return  void
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
}
