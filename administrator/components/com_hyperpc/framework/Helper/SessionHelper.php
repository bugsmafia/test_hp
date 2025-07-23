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
use JBZoo\Utils\Filter;
use Joomla\CMS\Factory;
use Joomla\Input\Cookie;

/**
 * Class SessionHelper
 *
 * @package HYPERPC\Helper
 *
 * @since   2.0
 */
class SessionHelper extends AppHelper
{

    const TYPE_COOKIE  = 'cookie';
    const TYPE_DEFAULT = 'session';

    /**
     * Hold cookie lifetime.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $_cookieLifetime;

    /**
     * Default cart namespace.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $_namespace = 'hyperpc';

    /**
     * Cart session namespace.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $_sessionNamespace = 'hpcart';

    /**
     * Hold session work type.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $_sessionType = self::TYPE_DEFAULT;

    /**
     * Check message existence.
     *
     * @param   string $msg
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function checkMessage($msg)
    {
        $messages = $this->hyper['cms']->getMessageQueue();
        foreach ($messages as $item) {
            if ($item['message'] === $msg) {
                return true;
            }
        }

        return false;
    }

    /**
     * Clear cart session.
     *
     * @param   string          $key
     * @param   array|mixed     $default
     *
     * @return  $this
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function clear($key, $default = [])
    {
        $this->set($key, $default);
        return $this;
    }

    /**
     * Get cart session.
     *
     * @return  JSON
     *
     * @since   2.0
     */
    public function get()
    {
        if ($this->getType() === self::TYPE_DEFAULT) {
            $session = Factory::getSession();
            $result  = $session->get($this->_sessionNamespace, [], $this->_namespace);
            return new JSON($result);
        } elseif ($this->getType() === self::TYPE_COOKIE) {
            /** @var Cookie $cookie */
            $cookie = $this->hyper['input']->cookie;
            return new JSON(base64_decode($cookie->get(md5($this->_sessionNamespace))));
        }

        return new JSON();
    }

    /**
     * Get cookie lifetime.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getCookieLifetime()
    {
        return $this->_cookieLifetime;
    }

    /**
     * Get session namespace.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getNamespace()
    {
        return $this->_sessionNamespace;
    }

    /**
     * Get session work type.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getType()
    {
        return $this->_sessionType;
    }

    /**
     * Initialize helper.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function initialize()
    {
        $this->setCookieLifetime(Factory::getConfig()->get('lifetime'));
    }

    /**
     * Setup session data.
     *
     * @param   string $key
     * @param   mixed  $value
     *
     * @return  void
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function set($key, $value)
    {
        $result = $this->get();
        $result->set($key, $value);

        if ($this->getType() === self::TYPE_DEFAULT) {
            $session = Factory::getSession();
            $session->set($this->_sessionNamespace, $result->getArrayCopy(), $this->_namespace);
        } elseif ($this->getType() === self::TYPE_COOKIE) {
            /** @var Cookie $cookie */
            $cookie = $this->hyper['input']->cookie;
            $expire = Filter::int($this->getCookieLifetime()) * 60;
            $cookie->set(
                md5($this->_sessionNamespace),
                base64_encode($result->write()),
                time() + $expire,
                $this->hyper['cms']->get('cookie_path', '/'),
                $this->hyper['cms']->get('cookie_domain', ''),
                $this->hyper['cms']->isHttpsForced(),
                true
            );
        } else {
            throw new \Exception('No find session type');
        }
    }

    /**
     * Setup cookie lifetime.
     *
     * @param   $lifetime
     *
     * @return  $this
     *
     * @since   2.0
     */
    public function setCookieLifetime($lifetime)
    {
        $this->_cookieLifetime = $lifetime;
        return $this;
    }

    /**
     * Get session namespace.
     *
     * @param   string $namespace
     *
     * @return  $this
     *
     * @since   2.0
     */
    public function setNamespace($namespace)
    {
        $this->_sessionNamespace = $namespace;
        return $this;
    }

    /**
     * Set session work type.
     *
     * @param   string  $type
     *
     * @return  $this
     *
     * @since   2.0
     */
    public function setType($type = self::TYPE_DEFAULT)
    {
        $this->_sessionType = $type;
        return $this;
    }
}
