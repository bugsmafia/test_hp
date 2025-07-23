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

use JBZoo\Utils\Str;
use Cake\Utility\Xml;
use HYPERPC\Data\JSON;
use Joomla\Event\Event;
use HYPERPC\Joomla\Factory;
use HYPERPC\ORM\Entity\User;
use Joomla\CMS\Language\Text;
use HYPERPC\Helper\UserHelper;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Http\HttpFactory;
use HYPERPC\Helper\MacrosHelper;
use HYPERPC\Helper\SessionHelper;
use HYPERPC\Elements\ElementAuth;
use HYPERPC\Joomla\Model\ModelForm;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\User\UserFactoryInterface;

/**
 * Class ElementAuthMobile
 *
 * @since   2.0
 */
class ElementAuthMobile extends ElementAuth
{

    const PHONE_REGX               = '/^\d{11,14}$/';
    const TARGET_SMS_API_SEND_URL  = 'https://sms.targetsms.ru/sendsmsjson.php';

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
        $phone = $this->getRequestValue();
        $clearPhone = $this->hyper['helper']['string']->clearMobilePhone($phone);

        if (!preg_match(self::PHONE_REGX, $clearPhone)) {
            $output->set('message', Text::_('HYPER_ELEMENT_AUTH_MOBILE_EDIT_ACCOUNT_FORM_ERROR_NO_CURRENT_MOBILE'));
            $this->hyper['cms']->close($output->write());
        }

