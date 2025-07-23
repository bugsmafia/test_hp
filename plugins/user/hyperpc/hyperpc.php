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

use Cake\Utility\Xml;
use HYPERPC\Data\JSON;
use JBZoo\Utils\Filter;
use Joomla\Event\Event;
use Joomla\Input\Cookie;
use Joomla\CMS\Form\Form;
use HYPERPC\Joomla\Factory;
use HYPERPC\ORM\Entity\User;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\FormHelper;
use HYPERPC\Helper\MindboxHelper;
use HYPERPC\Joomla\Plugin\CMSPlugin;
use HYPERPC\Joomla\Model\Entity\Field;
use Joomla\CMS\Component\ComponentHelper;

//  No direct access.
defined('_JEXEC') or die('Restricted access');

jimport('joomla.plugin.plugin');

/**
 * Class PlgUserHyperPC
 *
 * @since 2.0
 */
class PlgUserHyperPC extends CMSPlugin
{

    const USER_ACTIVATION_MOBILE = 3;

    /**
     * Pending profile data changing flag
     *
     * @var     bool
     *
     * @since   2.0
     */
    protected $_pendingProfileFill = false;

    /**
     * Hold user changed fields on save
     *
     * @var     array
     *
     * @since   2.0
     */
    protected $_changedFields = [];

    /**
     * This event is triggered before an update of a user record.
     *
     * @param   array   $oldUser
     * @param   bool    $isNew
     * @param   array   $newUser
     *
     * @return  bool
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function onUserBeforeSave($oldUser, $isNew, $newUser)
    {
        $oldData    = new JSON($oldUser);
        $newData    = new JSON($newUser);
        $phoneAlias = $this->params->get('phone_field', 'phone');
        $phoneValue = $newData->find('com_fields.' . $phoneAlias);

        $language = Factory::getApplication()->getLanguage();
        if (!array_key_exists(HP_OPTION, $language->getPaths())) {
            $language->load(HP_OPTION);
        }

        if (!$isNew) {
            if ($oldData->get('email', '') !== $newData->get('email', '')) {
                $this->_changedFields[] = 'email';
            }

            // other fields are not needed now
        }

        if ($phoneValue && $this->hyper['helper']['string']->isValidPhone((string) $phoneValue)) {
            JLoader::register('JFormRuleUniquemobile', JPATH_ROOT . '/components/' . HP_OPTION . '/models/rules/uniquemobile.php');

            $xmlPhone = Xml::build([
                'field' => [
                    '@type'         => 'uniquemobile',
                    '@name'         => $phoneAlias,
                    '@description'  => 'JFIELD_FIELDS_CATEGORY_DESC'
                ]
            ]);

            $rule = new JFormRuleUniquemobile();

            $resultRule   = $rule->test($xmlPhone, $phoneValue);
            $resultItemId = Filter::int((string) $xmlPhone['item_id']);
            $userId       = $newData->get('id', 0, 'int');

            if ($resultRule) {
                return true;
            }

            $return = ($resultItemId === $userId);
            if (!$return) {
                $this->hyper['cms']->enqueueMessage(Text::_('COM_HYPERPC_ERROR_USER_PHONE_EXIST'), 'error');

                if ($this->hyper['app']->isClient('site')) {
                    $this->hyper['cms']->redirect($this->hyper['route']->build([
                        'option'    => 'com_users',
                        'layout'    => 'edit',
                        'id'        => $userId
                    ]));
                }

                if ($this->hyper['app']->isClient('administrator')) {
                    $this->hyper['cms']->redirect($this->hyper['route']->build([
                        'option'    => 'com_users',
                        'view'      => 'user',
                        'layout'    => 'edit',
                        'id'        => $userId
                    ]));
                }

                return false;
            }

            return $return;
        }

        return true;
    }

    /**
     * This event is triggered after an update of a user record, or when a new user has been stored in the database.
     *
     * @param   $userORM
     * @param   $isNew
     * @param   $success
     * @param   $msg
     *
     * @return  void
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function onUserAfterSave($userORM, $isNew, $success, $msg)
    {
        $params = ComponentHelper::getParams('com_users');

        //  HYPERPC reload registration.
        if ($success &&
            $params->get('use_hyperpc_component')
        ) {
            $input = $this->hyper['input'];
            $isCreateOrderTask = $input->get('view') === 'cart' && $input->get('task') === 'save';
            $userData = new JSON($userORM);

            if ($this->hyper['app']->isClient('site')) {
                $user       = Factory::getUser($userORM['id']);
                $dataParam  = $user->getProperties();
                $dataParam['com_fields'] = $userData->get('com_fields');

                $dispatcher = Factory::getApplication()->getDispatcher();
                $event      = new Event('onContentAfterSave', ['com_users.user', $user, false, $dataParam]);
                $dispatcher->dispatch('onContentAfterSave', $event);

                //  Execute for cart hook element "add_user".
                if (!$isCreateOrderTask) {
                    JLoader::register('UserLibAmoCrm', JPATH_ROOT . '/plugins/user/hyperpc/lib/UserLibAmoCrm.php');
                    (new UserLibAmoCrm($userORM['id'], $this->params))->sendToAmo();
                }
            }

            /** @var MindboxHelper */
            $mindboxHelper   = $this->hyper['helper']['mindbox'];
            $phoneFieldAlias = $this->params->get('phone_field', 'phone');

            $userId    = $userData->get('id');
            $name      = $this->_getUserSpecificName($userData->getArrayCopy());
            $userEmail = !$this->hyper['helper']['string']->isAutoEmail($userData->get('email')) ? strtolower($userData->get('email')) : null;
            $userPhone = $userData->find('com_fields.' . $phoneFieldAlias);

