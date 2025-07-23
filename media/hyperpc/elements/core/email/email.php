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
use HYPERPC\Data\JSON;
use Joomla\CMS\Language\Text;
use HYPERPC\Elements\Element;
use Joomla\CMS\Mail\MailHelper;

/**
 * Class ElementCoreEmail
 *
 * @since   2.0
 */
class ElementCoreEmail extends Element
{

    const USER_DEBUG_EMAIL_VALUE = 'save@hyperpc.ru';

    /**
     * Get site cart form data value by identifier.
     *
     * @return  mixed
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function getValue()
    {
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

        $value = $session->find('form.' . $this->getIdentifier() . '.value');

        $isAutoEmail = $this->hyper['helper']['string']->isAutoEmail($this->hyper['user']->email);

        if ($value === null && !empty($this->hyper['user']->id) && !$isAutoEmail) {
            $value = $this->hyper['user']->email;
        } elseif (JDEBUG && $this->hyper['cms']->isClient('site') && empty($this->hyper['user']->id)) {
            $value = self::USER_DEBUG_EMAIL_VALUE;
        }

        return $value;
    }

    /**
     * Validate element field.
     *
     * @param   array $data
     * @return  bool|\RuntimeException
     *
     * @throws  \RuntimeException
     *
     * @since   2.0
     */
    public function validate(array $data)
    {
        $data  = new JSON($data);
        $value = $data->get('value');
        if (parent::validate($data->getArrayCopy()) === true) {
            $pregResult = preg_match('/^(save@hyperpc\.ru|([\w\.\-\+#$^]+@((?!hyperpc\.|epix\.)\w+[\w\.\-]*?\.)[a-zA-Z]{2,18}))$/', $value);
            if (!MailHelper::isEmailAddress($value) || !$pregResult) {
                $message = Text::_('HYPER_ELEMENT_CORE_EMAIL_VALIDATE_ERROR');
                return new \RuntimeException($message);
            }
        }

        return true;
    }
}
