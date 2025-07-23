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

JLoader::register('ElementCorePhone', JPATH_ROOT . '/media/hyperpc/elements/core/phone/phone.php');
JLoader::register('JFormRuleUniquemobile', JPATH_ROOT . '/components/' . HP_OPTION . '/models/rules/uniquemobile.php');

use Cake\Utility\Xml;
use HYPERPC\Data\JSON;
use Joomla\CMS\Factory;
use Joomla\Event\Event;
use Joomla\Input\Cookie;
use HYPERPC\ORM\Entity\User;
use Joomla\CMS\Language\Text;
use Joomla\CMS\User\UserHelper;
use Joomla\CMS\User\User as JUser;
use Joomla\CMS\Plugin\PluginHelper;
use HYPERPC\Joomla\Model\Entity\Order;
use HYPERPC\Elements\ElementOrderHook;
use Joomla\CMS\User\UserFactoryInterface;

/**
 * Class ElementOrderHookAddUser
 *
 * @property    Order   $_order
 * @property    JUser   $_registerUser
 *
 * @since       2.0
 */
class ElementOrderHookAddUser extends ElementOrderHook
{
    /**
     * Hook action.
     *
     * If you want to save phone custom field, set enabled rules.core.edit.value for public.
     *
     * @throws  \JBZoo\Utils\Exception
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @return  void
     *
     * @since   2.0
     */
    public function hook()
    {
        $this->_order = $this->_getOrder();

        $user = $this->_findUserByOrderData();

        if (!$user->id) {
            $userRegisterData = $this->_registerUser();
            if ($this->_registerUser->id > 0) {
                $this->_hookOnSuccessRegistration($userRegisterData);
            } else { // Possibly dead code
                $db = $this->hyper['db'];

                $query = $db->getQuery(true)
                    ->select($db->quoteName('id'))
                    ->from($db->quoteName('#__users'))
                    ->where($db->quoteName('email') . ' = ' . $db->quote($this->_order->getBuyerEmail()));
                $db->setQuery($query, 0, 1);

                $userIdByEmail = $db->loadResult();

                if ($userIdByEmail) {
                    $this->_order->set('created_user_id', $userIdByEmail);
                    /** @var HyperPcTableOrders $table */
                    $table = $this->hyper['helper']['order']->getTable();
                    $table->save($this->_order->getArray());

                    $ormUser = $this->hyper['helper']['user']->findById($userIdByEmail);
                    $ormUser->_checkUid();
                }
                $this->hyper['helper']['mindbox']->createAuthorizedOrder($this->_order);
            }
        } else {
            $this->_hookOnFoundUser($user);
            $this->hyper['helper']['mindbox']->createAuthorizedOrder($this->_order);
        }
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
        Factory::getApplication()->getLanguage()->load('com_users');

        parent::initialize();
    }

    /**
     * Set uid to user if not exists
     *
     * @param   User $user
     *
     * @return  void
     *
     * @since   2.0
     */
    protected function _checkUid(User $user)
    {
        /** @var Cookie */
        $cookie = $this->hyper['input']->cookie;
        $uid = $user->getUid();
        if (empty($uid) && !$cookie->get(HP_COOKIE_HMP)) {
            $uid = $cookie->get(HP_COOKIE_UID);
            $user->setUid($uid);
        }
    }

    /**
     * Find user by order data.
     *
     * @return  JUser
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    protected function _findUserByOrderData()
    {
        $user = Factory::getApplication()->getIdentity();
        if ($user !== null && $user->id) {
            return $user;
        }

        /** @var UserFactoryInterface $userFactory */
        $userFactory = Factory::getContainer()->get(UserFactoryInterface::class);

        $order = $this->_getOrder();
        $userEntity = $this->hyper['helper']['user']->findByEmail($order->getBuyerEmail());
        if ($userEntity->id) {
            return $userFactory->loadUserById($userEntity->id);
        }

        $userIdByPhone = $this->_findUserIdByPhoneNumber($order->getBuyerPhone());

