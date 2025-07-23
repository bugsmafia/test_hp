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

use JBZoo\Data\Data;
use JBZoo\Utils\Str;
use HYPERPC\Data\JSON;
use Joomla\CMS\Factory;
use Joomla\Event\Event;
use Cake\Utility\Inflector;
use HYPERPC\ORM\Entity\User;
use Joomla\CMS\Language\Text;
use Joomla\CMS\User\UserHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\User\UserFactoryInterface;
use HYPERPC\Elements\ElementConfigurationHook;
use HYPERPC\Joomla\Model\Entity\SaveConfiguration;

/**
 * Class ElementConfiguratorHookAddUser
 *
 * @since   2.0
 */
class ElementConfiguratorHookAddUser extends ElementConfigurationHook
{

    /**
     * System controller message.
     *
     * @var     array
     *
     * @since   2.0
     */
    protected $_sysMessage = [];

    /**
     * Get controller system action message.
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getSystemMessage()
    {
        return $this->_sysMessage;
    }

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
        $user = Factory::getUser();
        $data = $this->_config->find('data.form');

        /** @var SaveConfiguration $configuration */
        $configuration = $this->_config->find('data.configuration');

        if ($configuration->created_user_id) {
            return null;
        }

        if ($data instanceof Data && !$user->id && $configuration->id) {
            $password = UserHelper::genRandomPassword();
            $email    = $data->get('email');

            $userData = new JSON([
                'email1'    => $email,
                'password1' => $password,
                'password2' => $password,
            ]);

            if (!empty($data->get('username'))) {
                $userData->set('name', $data->get('username'));
            }

            $model = new HyperPcModelRegistration();
            $model->register($userData->getArrayCopy());

            $registeredUser = $model->getRegisterUser();
            $userData->set('username', $registeredUser->username);

            $userId = $registeredUser->id;

            /** @var HyperPcTableSaved_Configurations $table */
            $table = $this->hyper['helper']['configuration']->getTable();

            if ((int) $userId > 0) {
                $configuration->set('created_user_id', $userId);
                $table->save($configuration);

                if ($userData->get('username') && $userData->get('password1')) {
                    $this->hyper['cms']->login([
                        'username' => $userData->get('username'),
                        'password' => $userData->get('password1')
                    ], ['remember' => true]);
                }

                $mobileAlias = $this->_config->get('alias_mobile');
                if (!empty($mobileAlias)) {
                    PluginHelper::importPlugin('system');

                    $authUser = Factory::getContainer()->get(UserFactoryInterface::class)->loadUserById($userId);

                    $dataParam = $authUser->getProperties();
                    $dataParam[JOOMLA_COM_FIELDS][$mobileAlias] = $data->get('phone');

                    $dispatcher = Factory::getApplication()->getDispatcher();
                    $event      = new Event('onContentAfterSave', [JOOMLA_COM_USERS . '.user', $authUser, false, $dataParam]);
                    $dispatcher->dispatch('onContentAfterSave', $event);
                }

                $this->_sysMessage = [
                    Text::sprintf(
                        'HYPER_ELEMENT_CONFIGURATOR_SAVE_FORM_ADD_USER_MESSAGE',
                        Inflector::camelize($data->get('username')),
                        Str::up($this->hyper['params']->get('site_context'))
                    )
                ];
            } else {
                /** @var User $userEntity */
                $userEntity = $this->hyper['helper']['user']->findByEmail($email);

                if ($userEntity->id) {
                    $configuration->set('created_user_id', $userEntity->id);
                    $table->save($configuration->getArray());

                    $this->_sysMessage = [
                        Text::sprintf(
                            'HYPER_ELEMENT_CONFIGURATOR_SAVE_FORM_ADD_USER_FIND_ACCOUNT_MESSAGE',
                            Inflector::camelize($data->get('username')),
                            $configuration->id
                        )
                    ];
                }
            }
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
        Factory::getApplication()->getLanguage()->load(JOOMLA_COM_USERS);

        parent::initialize();
    }

    /**
     * Add controller system action message.
     *
     * @param   array  $array
     *
     * @return  $this
     *
     * @since   2.0
     */
    public function setSystemMessage(array $array = [])
    {
        $this->_sysMessage = $array;
        return $this;
    }
}
