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

use JBZoo\Data\Data;
use HYPERPC\Data\JSON;
use HYPERPC\Elements\Element;
use Joomla\CMS\Language\Text;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;

/**
 * Class ElementCorePhone
 *
 * @since   2.0
 */
class ElementCorePhone extends Element
{

    const USER_PHONE_MIN_SIZE                  = 6;
    const USER_CUSTOM_FIELD_MOBILE_PHONE_ALIAS = 'phone';
    const USER_DEBUG_PHONE_VALUE               = '+7 (000) 000-00-00';
    const USER_DEBUG_EMAIL_VALUE               = 'save@hyperpc.ru';

    /**
     * User phone from custom field.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $_userPhone;

    /**
     * Initialize method.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function initialize()
    {
        parent::initialize();
        $user = $this->hyper['user'];
        if (!empty($user->id)) {
            $fields = FieldsHelper::getFields('com_users.user', $this->hyper['user']);
            /** @var object $field */
            foreach ($fields as $field) {
                if ($field->name === self::USER_CUSTOM_FIELD_MOBILE_PHONE_ALIAS) {
                    $this->_userPhone = $field->value;
                    break;
                }
            }
        }
    }

    /**
     * Get user phone from custom fields.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getUserPhone()
    {
        return $this->_userPhone;
    }

    /**
     * Get site cart form data value by identifier.
     *
     * @return  mixed
     *
     * @throws \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function getValue()
    {
        //  Set default debug value.
        if (JDEBUG && !$this->hyper['cms']->isClient('administrator')) {
            return self::USER_DEBUG_PHONE_VALUE;
        }

        //  Get saved value.
        $savedValue = $this->getConfig('data.value');
        if (!empty($savedValue)) {
            return $savedValue;
        }

        static $session;
        if ($session === null) {
            /** @var Data $session */
            $session = $this->hyper['helper']['session']->get();
        }

        $user  = $this->hyper['user'];
        $value = $session->find('form.' . $this->getIdentifier() . '.value');
        if ($value === null && !empty($user->id)) {
            $value = $this->_userPhone;
        }

        return $value;
    }

    /**
     * Validate data.
     *
     * @param   array $data
     *
     * @return  bool|\RuntimeException
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function validate(array $data)
    {
        $result = parent::validate($data);
        $data   = new JSON($data);
        $value  = $data->get('value');

        if ($result && $this->hyper['input']->get('view') === 'cart') {
            $formData = new JSON((array) $this->hyper['input']->get('jform', [], 'array'));

            //  Check min value.
            if (
                $value === self::USER_DEBUG_PHONE_VALUE &&
                $formData->find('elements.email.value') !== self::USER_DEBUG_EMAIL_VALUE
            ) {
                return new \RuntimeException(Text::_('HYPER_ELEMENT_CORE_PHONE_RESERVED_PHONE_ERROR'));
            }
        }

        return $result;
    }
}
