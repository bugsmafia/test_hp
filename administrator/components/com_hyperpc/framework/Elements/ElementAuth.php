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

namespace HYPERPC\Elements;

use Cake\Utility\Xml;
use HYPERPC\Data\JSON;
use JBZoo\Utils\Filter;
use HYPERPC\ORM\Entity\User;
use HYPERPC\Joomla\Form\Form;
use HYPERPC\Helper\AuthHelper;
use HYPERPC\Helper\UserHelper;
use HYPERPC\ORM\Table\Table;
use Joomla\CMS\Captcha\Captcha;

defined('_JEXEC') or die('Restricted access');

/**
 * Class ElementAuth
 *
 * @property    AuthHelper  $_hAuth
 * @property    UserHelper  $_hUser
 * @property    null|string $_newPassword
 * @property    User        $_user
 *
 * @package     HYPERPC\Elements
 *
 * @since       2.0
 */
abstract class ElementAuth extends Element
{

    const CUSTOM_FIELD_PHONE_ALIAS = 'phone';

    /**
     * Hold flag use captcha.
     *
     * @var     bool
     *
     * @since   2.0
     */
    protected $_useCaptcha = false;

    /**
     * Check user can sing in.
     *
     * @param   JSON  $output
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function canSignIn(JSON $output)
    {
        $user = $this->getUserByRequest();
        return !$this->_hUser->isInManagerGroup((array) $user->groups);
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
    abstract public function checkEditUniqueValue(JSON &$output);

    /**
     * Get auth form instance.
     *
     * @return  \Joomla\CMS\Form\Form
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getAuthForm()
    {
        Form::addFormPath($this->getPath('form'));
        $form = $this->_hAuth->getAuthForm($this->_type);

        if ($this->getUseCaptcha() === true) {
            $form->setField(Xml::build([
                'field' => [
                    '@id'       => 'captcha_' . $this->getType(),
                    '@type'     => 'captcha',
                    '@validate' => 'captcha',
                    '@required' => 'required',
                    '@name'     => 'captcha'
                ]
            ]), null, true);
        }

        return $form;
    }

    /**
     * Get user custom field phone alias.
     *
     * @return  string
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function getCustomFieldMobileAlias()
    {
        $mobileAlias = self::CUSTOM_FIELD_PHONE_ALIAS;
        /** @var \ElementOrderHookAddUser $addUserElement */
        $addUserElement = $this->getManager()->getElement('order_after_save', 'add_user');
        if ($addUserElement instanceof \ElementOrderHookAddUser) {
            $mobileAlias = $addUserElement->getConfig('alias_mobile');
        }

        return $mobileAlias;
    }

    /**
     * Get request edit value.
     *
     * @return  mixed
     *
     * @since   2.0
     */
    public function getEditRequestValue()
    {
        return $this->hyper['input']->post->get('value', '', 'string');
    }

    /**
     * Get html of the confirm merge accounts.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getMergeConfirmForm()
    {
        return $this->render(['layout' => 'merge_confirm']);
    }

    /**
     * Get new password.
     *
     * @return  string|null
     *
     * @since   2.0
     */
    public function getNewPassword()
    {
        return $this->_newPassword;
    }

    /**
     * Get request data.
     *
     * @param   null|string  $key
     *
     * @return  JSON|mixed
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function getRequest($key = null)
    {
        $request = new JSON($this->hyper['input']->get('jform', [], 'array'));
        return ($key) ? $request->find($key) : $request;
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
        return $this->getRequest('email');
    }

    /**
     * Get use captcha flag.
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function getUseCaptcha()
    {
        return $this->_useCaptcha;
    }

    /**
     * Get holder user entity.
     *
     * @return  User
     *
     * @since   2.0
     */
    public function getUser()
    {
        return $this->_user;
    }

    /**
     * Get user entity by request data.
     *
     * @return  User
     *
     * @since   2.0
     */
    abstract public function getUserByRequest();

    /**
     * Initialize method.
     *
     * @return  void
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function initialize()
    {
        parent::initialize();

        $this->_user  = new User();
        $this->_hAuth = $this->hyper['helper']['auth'];
        $this->_hUser = $this->hyper['helper']['user'];

        static $isHighActivity;

        if (!isset($isHighActivity)) {
            /** @var \HyperPcTableForm_Counter $tableCounter */
            $tableCounter = Table::getInstance('Form_Counter');
            $tableCounter->checkHighActive();
            $isHighActivity = $tableCounter->isHighActive();

            if ($isHighActivity) {
                $captcha = Captcha::getInstance('recaptcha');
                $captcha->initialise(null);
            }
        }

        if ($isHighActivity) {
            $this->setUseCaptcha(true);
        }
    }

    /**
     * Check for edit attempts exceeded.
     *
     * @return  bool
     *
     * @since   2.0
     */
    abstract public function isEditExceeded();

    /**
     * Check is enabled element.
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function isEnabled()
    {
        return Filter::bool($this->_config->get('is_enable', HP_STATUS_PUBLISHED));
    }

    /**
     * Event on create new user.
     *
     * @param   JSON  $output
     *
     * @return  void
     *
     * @since   2.0
     */
    abstract public function onCreateNewUser(JSON &$output);

    /**
     * Event on first step success auth.
     *
     * @param   JSON  $output
     *
     * @return  void
     *
     * @since   2.0
     */
    abstract public function onFirstStepSuccess(JSON &$output);

    /**
     * On success edit value.
     *
     * @param   User  $user
     * @param   JSON  $output
     *
     * @return  void
     *
     * @since   2.0
     */
    abstract public function onSuccessEditValue(User $user, JSON &$output);

    /**
     * On success send edit code.
     *
     * @param   JSON  $output
     *
     * @return  void
     *
     * @since   2.0
     */
    abstract public function onSuccessSendEditCode(JSON &$output);

    /**
     * Render account edit.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function renderAccountEdit()
    {
        return $this->render(['layout' => 'edit']);
    }

    /**
     * Send edit code.
     *
     * @param   JSON  $output
     *
     * @return  mixed
     *
     * @since   2.0
     */
    abstract public function sendEditCode(JSON &$output);

    /**
     * Set new password.
     *
     * @param   string  $pass
     *
     * @return  $this
     *
     * @since   2.0
     */
    public function setNewPassword($pass)
    {
        $this->_newPassword = Filter::int($pass);
        return $this;
    }

    /**
     * Setup use captcha flag.
     *
     * @param   bool    $value
     *
     * @return  $this
     *
     * @since   2.0
     */
    public function setUseCaptcha($value)
    {
        $this->_useCaptcha = Filter::bool($value);
        return $this;
    }

    /**
     * Set holder user entity.
     *
     * @param   User  $user
     *
     * @return  $this
     *
     * @since   2.0
     */
    public function setUser(User $user)
    {
        $this->_user = $user;
        return $this;
    }

    /**
     * Action log.
     *
     * @param   string $message
     *
     * @since   2.0
     */
    protected function _log($message)
    {
        $this->hyper->log($message, null, 'auth/' . date('Y/m/d') . '/' . $this->_type . '.php');
    }
}
