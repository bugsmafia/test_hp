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
use HYPERPC\Joomla\Factory;
use HYPERPC\ORM\Table\Table;
use HYPERPC\ORM\Entity\User;
use HYPERPC\ORM\Entity\Field;
use Joomla\CMS\User\User as JUser;
use Joomla\CMS\Plugin\PluginHelper;
use HYPERPC\Joomla\Model\Entity\Entity;
use HYPERPC\Helper\Context\EntityContext;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;

/**
 * Class UserHelper
 *
 * @method      User    findById($value, array $options = [])
 * @method      User    findByEmail($value, array $options = [])
 *
 * @package     HYPERPC\Helper
 *
 * @since       2.0
 */
class UserHelper extends EntityContext
{

    const SESSION_NAMESPACE = 'user';

    /**
     * get view level title by id
     *
     * @param   int  $viewlevelId
     *
     * @return  string
     *
     * @throws  RuntimeException
     *
     * @since   2.0
     */
    public function getViewlevelTitle($viewlevelId)
    {
        $db = Factory::getDbo();

        $query = $db->getQuery(true)
            ->select('id, title')
            ->from($db->quoteName('#__viewlevels', 'a'))
            ->where($db->quoteName('a.id') . ' = ' . $viewlevelId);

        $db->setQuery($query);

        if ($result = $db->loadObject()) {
            return $result->title;
        }

        return '';
    }

    /**
     * Find by entity object column key value.
     *
     * @param   string  $key          Key of table column.
     * @param   mixed   $value        Value of table column.
     * @param   array   $options
     *
     * @return  User
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function findBy($key, $value, array $options = [])
    {
        /** @var User $user */
        $user    = parent::findBy($key, $value, $options);
        $options = new JSON($options);

        if ($user->id && $options->get('load_fields')) {
            $fields     = [];
            $listFields = FieldsHelper::getFields('com_users.user', $user, true);
            foreach ($listFields as $listField) {
                $fields[$listField->name] = new Field((array) $listField);
            }
            $user->set('fields', $fields);
        }

        $fUser = Factory::getUser($user->id);
        $user->set('groups', $fUser->groups);

        return $user;
    }

    /**
     * Check user group is in manager the groups.
     *
     * @param   array  $userGroups
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function isInManagerGroup(array $userGroups)
    {
        $managerGroup = $this->getManagerGroup();
        foreach ($userGroups as $group) {
            if (in_array((string) $group, $managerGroup)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Reassign data from old user to current and delete old user
     *
     * @param string $oldUserId
     * @param string $userId
     *
     * @since 2.0
     */
    public function reassignUserData(string $oldUserId, string $userId)
    {
        $this->hyper['helper']['order']->reassignUser($oldUserId, $userId);
        $this->hyper['helper']['configuration']->reassignUser($oldUserId, $userId);
        $this->hyper['helper']['review']->reassignUser($oldUserId, $userId);
        $this->hyper['helper']['note']->reassignUser($oldUserId, $userId);

        $this->removeUserById($oldUserId);
    }

    /**
     * Reassign user phone from old user to current
     *
     * @param  string $oldUserId
     * @param  string $userId
     *
     * @return false|string
     *
     * @throws \Exception
     *
     * @since  2.0
     */
    public function reassignPhone(string $oldUserId, string $userId)
    {
        $oldUser      = $this->findById($oldUserId, ['load_fields' => true]);
        $oldUserPhone = $oldUser->getPhone();

        $user      = $this->findById($userId, ['load_fields' => true]);
        $userPhone = $user->getPhone();

        if (empty($oldUserPhone) && !empty($userPhone)) {
            return false;
        }

        $fieldId = $oldUser->getField('phone')->id;
        $db      = $this->hyper['db'];
        $query   = $db->getQuery(true);

        $query
            ->update($db->qn(JOOMLA_TABLE_FIELDS_VALUES))
            ->set([
                $db->qn('item_id') . ' = ' . $db->q($userId)
            ])
            ->where([
                $db->qn('field_id') . ' = ' . $db->q($fieldId),
                $db->qn('item_id')  . ' = ' . $db->q($oldUserId),
            ]);

        $db->setQuery($query)->execute();

        return $oldUserPhone;
    }

    /**
     * Remove user and all their data
     *
     * @param  string $id
     *
     * @return mixed
     *
     * @since  2.0
     */
    public function removeUserById(string $id)
    {
        PluginHelper::importPlugin('user');
        $user = JUser::getInstance($id);

        $dispatcher = Factory::getApplication()->getDispatcher();
        $event      = new Event('onUserBeforeDelete', [$user]);
        $dispatcher->dispatch('onUserBeforeDelete', $event);

        return $user->delete();
    }

    /**
     * Get user manager group from component settings.
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getManagerGroup()
    {
        return (array) $this->hyper['params']->get('user_manager_group');
    }

    /**
     * Initialize helper.
     *
     * @return  void
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function initialize()
    {
        $this->setTable(Table::getInstance('User', 'JTable'));
        parent::initialize();
    }

    /**
     * Get table entity.
     *
     * @return Entity|string
     *
     * @since 2.0
     */
    protected function _getTableEntity()
    {
        return 'HYPERPC\\ORM\\Entity\\User';
    }
}
