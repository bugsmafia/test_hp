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

use Cake\Utility\Xml;
use HYPERPC\Data\JSON;
use Joomla\Event\Event;
use HYPERPC\Joomla\Factory;
use HYPERPC\ORM\Entity\User;
use Joomla\CMS\Language\Text;
use HYPERPC\Helper\UserHelper;
use Joomla\CMS\Session\Session;
use HYPERPC\Helper\SessionHelper;
use HYPERPC\Elements\ElementAuth;
use Joomla\CMS\Mail\MailTemplate;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Form\Rule\EmailRule;
use HYPERPC\Joomla\Model\ModelForm;
use HYPERPC\Object\Mail\TemplateData;

/**
 * Class ElementAuthEmail
 *
 * @since   2.0
 */
class ElementAuthEmail extends ElementAuth
{

    const EMAIL_PREG = '/^(save@hyperpc\.ru|([\w\.\-\+#$^]+@((?!hyperpc\.|epix\.)\w+[\w\.\-]*?\.)[a-zA-Z]{2,18}))$/';

    /**
     * Check user can sing in.
     *
     * @param   JSON  $output
     *
     * @return  bool
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function canSignIn(JSON $output)
    {
        $user = $this->getUserByRequest();
        $validEmail = preg_match(self::EMAIL_PREG, $this->getRequestValue());
        return $validEmail || $this->_hUser->isInManagerGroup((array) $user->groups);
    }

    /**
     * Check edit unique value.
     *
     * @param   JSON  $output
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function checkEditUniqueValue(JSON &$output)
    {
        $email = $this->getEditRequestValue();

        $mailXML = Xml::build([
            'field' => [
                '@type'     => 'text',
                '@name'     => 'email',
                '@unique'   => 'unique'
            ]
        ]);

        $emailRule  = new EmailRule();
        $emailRule->setDatabase($this->hyper['db']);

        try {
            $emailRule->test($mailXML, $email);
        } catch (Exception $e) {
            $output->set('message', $e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * Get user entity by request data.
     *
     * @return  User
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function getUserByRequest()
    {
        $this->setUser($this->_hUser->findByEmail($this->getRequestValue() ? $this->getRequestValue() : $this->getEditRequestValue(), ['load_fields' => true]));
        return $this->_user;
    }

    /**
     * Event on create new user.
     *
     * @param   JSON  $output
     *
     * @return  void
     *
     * @throws  \JBZoo\Utils\Exception
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function onCreateNewUser(JSON &$output)
    {
        $registrationAllowed = $this->getConfig('create_new_profile', false, 'bool');
        if (!$registrationAllowed) {
            $output->set('message', Text::_('HYPER_ELEMENT_AUTH_EMAIL_ERROR_NOT_FIND_EMAIL'));
            $this->hyper['cms']->close($output->write());
        }

        $model = new HyperPcModelRegistration();
        $model->register(['email1' => $this->getRequestValue()]);

        $registeredUser = $model->getRegisterUser();
        if (!$registeredUser->id) {
            return;
        }

        $output
            ->set('result', true)
            ->set('new', true);

        $this->setUser(
            $this->_hUser->findById($registeredUser->id, ['load_fields' => true])
        );
    }

    /**
     * Event on first step success auth.
     *
     * @param   JSON  $output
     *
     * @return  void
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function onFirstStepSuccess(JSON &$output)
    {
        $user = $this->getUser();

        $this->_hAuth->setSession($user->id);

        $returnMail = $this->_sendEmail($user->email);

        /** @var HyperPcModelUser_Code $cModel */
        $cModel = ModelForm::getInstance('User_Code');

