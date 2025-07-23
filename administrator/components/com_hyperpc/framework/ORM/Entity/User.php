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

namespace HYPERPC\ORM\Entity;

use Cake\Utility\Hash;
use HYPERPC\Data\JSON;
use JBZoo\Image\Image;
use Joomla\CMS\Factory;
use Joomla\Event\Event;
use Joomla\CMS\Date\Date;
use HYPERPC\Helper\UserHelper;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Plugin\PluginHelper;

/**
 * User class.
 *
 * @property    int         $id
 * @property    string      $name
 * @property    string      $username
 * @property    string      $email
 * @property    array       $groups
 * @property    string      $password
 * @property    string      $block
 * @property    string      $sendEmail
 * @property    Date        $registerDate
 * @property    Date        $lastvisitDate
 * @property    string      $activation
 * @property    JSON        $params
 * @property    Date        $lastResetTime
 * @property    int         $resetCount
 * @property    string      $otpKey
 * @property    string      $otep
 * @property    string      $requireReset
 * @property    array       $fields
 *
 * @property    UserHelper  $_helper
 *
 * @method      UserHelper  getHelper()
 *
 * @package     HYPERPC\ORM\Entity
 *
 * @since       2.0
 */
class User extends Entity
{

    const CABINET_MIN_ITEM_LIMIT = 5;

    const FIELD_PHONE_ALIAS = 'phone';
    const FIELD_UUID_ALIAS  = 'uuid';

    /**
     * Initialize hook method.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function initialize()
    {
        $this
            ->setTableType('User')
            ->setTablePrefix('JTable');

        parent::initialize();
    }

    /**
     * Get field by alias.
     *
     * @param   string $key
     *
     * @return  Field|mixed
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getField($key)
    {
        if (!empty($this->fields) && array_key_exists($key, $this->fields)) {
            return $this->fields[$key];
        }

        return new Field();
    }

    /**
     * Get uid.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getUid()
    {
        $uidFieldKey = $this->hyper['params']->get('user_uid_field');
        if (empty($uidFieldKey)) {
            return '';
        }

        return $this->getFieldValue($uidFieldKey);
    }

    /**
     * Check is manager group.
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function isManager()
    {
        foreach ((array) $this->_helper->getManagerGroup() as $groupId) {
            if (in_array((string) $groupId, $this->groups)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get profile edit url.
     *
     * @param   array  $args
     *
     * @return  mixed
     *
     * @since   2.0
     */
    public function getProfileEditUrl(array $args = [])
    {
        return  $this->hyper['route']->build(Hash::merge($args, [
            'user_id' => $this->id,
            'option'  => 'com_users',
            'task'    => 'profile.edit'
        ]));
    }

    /**
     * Get user configurations.
     *
     * @param   int  $limit
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getConfigurations($limit = self::CABINET_MIN_ITEM_LIMIT)
    {
        return $this->hyper['helper']['configuration']->getUserConfigurations($this->id, $limit);
    }

    /**
     * Get user orders.
     *
     * @param   int  $limit
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getOrders($limit = self::CABINET_MIN_ITEM_LIMIT)
    {
        return $this->hyper['helper']['order']->getUserOrders($this->id, $limit);
    }

    /**
     * Get user avatar.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getAvatar()
    {
        $avatar = $this->get('avatar');
        if ($avatar && File::exists(JPATH_ROOT . '/' . $avatar)) {
            $avatar = new Image(Path::clean(JPATH_ROOT . '/' . $avatar));
            return '/' . ltrim($avatar->getPath(), '/');
        }

        return $this->hyper['path']->url('img:user/placeholder.png');
    }

    /**
     * Get admin (backend) edit url.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getAdminEditUrl()
    {
        return '';
    }

    /**
     * Get user field value by field alias
     *
     * @param   string $fieldAlias
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getFieldValue($fieldAlias)
    {
        if ($this->id) {
            $fieldValue = $this->getField($fieldAlias)->get('value');
            if ($fieldValue === null) {
                $user = $this->_helper->findById($this->id, ['load_fields' => true]);
                $fieldValue = $user->getField($fieldAlias)->get('value');
            }

            if (!empty($fieldValue)) {
                return $fieldValue;
            }
        }

        return '';
    }

    /**
     * Get the uuid of the associated Moysklad counterparty
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getMoyskladUuid()
    {
        return $this->getFieldValue(self::FIELD_UUID_ALIAS);
    }

    /**
     * Get user phone
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getPhone()
    {
        return $this->getFieldValue(self::FIELD_PHONE_ALIAS);
    }

    /**
     * Set field value by field alias
     *
     * @param   string $fieldAlias
     * @param   string $fieldValue
     *
     * @return  void
     *
     * @since   2.0
     */
    public function setFieldValue($fieldAlias, $fieldValue)
    {
        if (!$this->id) {
            return;
        }

        PluginHelper::importPlugin('system');

        $jUser = Factory::getUser($this->id);

        $dataParam = $jUser->getProperties();
        $dataParam[JOOMLA_COM_FIELDS][$fieldAlias] = $fieldValue;

        $dispatcher = Factory::getApplication()->getDispatcher();
        $event      = new Event('onContentAfterSave', [
            JOOMLA_COM_USERS . '.user',
            $jUser,
            false,
            $dataParam
        ]);
        $dispatcher->dispatch('onContentAfterSave', $event);
    }

    /**
     * Set uid.
     *
     * @param   string $uid
     *
     * @return  void
     *
     * @since   2.0
     */
    public function setUid($uid)
    {
        $uidFieldKey = $this->hyper['params']->get('user_uid_field');

        $this->setFieldValue($uidFieldKey, $uid);
    }
}
