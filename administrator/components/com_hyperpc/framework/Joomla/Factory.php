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

namespace HYPERPC\Joomla;

use Joomla\CMS\Version;
use Joomla\CMS\Mail\Mail;
use Joomla\CMS\Factory as BaseFactory;

/**
 * Class Factory
 *
 * @package HYPERPC\Joomla
 *
 * @since   2.0
 */
abstract class Factory extends BaseFactory
{

    /**
     * Global database object
     *
     * @var    \JDatabaseDriver
     *
     * @since  2.0
     */
    public static $database = null;

    /**
     * Component database object.
     *
     * @var     \JDatabaseDriver
     *
     * @since   2.0
     */
    public static $databaseComponent;

    /**
     * Get a database object.
     *
     * Returns the global {@link \JDatabaseDriver} object, only creating it if it doesn't already exist.
     *
     * @return  \JDatabaseDriver
     *
     * @since   2.0
     */
    public static function getDbo()
    {
        if (!self::$database) {
            self::$database = self::createDbo();
        }

        return self::$database;
    }

    /**
     * Get a mailer object.
     *
     * Returns the global {@link \JMail} object, only creating it if it doesn't already exist.
     *
     * @return  \JMail|Mail object
     *
     * @see     JMail
     * @since   1.7.0
     */
    public static function getMailer()
    {
        if (!self::$mailer) {
            self::$mailer = self::createMailer();
        }

        $config = self::getConfig();

        $sender = [
            $config->get('mailfrom'),
            $config->get('fromname')
        ];

        $copy = clone self::$mailer;

        $copy
            ->isHtml(true)
            ->setSender($sender);

        return $copy;
    }

    /**
     * Create a database object
     *
     * @return  \JDatabaseDriver
     *
     * @since   2.0
     */
    protected static function createDbo()
    {
        $config   = self::getConfig();

        $options = [
            'database' => $config->get('db'),
            'host'     => $config->get('host'),
            'user'     => $config->get('user'),
            'driver'   => $config->get('dbtype'),
            'prefix'   => $config->get('dbprefix'),
            'password' => $config->get('password')
        ];

        try {
            $version = new Version();
            if ($version->getShortVersion() > 4) {
                $db = new \Joomla\Database\DatabaseFactory;
            } else {
                $db = new \JDatabaseFactory;
            }

            return $db->getDriver('mysqli', $options);
        } catch (\RuntimeException $e) {
            if (!headers_sent()) {
                header('HTTP/1.1 500 Internal Server Error');
            }

            jexit('Database Error: ' . $e->getMessage());
        }
    }
}
