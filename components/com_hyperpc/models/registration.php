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
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\User\User;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\User\UserHelper;
use Joomla\CMS\Mail\MailTemplate;
use Joomla\Database\ParameterType;
use Joomla\CMS\Plugin\PluginHelper;
use HYPERPC\Object\Mail\TemplateData;
use Joomla\CMS\String\PunycodeHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Application\ApplicationHelper;
use Joomla\Component\Users\Site\Model\RegistrationModel;

defined('_JEXEC') or die('Restricted access');

/**
 * Class HyperPcModelRegistration
 *
 * @property    App     $hyper
 * @property    User    $_registerUser
 *
 * @since       2.0
 */
class HyperPcModelRegistration extends RegistrationModel
{

    /**
     * HyperPcModelRegistration constructor.
     *
     * @param   array $config
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->_registerUser = new User();
        $this->hyper         = App::getInstance();

        Factory::getApplication()->getLanguage()->load('com_users');
    }

    /**
     * Method to save the form data.
     *
     * @param   array  $temp  The form data.
     *
     * @return  mixed  The user id on success, false on failure.
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function register($temp)
    {
        $params = ComponentHelper::getParams('com_users');

        // Initialise the table with Joomla\CMS\User\User.
        $user = new User();
        $data = (array) $this->getData();

        // Merge in the registration data.
        foreach ($temp as $k => $v) {
            $data[$k] = $v;
        }

        // Fill missing data
        $autoEmail = uniqid('no-name-') . '@gmail.com';

        $defaults = [
            'username' => $autoEmail,
            'name' => $autoEmail,
            'email1' => $autoEmail,
        ];

        if (!key_exists('password1', $data) && !key_exists('password2', $data)) {
            $password = UserHelper::genRandomPassword();
            $defaults['password1'] = $password;
            $defaults['password2'] = $password;
        }

        $data = array_merge($defaults, $data);

        // Prepare the data for the user object.
        $data['email']    = PunycodeHelper::emailToPunycode($data['email1']);
        $data['password'] = $data['password1'];
        $useractivation   = $params->get('useractivation');
        $sendpassword     = $params->get('sendpassword', 1);

        // Check if the user needs to activate their account.
        if (($useractivation == 1) || ($useractivation == 2)) {
            $data['activation'] = ApplicationHelper::getHash(UserHelper::genRandomPassword());
            $data['block']      = 1;
        }

        // Bind the data.
        if (!$user->bind($data)) {
            $this->setError($user->getError());

            return false;
        }

        // Load the users plugin group.
        PluginHelper::importPlugin('user');

        // Store the data.
        if (!$user->save()) {
            $this->setError(Text::sprintf('COM_USERS_REGISTRATION_SAVE_FAILED', $user->getError()));

            return false;
        }

        // Change autoEmail values to autoName in name and username fields
        if (in_array($autoEmail, [$user->name, $user->username])) {
            $userId = $user->id;

            $db = $this->getDatabase();
            $query = $db->getQuery(true);
            $query
                ->update($db->quoteName('#__users'))
                ->where($db->quoteName('id') . ' = :userid')
                ->bind(':userid', $userId, ParameterType::INTEGER);

            $autoName = $this->hyper->getContext() . '-' . $userId;

            if ($user->name === $autoEmail) {
                $user->name = $autoName;
                $query->set($db->quoteName('name') . ' = ' . $db->quote($autoName));
            }

            if ($user->username === $autoEmail) {
                $user->username = $autoName;
                $query->set($db->quoteName('username') . ' = ' . $db->quote($autoName));
            }

            $db->setQuery($query)->execute();
        }

        $this->_registerUser = $user;

        $app   = Factory::getApplication();
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);

        // Compile the notification mail values.
        $data             = $user->getProperties();
        $data['fromname'] = $app->get('fromname');
        $data['mailfrom'] = $app->get('mailfrom');
        $data['sitename'] = $app->get('sitename');
        $data['siteurl']  = Uri::root();

        $mailSubjectLangKey = 'COM_HYPERPC_EMAIL_REGISTERED_SUBJECT';
        $mailBodyLangKey = 'COM_HYPERPC_EMAIL_REGISTERED_BODY';

        // Handle account activation/confirmation emails.
        if ($useractivation == 2) { // activation by admin
            // Set the link to confirm the user email.
            $linkMode = $app->get('force_ssl', 0) == 2 ? Route::TLS_FORCE : Route::TLS_IGNORE;

            $data['activate'] = Route::link(
                'site',
                'index.php?option=com_users&task=registration.activate&token=' . $data['activation'],
                false,
                $linkMode,
                true
            );

            $mailBodyLangKey = 'COM_USERS_EMAIL_REGISTERED_WITH_ADMIN_ACTIVATION_BODY';
        } elseif ($useractivation == 1) { // self activation
            // Set the link to activate the user account.
            $linkMode = $app->get('force_ssl', 0) == 2 ? Route::TLS_FORCE : Route::TLS_IGNORE;

            $data['activate'] = Route::link(
                'site',
                'index.php?option=com_users&task=registration.activate&token=' . $data['activation'],
                false,
                $linkMode,
                true
            );

            $mailBodyLangKey = 'COM_USERS_EMAIL_REGISTERED_WITH_ACTIVATION_BODY';
        }

        if (!$sendpassword) {
            $mailBodyLangKey .= '_NOPW';
        }

        $macros = $this->hyper['helper']['macros']->setData($data);

        $mailSubject = $macros->text(str_replace("\n", '<br />', Text::_($mailSubjectLangKey)));
        $mailBody = $macros->text(str_replace("\n", '<br />', Text::_($mailBodyLangKey)));

        $templateData = new TemplateData([
            'subject' => $mailSubject,
            'heading' => Text::_('COM_HYPERPC_EMAIL_REGISTERED_HEADING'),
            'message' => $mailBody,
            'reason' => Text::sprintf(
                'COM_HYPERPC_EMAIL_REGISTERED_REASON',
                $data['sitename']
            )
        ]);

        // Try to send the registration email.
        if ($data['email'] !== $autoEmail) {
            try {
                $mailer = new MailTemplate('com_hyperpc.mail', $app->getLanguage()->getTag());
                $mailer->addTemplateData($templateData->toArray());
                $mailer->addRecipient($data['email']);
                $return = $mailer->send();
            } catch (\Exception $exception) {
                try {
                    Log::add(Text::_($exception->getMessage()), Log::WARNING, 'jerror');

                    $return = false;
                } catch (\RuntimeException $exception) {
                    Factory::getApplication()->enqueueMessage(Text::_($exception->getMessage()), 'warning');

                    $this->setError(Text::_('COM_MESSAGES_ERROR_MAIL_FAILED'));

                    $return = false;
                }
            }
        }

        // Send mail to all users with user creating permissions and receiving system emails
        if (($params->get('useractivation') < 2) && ($params->get('mail_to_admin') == 1)) {
            // Get all admin users
            $query->clear()
                ->select($db->quoteName(['name', 'email', 'sendEmail', 'id']))
                ->from($db->quoteName('#__users'))
                ->where($db->quoteName('sendEmail') . ' = 1')
                ->where($db->quoteName('block') . ' = 0');

            $db->setQuery($query);

            try {
                $rows = $db->loadObjectList();
            } catch (\RuntimeException $e) {
                $this->setError(Text::sprintf('COM_USERS_DATABASE_ERROR', $e->getMessage()));

                return false;
            }

            // Send mail to all superadministrators id
            foreach ($rows as $row) {
                $usercreator = Factory::getUser($row->id);

                if (!$usercreator->authorise('core.create', 'com_users') || !$usercreator->authorise('core.manage', 'com_users')) {
                    continue;
                }

                try {
                    $mailer = new MailTemplate('com_users.registration.admin.new_notification', $app->getLanguage()->getTag());
                    $mailer->addTemplateData($data);
                    $mailer->addRecipient($row->email);
                    $return = $mailer->send();
                } catch (\Exception $exception) {
                    try {
                        Log::add(Text::_($exception->getMessage()), Log::WARNING, 'jerror');

                        $return = false;
                    } catch (\RuntimeException $exception) {
                        Factory::getApplication()->enqueueMessage(Text::_($exception->getMessage()), 'warning');

                        $return = false;
                    }
                }

                // Check for an error.
                if ($return !== true) {
                    $this->setError(Text::_('COM_USERS_REGISTRATION_ACTIVATION_NOTIFY_SEND_MAIL_FAILED'));

                    return false;
                }
            }
        }

        // Check for an error.
        if ($return !== true) {
            $this->setError(Text::_('COM_USERS_REGISTRATION_SEND_MAIL_FAILED'));

            // Send a system message to administrators receiving system mails
            $db = $this->getDatabase();
            $query->clear()
                ->select($db->quoteName('id'))
                ->from($db->quoteName('#__users'))
                ->where($db->quoteName('block') . ' = 0')
                ->where($db->quoteName('sendEmail') . ' = 1');
            $db->setQuery($query);

            try {
                $userids = $db->loadColumn();
            } catch (\RuntimeException $e) {
                $this->setError(Text::sprintf('COM_USERS_DATABASE_ERROR', $e->getMessage()));

                return false;
            }

            if (count($userids) > 0) {
                $jdate     = new Date();
                $dateToSql = $jdate->toSql();
                $subject   = Text::_('COM_USERS_MAIL_SEND_FAILURE_SUBJECT');
                $message   = Text::sprintf('COM_USERS_MAIL_SEND_FAILURE_BODY', $data['username']);

                // Build the query to add the messages
                foreach ($userids as $userid) {
                    $values = [
                        ':user_id_from',
                        ':user_id_to',
                        ':date_time',
                        ':subject',
                        ':message',
                    ];
                    $query->clear()
                        ->insert($db->quoteName('#__messages'))
                        ->columns($db->quoteName(['user_id_from', 'user_id_to', 'date_time', 'subject', 'message']))
                        ->values(implode(',', $values));
                    $query->bind(':user_id_from', $userid, ParameterType::INTEGER)
                        ->bind(':user_id_to', $userid, ParameterType::INTEGER)
                        ->bind(':date_time', $dateToSql)
                        ->bind(':subject', $subject)
                        ->bind(':message', $message);

                    $db->setQuery($query);

                    try {
                        $db->execute();
                    } catch (\RuntimeException $e) {
                        $this->setError(Text::sprintf('COM_USERS_DATABASE_ERROR', $e->getMessage()));

                        return false;
                    }
                }
            }

            return false;
        }

        if ($useractivation == 1) {
            return 'useractivate';
        } elseif ($useractivation == 2) {
            return 'adminactivate';
        } else {
            return $user->id;
        }
    }

    /**
     * Get register user data.
     *
     * @return  User
     *
     * @since   2.0
     */
    public function getRegisterUser()
    {
        return $this->_registerUser;
    }

    /**
     * Method to get a form object.
     *
     * @param   string   $name     The name of the form.
     * @param   string   $source   The form source. Can be XML string if file flag is set to false.
     * @param   array    $options  Optional array of options for the form creation.
     * @param   boolean  $clear    Optional argument to force load a new form.
     * @param   string   $xpath    An optional xpath to search for the fields.
     *
     * @return  Form
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    protected function loadForm($name, $source = null, $options = [], $clear = false, $xpath = false)
    {
        //  Get com_users form.
        Form::addFormPath(JPATH_ROOT . '/components/com_users/forms');

        return parent::loadForm($name, $source, $options, $clear, $xpath);
    }
}
