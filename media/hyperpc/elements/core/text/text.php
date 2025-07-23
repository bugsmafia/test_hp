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

use HYPERPC\Elements\Element;

/**
 * Class ElementCoreText
 *
 * @since   2.0
 */
class ElementCoreText extends Element
{

    const USER_DEBUG_USERNAME_VALUE = 'save@hyperpc.ru';
    const USERNAME_LAYOUT           = 'username';

    /**
     * Get site cart form data value by identifier.
     *
     * @return  mixed
     *
     * @since   2.0
     */
    public function getValue()
    {
        static $session;
        if ($session === null) {
            $session = $this->hyper['helper']['session']->get();
        }

        $user  = $this->hyper['user'];
        $value = $session->find('form.' . $this->getIdentifier() . '.value');

        if ($value === null && !empty($user->id) && $this->_config->get('layout') === self::USERNAME_LAYOUT && $user->name !== $user->email && !preg_match('/^(hyperpc|epix)-\d+/', $user->name)) {
            $value = $user->name;
        } elseif (JDEBUG && $this->_config->get('layout') === self::USERNAME_LAYOUT && empty($user->id)) {
            $value = self::USER_DEBUG_USERNAME_VALUE;
        }

        return $value;
    }
}