        return $userFactory->loadUserById($userIdByPhone);
    }

    /**
     * Hook logic on found user.
     *
     * @param   JUser $jUser
     *
     * @return  void
     *
     * @throws  \JBZoo\Utils\Exception
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    protected function _hookOnFoundUser($jUser)
    {
        if (!$jUser || !$jUser->id) {
            return;
        }

        $userId = $jUser->id;
        $orderEmail = $this->_order->getBuyerEmail();
        $jUserChanged = false;

        // Update user name
        $isAutoName = $jUser->name === $jUser->email || preg_match('/^(hyperpc|epix)-\d+/', $jUser->name);
        if ($isAutoName) {
            $jUser->name = $this->_order->getBuyer();
            $jUserChanged = true;
        }

        // Update user email
        $isAutoEmail = $this->hyper['helper']['string']->isAutoEmail($jUser->email);
        if ($isAutoEmail) {
            /** @var User $testUser */
            $testUser = $this->hyper['helper']['user']->findByEmail($orderEmail);
            if (!$testUser->id) { // There is no user with this email
                $jUser->email = $orderEmail;
                $jUserChanged = true;
            }
        }

        if ($jUserChanged) {
            $jUser->save(true);
        }

        /** @var User $hpUser */
        $hpUser = $this->hyper['helper']['user']->findById($userId, ['load_fields' => true]);

        // Update user phone
        if ($this->getConfig('update_phone', true, 'bool')) {
            $userIdByPhone = $this->_findUserIdByPhoneNumber($this->_order->getBuyerPhone());
            if ($userIdByPhone === 0) { // There is no user with the phone from order
                $this->_updateUserMobilePhone($hpUser->id);
            }
        }

        $this->_checkUid($hpUser);

        $this->_order->set('created_user_id', $userId);

        /** @var HyperPcTableOrders $table */
        $table = $this->hyper['helper']['order']->getTable();
        $table->save($this->_order->getArray());
    }

    /**
     * Hook on success user registration.
     *
     * @param   JSON  $registerData
     *
     * @return  void
     *
     * @throws  \JBZoo\Utils\Exception
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    protected function _hookOnSuccessRegistration(JSON $registerData)
    {
        /** @var HyperPcTableOrders $table */
        $table = $this->hyper['helper']['order']->getTable();
        $this->_order->set('created_user_id', $this->_registerUser->id);
        $table->save($this->_order->getArray());

        // Login user
        if ($registerData->get('username') && $registerData->get('password1')) {
            $this->hyper['cms']->login([
                'username' => $registerData->get('username'),
                'password' => $registerData->get('password1')
            ], ['remember' => true]);
        }

        $mobileAlias = $this->_config->get('alias_mobile');
        if (!empty($mobileAlias)) { // Update user phone field
            $this->_updateUserMobilePhone($this->_registerUser->id);
        }

        $message = implode(PHP_EOL, [
            '<div class="uk-text-emphasis">',
                Text::_('HYPER_ELEMENT_ORDER_HOOK_SUCCESS_REGISTER_USER_ACCOUNT_MESSAGE_HEADING'),
            '</div>',
            '<div>',
                Text::_('HYPER_ELEMENT_ORDER_HOOK_SUCCESS_REGISTER_USER_ACCOUNT_MESSAGE'),
            '</div>'
        ]);

        $this->hyper['helper']['mindbox']->createUnauthorizedOrder($this->_order);

        $this->hyper['cms']->enqueueMessage($message, 'info');
    }

    /**
     * Process register user.
     *
     * @return  JSON
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    protected function _registerUser()
    {
        $password   = UserHelper::genRandomPassword();
        $buyerEmail = $this->_order->getBuyerEmail();

        $userRegisterData = [
            'password1' => $password,
            'password2' => $password,
            'email1'    => $buyerEmail,
            'name'      => $this->_order->getBuyer()
        ];

        $model = new HyperPcModelRegistration();
        $model->register($userRegisterData);

        $this->_registerUser = $model->getRegisterUser();

        $userRegisterData['username'] = $this->_registerUser->username;

        return new JSON($userRegisterData);
    }

    /**
     * Update user mobile phone from order.
     *
     * @param   int $userId
     *
     * @since   2.0
     */
    protected function _updateUserMobilePhone(int $userId)
    {
        PluginHelper::importPlugin('system');

        /** @var JUser $user */
        $user = Factory::getContainer()->get(UserFactoryInterface::class)->loadUserById($userId);
        $mobileAlias = $this->_config->get('alias_mobile');

        $dataParam = $user->getProperties();
        $dataParam['com_fields'][$mobileAlias] = $this->_order->getBuyerPhone();

        $dispatcher = Factory::getApplication()->getDispatcher();
        $event = new Event('onContentAfterSave', ['com_users.user', $user, false, $dataParam]);
        $dispatcher->dispatch('onContentAfterSave', $event);
    }

    /**
     * Finds user id by the phone number.
     *
     * @param   string $phone
     *
     * @return  int user id or 0 if there is no user with given phone.
     *
     * @throws  \Exception
     */
    protected function _findUserIdByPhoneNumber(string $phone)
    {
        $mobileAlias = $this->_config->get('alias_mobile');

        $xmlPhone = Xml::build([
            'field' => [
                '@name' => $mobileAlias,
                '@type' => 'uniquemobile',
            ]
        ]);

        $rule = new JFormRuleUniquemobile();

        $resultRule = $rule->test($xmlPhone, $phone);
        if (!$resultRule) {
            return (int) $xmlPhone['item_id'];
        }

        return 0;
    }
}