        return true;
    }

    /**
     * Check edit unique value.
     *
     * @param   JSON  $output
     *
     * @return  bool
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public function checkEditUniqueValue(JSON &$output)
    {
        $phone      = $this->getEditRequestValue();
        $clearPhone = $this->hyper['helper']['string']->clearMobilePhone($phone);

        if (!preg_match(self::PHONE_REGX, $clearPhone)) {
            $output->set('message', Text::_('HYPER_ELEMENT_AUTH_MOBILE_EDIT_ACCOUNT_FORM_ERROR_NO_CURRENT_MOBILE'));
            return false;
        }

        $phoneXML = Xml::build([
            'field' => [
                '@type'   => 'text',
                '@name'   => 'phone',
                '@unique' => 'uniquemobile'
            ]
        ]);

        FormHelper::addRulePath($this->hyper['path']->get('site:models/rules'));
        FormHelper::loadRuleClass('Uniquemobile');

        $testResult = (new JFormRuleUniquemobile())->test($phoneXML, $phone);
        if (!$testResult) {
            $output->set('message', (string) $phoneXML['message']);
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
        $db     = $this->hyper['db'];
        $phone  = $this->getRequestValue() ? $this->getRequestValue() : $this->getEditRequestValue();

        $unmaskedPhone = str_replace([' ', '-', '(', ')'], '', $phone);

        $where = [$db->qn('a.field_id') . ' = ' . $db->q($this->getConfig('field_phone'))];
        if (strlen($unmaskedPhone) === 12) {
            $maskedPhone = preg_replace('/([+]7)(\d{3})(\d{3})(\d{2})(\d{2})/i', '$1 ($2) $3-$4-$5', $unmaskedPhone);
            $where[]     = $db->qn('a.value') . ' IN (' . $db->q($maskedPhone) . ',' . $db->q($unmaskedPhone) . ')';
        } else {
            $where[] = $db->qn('a.value') . ' = ' . $db->q($phone);
        }

        $query = $db
            ->getQuery(true)
            ->select([
                'a.*'
            ])
            ->from(
                $db->qn(JOOMLA_TABLE_FIELDS_VALUES, 'a')
            )
            ->where($where);

        $queryPhones = $db->setQuery($query)->loadAssocList();

        if (count($queryPhones)) {
            foreach ($queryPhones as $queryPhone) {
                $queryPhone = new JSON($queryPhone);
                if ($queryPhone->get('item_id')) {
                    $user = $this->_hUser->findById($queryPhone->get('item_id'), ['load_fields' => true]);
                    if ($user->id) {
                        $this->setUser($user);
                        return $this->_user;
                    }
                }
            }
        }

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
        PluginHelper::importPlugin('system');

        $model = new HyperPcModelRegistration();
        $model->register([]);

        $registeredUser = $model->getRegisterUser();
        if (!$registeredUser->id) {
            return;
        }

        $output
            ->set('new', true)
            ->set('result', true);

        $mobileAlias = $this->getCustomFieldMobileAlias();

        $user = Factory::getContainer()->get(UserFactoryInterface::class)->loadUserById($registeredUser->id);
        $userProperties = $user->getProperties();

        $userProperties['com_fields'][$mobileAlias] = $this->getRequestValue();

        $dispatcher = Factory::getApplication()->getDispatcher();
        $event      = new Event('onContentAfterSave', ['com_users.user', $user, false, $userProperties]);
        $dispatcher->dispatch('onContentAfterSave', $event);

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
        $fields     = new JSON($this->_user->fields);
        $phoneField = $fields->get($this->getCustomFieldMobileAlias());
        $phone      = $this->hyper['helper']['string']->clearMobilePhone($phoneField->get('value'));

        try {
            $response = $this->_sendSmsCode($phone);

            $logMessage = implode(', ', [
                'Phone: ' . $phone,
                'User: ' . $this->_user->id,
                'Body: ' . json_encode($response->getArrayCopy())
            ]);

            $this->_log($logMessage);

            $error = false;
            //  Check error in response.
            if ($response->get('error')) {
                $error = $response->get('error');
            }

            //  Check error in sms message.
            $smsError = $response->find('sms.0.error');
            if (!empty($smsError)) {
                $error = $smsError;
            }

            if ($error) {
                $output
                    ->set('result', false)
                    ->set('message', $error);
            } else {
                /** @var HyperPcModelUser_Code $cModel */
                $cModel = ModelForm::getInstance('User_Code');

                $output
                    ->set('token', Session::getFormToken())
                    ->set('user', base64_encode($this->_user->id . '::' . $cModel->getDbo()->insertid()))
                    ->set('message', Text::sprintf(
                        'COM_HYPERPC_AUTH_ENTER_THE_ONE_TIME_PASSWORD_SENT_TO_PHONE',
                        $phoneField->get('value')
                    ));
            }
        } catch (Exception $e) {
            $output
                ->set('result', false)
                ->set('message', $e->getMessage());
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
        PluginHelper::importPlugin('system');

        $mobileAlias = $this->getCustomFieldMobileAlias();
        /** @var User $authUser */
        $authUser    = $this->hyper['user'];
        $dataParam   = $authUser->toArray();

        $dataParam['com_fields'][$mobileAlias] = $this->getEditRequestValue();

        $dispatcher = Factory::getApplication()->getDispatcher();
        $event      = new Event('onContentAfterSave', ['com_users.user', $authUser, false, $dataParam]);
        $result     = $dispatcher->dispatch('onContentAfterSave', $event);
        $resultData = $result->getArgument('result');

        if (is_array($resultData) && !in_array(true, $resultData)) {
            $output->set('message', Text::_('COM_HYPERPC_ERROR_UPDATE_USER'));
        } else {
            $output
                ->set('result', true)
                ->set('message', Text::_('HYPER_ELEMENT_AUTH_MOBILE_EDIT_ACCOUNT_SUCCESS_UPDATE_MOBILE'));
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
            'COM_HYPERPC_AUTH_ENTER_THE_ONE_TIME_PASSWORD_SENT_TO_PHONE',
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
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function sendEditCode(JSON &$output)
    {
        $phone    = $this->hyper['helper']['string']->clearMobilePhone($this->getEditRequestValue());
        $response = $this->_sendSmsCode($phone, 'Edit mobile phone');
        $smsError = $response->find('sms.0.error');

        if ($response->get('error')) {
            $output
                ->set('result', false)
                ->set('message', $response->get('error'));

            return false;
        }

        if ($smsError) {
            $output
                ->set('result', false)
                ->set('message', $smsError);

            return false;
        }

        return true;
    }

    /**
     * Get auth request value.
     *
     * @return  mixed
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function getRequestValue()
    {
        return $this->getRequest('phone');
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

        return (bool) $session->get()->mobile;
    }

    /**
     * Send sms code.
     *
     * @param   string|int  $phone          Set clear mobile phone/ For example 79997775522.
     * @param   string      $nameDelivery
     *
     * @return  JSON
     *
     * @throws  \JBZoo\Utils\Exception
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    protected function _sendSmsCode($phone, $nameDelivery = null)
    {
        if (!$nameDelivery) {
            $nameDelivery = Text::_('HYPER_ELEMENT_AUTH_MOBILE_SMS_DELIVERY');
        }

        $nameDelivery .= ' ' . Str::up($this->hyper->getContext());

        /** @var MacrosHelper $macros */
        $macros = $this->hyper['helper']['macros'];
        $macros->setData(['code' => $this->getNewPassword()]);

        $data = new JSON([
            'security' => [
                'password' => $this->getConfig('target_pwd'),
                'login'    => $this->getConfig('target_login')
            ],
            'message' => [
                [
                    'abonent' => [
                        [
                            'number_sms'    => 1,
                            'phone'         => '' . $phone . ''
                        ]
                    ],
                    'type'          => 'sms',
                    'name_delivery' => $nameDelivery,
                    'sender'        => $this->getConfig('target_sender'),
                    'text'          => $macros->text($this->getConfig('target_message'))
                ]
            ],
            'type' => 'sms'
        ]);

        $http = HttpFactory::getHttp([], 'stream');

        $result = $http->post(self::TARGET_SMS_API_SEND_URL, $data->write(), [
            'Content-Type' => 'application/json',
            'Accept'       => 'application/json'
        ]);

        return new JSON($result->body);
    }
}
