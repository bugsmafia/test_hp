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

/**
 * Class Lead
 *
 * @package HYPERPC\Joomla\Model\Entity
 *
 * @since   2.0
 */
class Lead extends Entity
{

    /**
     * Lead id.
     *
     * @var     int
     *
     * @since   2.0
     */
    public $id;

    /**
     * User name.
     *
     * @var     string
     *
     * @since   2.0
     */
    public $username;

    /**
     * User email.
     *
     * @var     string
     *
     * @since   2.0
     */
    public $email;

    /**
     * User consent.
     *
     * @var     bool
     *
     * @since   2.0
     */
    public $consent;

    /**
     * Type of lead.
     *
     * @var     int
     *
     * @since   2.0
     */

    public $type;

    /**
     * Lead history.
     *
     * @var     int
     *
     * @since   2.0
     */
    public $history;

    /**
     * Lead params.
     *
     * @var     int
     *
     * @since   2.0
     */
    public $params;

    /**
     * Lead created date.
     *
     * @var     int
     *
     * @since   2.0
     */
    public $created;

    /**
     * Lead modified date.
     *
     * @var     int
     *
     * @since   2.0
     */
    public $modified;

    /**
     * Get site view category link.
     *
     * @param   array $query
     * @return  null|string
     *
     * @since   2.0
     */
    public function getViewUrl(array $query = [])
    {
        return null;
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
        return ['consent'];
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
        return ['type', 'id'];
    }
}