            if ($isNew) { // new user registered, except on order created
                if (!$isCreateOrderTask) {
                    $mindboxHelper->registerCustomer($userId, $name, $userEmail, $userPhone);

                    if (empty($userPhone) || $this->hyper['helper']['string']->isAutoEmail($userData->get('name'))) {
                        $this->_pendingProfileFill = true;
                    }
                }
            } elseif ($input->get('task') !== 'step-one') { // existed user edited, exclude auth
                $userPhone = $userData->find('com_fields.' . $phoneFieldAlias) ?? '';
                if ($this->hyper['app']->isClient('site')) { // only edit from site
                    $mindboxHelper->editCustomer($userId, $name, $userEmail, $userPhone, $this->_changedFields);
                } else {
                    $mindboxHelper->editCustomer($userId, $name, $userEmail, $userPhone);
                }
                if (empty($userPhone)) {
                    $this->_pendingProfileFill = true;
                }
            }
        }
    }

    /**
     * This event is triggered after user login
     *
     * @param   array $options  Array holding options (user, responseType)
     *
     * @return  void
     *
     * @since   2.0
     */
    public function onUserAfterLogin($options)
    {
        /** @var Cookie $cookie */
        $cookie = $this->hyper['input']->cookie;
        if ($cookie->get(HP_COOKIE_HMP)) {
            return; // don't set uid for managers
        }

        /** @var User $user */
        $user = $this->hyper['helper']['user']->findById($options['user']->id);

        $userUid = $user->getUid();
        if (!empty($userUid)) {
            $expire = '2147483647';
            $cookie->set(
                HP_COOKIE_UID,
                $userUid,
                $expire,
                $this->hyper['cms']->get('cookie_path', '/')
            );
        } else {
            $uid = $cookie->get(HP_COOKIE_UID);
            $user->setUid($uid);
        }
    }

    /**
     * This event is triggered after an update of a user record.
     *
     * @param   $context
     * @param   $user
     * @param   $isNew
     * @param   $userData
     *
     * @return  void
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function onContentAfterSave($context, $user, $isNew, $userData)
    {
        if ($this->_pendingProfileFill) {
            /** @var MindboxHelper */
            $mindboxHelper   = $this->hyper['helper']['mindbox'];
            $phoneFieldAlias = $this->params->get('phone_field', 'phone');

            $userData  = new JSON($userData);

            $userId    = $userData->get('id');
            $userPhone = $userData->find('com_fields.' . $phoneFieldAlias);

            $mindboxHelper->editCustomer($userId, null, null, $userPhone);
        }
    }

    /**
     * Adds additional fields to the user editing form
     *
     * @param   Form   $form  The form to be altered.
     * @param   mixed  $data  The associated data for the form.
     *
     * @return  void
     *
     * @since   1.6
     */
    public function onContentPrepareForm(Form $form, $data)
    {
        $name   = $form->getName();
        $params = ComponentHelper::getParams('com_users');

        //  Only user mobile activate.
        if (
            $name === 'com_users.registration' &&
            $params->get('use_hyperpc_component') &&
            $this->hyper['app']->isClient('site')
        ) {
            $phoneAlias = Filter::bool($this->params->get('enable_captcha', true));

            $form->removeField('email2');
            $form->removeField('username');
            $form->removeField('password1');
            $form->removeField('password2');

            if ($phoneAlias) {
                $form->removeField('captcha');
            }

            FormHelper::addRulePath($this->hyper['path']->get('site:models/rules'));

            /** @var Field $field */
            foreach ($this->_getCustomFields() as $field) {
                $type = ($field->name === 'phone') ? 'tel' : $field->type;

                $form->setField(Xml::build([
                    'field' => [
                        '@type'     => $type,
                        '@validate' => 'uniquemobile',
                        '@name'     => $field->name,
                        '@label'    => $field->label,
                        '@required' => $field->required
                    ]
                ]), null, true);
            }
        }
    }

    /**
     * Get custom fields.
     *
     * @return  array
     *
     * @since   2.0
     */
    protected function _getCustomFields()
    {
        $db = $this->hyper['db'];

        $fieldNames = [];
        foreach ((array) $this->params->get('registration_fields') as $fieldName) {
            $fieldNames[] = $db->quote($fieldName);
        }

        static $items;
        if (count($fieldNames) && !$items) {
            $query = $db
                ->getQuery(true)
                ->select(['f.*'])
                ->from($db->quoteName('#__fields', 'f'))
                ->where([
                    $db->quoteName('f.context') . ' = ' . $db->quote('com_users.user'),
                    $db->quoteName('f.name')    . ' IN(' . implode(', ', $fieldNames) . ')',
                    $db->quoteName('f.state')   . ' = ' . $db->quote(HP_STATUS_PUBLISHED)
                ])
                ->order($db->quoteName('f.ordering') . ' ASC');

            $_fields = $db->setQuery($query)->loadAssocList('id');
            $fields  = [];
            foreach ($_fields as $id => $field) {
                $fields[$id] = new Field($field);
            }

            $items = $fields;
        }

        return (array) $items;
    }

    /**
     * Get name of user if it not automatically set
     *
     * @param   array $userData
     *
     * @return  string
     *
     * @since   2.0
     */
    protected function _getUserSpecificName($userData)
    {
        $userData = new JSON($userData);
        $name = $userData->get('name');
        if (
            $this->hyper['helper']['string']->isAutoEmail($name) ||
            $name === $this->hyper->getContext() . '-' . $userData->get('id') ||
            $name === $userData->get('email')
        ) {
            return '';
        }

        return $name;
    }
}
