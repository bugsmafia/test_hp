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

namespace HYPERPC\Joomla\Model\Entity;

use HYPERPC\Data\JSON;

/**
 * Class User
 *
 * @package     HYPERPC\Joomla\Model\Entity
 *
 * @since       2.0
 */
class User extends Entity
{

    /**
     * Id.
     *
     * @var     int
     *
     * @since   2.0
     */
    public $id;

    /**
     * Field name.
     *
     * @var     int
     *
     * @since   2.0
     */
    public $name;

    /**
     * Field name.
     *
     * @var     int
     *
     * @since   2.0
     */
    public $username;
    public $email;
    public $password;
    public $block;
    public $sendEmail;
    public $registerDate;
    public $lastvisitDate;
    public $activation;
    public $lastResetTime;
    public $resetCount;
    public $otpKey;
    public $otep;
    public $requireReset;

    /**
     * Params.
     *
     * @var     JSON
     *
     * @since   2.0
     */
    public $params;

    /**
     * Get site view category url.
     *
     * @return  string
     * @param   array $query
     *
     * @since   2.0
     */
    public function getViewUrl(array $query = [])
    {
        return null;
    }

    /**
     * Fields of integer data.
     *
     * @return  array
     *
     * @since   2.0
     */
    protected function _getFieldInt()
    {
        return ['id', 'resetCount'];
    }

    /**
     * Fields of integer data.
     *
     * @return  array
     *
     * @since   2.0
     */
    protected function _getFieldJsonData()
    {
        return ['params', 'otep'];
    }

    /**
     * Fields of boolean data.
     *
     * @return  array
     *
     * @since   2.0
     */
    protected function _getFieldBoolean()
    {
        return ['block', 'sendEmail', 'requireReset'];
    }

    /**
     * Fields of datetime.
     *
     * @return  array
     *
     * @since   2.0
     */
    protected function _getFieldDate()
    {
        return ['registerDate', 'lastvisitDate', 'lastResetTime'];
    }
}