        if (!$returnMail) {
            $output->set('result', false);
        } else {
            $logMessage = implode(', ', [
                'Email: ' . $user->email,
                'User: ' . $this->_user->id
            ]);

            $this->_log($logMessage);

            $output
                ->set('token', Session::getFormToken())
                ->set('user', base64_encode($user->id . '::' . $cModel->getDbo()->insertid()))
                ->set('message', Text::sprintf(
                    'COM_HYPERPC_AUTH_ENTER_THE_ONE_TIME_PASSWORD_SENT_TO_MAIL',
                    $user->email
                ));
        }
    }

    /**
     * On success edit value.
     *
     * @param   User  $user
     * @param   JSON  $output
     *
     * @return  void
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function onSuccessEditValue(User $user, JSON &$output)
    {
        $db       = $this->hyper['db'];
        $query    = $db->getQuery(true);
        $newEmail = $this->getEditRequestValue();

        $newUser  = clone $user;
        $newUser->email = $newEmail;

        PluginHelper::importPlugin('user', 'hyperpc');

        $dispatcher = Factory::getApplication()->getDispatcher();
        $event      = new Event('onUserBeforeSave', [$user->toArray(), false, $newUser->toArray()]);
        $dispatcher->dispatch('onUserBeforeSave', $event);

        $query
            ->update($db->qn('#__users'))
            ->set([
                $db->qn('email') . ' = ' . $db->q($newEmail),
            ])
            ->where([
                $db->qn('id') . ' = ' . $db->q($user->id)
            ]);

        $result = $db->setQuery($query)->execute();

        if (!$result) {
            $output->set('message', Text::_('COM_HYPERPC_ERROR_UPDATE_USER'));
        } else {
            $dispatcher = Factory::getApplication()->getDispatcher();
            $event      = new Event('onUserAfterSave', [$newUser->toArray(), false, $result, '']);
            $dispatcher->dispatch('onUserAfterSave', $event);

            $output
                ->set('result', true)
                ->set('message', Text::_('HYPER_ELEMENT_AUTH_EMAIL_EDIT_ACCOUNT_SUCCESS_UPDATE_EMAIL'));

            if ($this->getConfig('mail_change_old_sbj', '', 'trim')) {
                $this->_sendEmail($user->email, 'change_old');
            }
        }
    }

    /**
     * On success send edit code.
     *
     * @param   JSON  $output
     *
     * @return  void
     *
     * @since   2.0
     */
    public function onSuccessSendEditCode(JSON &$output)
    {
        $output->set('message', Text::sprintf(
            'HYPER_ELEMENT_AUTH_EMAIL_EDIT_ACCOUNT_SUCCESS_SEND_EMAIL',
            $this->getEditRequestValue()
        ));
    }

    /**
     * Send edit code.
     *
     * @param   JSON  $output
     *
     * @return  mixed
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     *
     * @todo    add specified content for adit code email
     */
    public function sendEditCode(JSON &$output)
    {
        $returnMail = $this->_sendEmail($this->getEditRequestValue());
        if (!$returnMail) {
            $output->set('message', Text::_('HYPER_ELEMENT_AUTH_EMAIL_EDIT_ACCOUNT_ERROR_SEND_EMAIL'));
        }

        return $returnMail;
    }

    /**
     * Check for edit attempts exceeded.
     *
     * @return  bool Returns true if count of edit requests more then one.
     *
     * @since   2.0
     */
    public function isEditExceeded()
    {
        $session = $this->hyper['helper']['session'];

        $session
            ->setType(SessionHelper::TYPE_COOKIE)
            ->setNamespace(UserHelper::SESSION_NAMESPACE);

        return (bool) $session->get()->email;
    }

    /**
     * Send email message code.
     *
     * @param   string  $recipient
     * @param   string  $type enum [auth | change_old]
     * @param   string  $tmpl
     *
     * @return  bool
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    protected function _sendEmail($recipient, $type = 'auth', $tmpl = 'mail')
    {
        $mailer = new MailTemplate('com_hyperpc.' . $tmpl, Factory::getApplication()->getLanguage()->getTag());

        $subject = $this->getConfig('mail_' . $type . '_sbj');
        if (empty($subject)) {
            $subject = Text::sprintf(
                'COM_HYPERPC_AUTH_SEND_CODE_MAIL_SUBJECT',
                $this->hyper['params']->get('site_context', HP_CONTEXT_HYPERPC)
            );
        }

        switch ($type) {
            case 'auth':
                $heading = Text::sprintf('HYPER_ELEMENT_AUTH_EMAIL_MAIL_AUTH_HEADING', $this->getNewPassword());
                break;
            default:
                $heading = Text::_('HYPER_ELEMENT_AUTH_EMAIL_MAIL_' . $type . '_HEADING');
                break;
        }

        $templateData = new TemplateData([
            'subject' => $subject,
            'heading' => $heading,
            'message' => $this->getConfig('mail_' . $type . '_msg', ''),
            'reason' => Text::_('HYPER_ELEMENT_AUTH_EMAIL_MAIL_' . strtoupper($type) . '_REASON')
        ]);

        $mailer->addTemplateData($templateData->toArray());
        $mailer->addRecipient($recipient);

        try {
            return $mailer->send();
        } catch (\Throwable $th) {
            $this->_log($th->getMessage());
        }

        return false;
    }
}
