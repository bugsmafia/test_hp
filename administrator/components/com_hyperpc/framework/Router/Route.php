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

namespace HYPERPC\Router;

use JBZoo\Utils\Url;
use Cake\Utility\Hash;
use HYPERPC\Container;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\SiteRouter;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Router\Route as BaseRoute;

/**
 * Class Route
 *
 * @package     HYPERPC\Router
 *
 * @since       2.0
 */
class Route extends Container
{

    /**
     * Build compare route url.
     *
     * @param   array   $args       Array arguments for build url.
     * @param   bool    $isFull     Add host name in to the url.
     * @param   bool    $xhtml      Replace & by &amp; for XML compliance.
     * @param   int     $ssl        Secure state for the resolved URI.
     *                                  0: (default) No change, use the protocol currently used in the request
     *                                  1: Make URI secure using global secure site URI.
     *                                  2: Make URI unsecure using the global unsecure site URI.
     * @return  string
     *
     * @since   2.0
     */
    public function getCompareUrl(array $args = [], $isFull = false, $xhtml = false, $ssl = null)
    {
        $args = Hash::merge($args, ['view' => 'compare']);
        return $this->build($args, $isFull, $xhtml, $ssl);
    }

    /**
     * Get joomla auth url.
     *
     * @param   array   $args       Array arguments for build url.
     * @param   bool    $isFull     Add host name in to the url.
     * @param   bool    $xhtml      Replace & by &amp; for XML compliance.
     * @param   int     $ssl        Secure state for the resolved URI.
     *                                  0: (default) No change, use the protocol currently used in the request
     *                                  1: Make URI secure using global secure site URI.
     *                                  2: Make URI unsecure using the global unsecure site URI.
     * @return  string
     *
     * @since   2.0
     */
    public function getAuthUrl($args = [], $isFull = true, $xhtml = false, $ssl = null)
    {
        $args = Hash::merge($args, [
            'option' => 'com_users',
            'view'   => 'login'
        ]);

        return $this->hyper['route']->build($args, $isFull, $xhtml, $ssl);
    }

    /**
     * Get joomla user profile account url.
     *
     * @param   array   $args       Array arguments for build url.
     * @param   bool    $isFull     Add host name in to the url.
     * @param   bool    $xhtml      Replace & by &amp; for XML compliance.
     * @param   int     $ssl        Secure state for the resolved URI.
     *                                  0: (default) No change, use the protocol currently used in the request
     *                                  1: Make URI secure using global secure site URI.
     *                                  2: Make URI unsecure using the global unsecure site URI.
     * @return  string
     *
     * @since   2.0
     */
    public function getUserProfile($args = [], $isFull = true, $xhtml = false, $ssl = null)
    {
        $args = Hash::merge($args, [
            'option' => 'com_users',
            'view'   => 'profile'
        ]);

        return $this->hyper['route']->build($args, $isFull, $xhtml, $ssl);
    }

    /**
     * Get site sef url in admin end.
     *
     * @param   string $url
     *
     * @return  string
     *
     * @since   2.0
     */
    public function buildSite($url)
    {
        $site = CMSApplication::getInstance('site');
        /** @var SiteRouter $router */
        $router = $site::getRouter('site');

        /** @var Uri $uri */
        $uri  = $router->build($url);
        $path = trim(str_replace('/administrator', '', $uri->getPath()), '/');

        return Uri::root() . $path;
    }

    /**
     * Build route url.
     *
     * @param   array   $args       Array arguments for build url.
     * @param   bool    $isFull     Add host name in to the url.
     * @param   bool    $xhtml      Replace & by &amp; for XML compliance.
     * @param   int     $ssl        Secure state for the resolved URI.
     *                                  0: (default) No change, use the protocol currently used in the request
     *                                  1: Make URI secure using global secure site URI.
     *                                  2: Make URI unsecure using the global unsecure site URI.
     * @return  string
     *
     * @since   2.0
     */
    public function build(array $args = [], $isFull = false, $xhtml = false, $ssl = null)
    {
        foreach ($args as $key => $value) {
            if (preg_match('/^%[a-zA-Z0-9]/', $value)) {
                $valueKey   = str_replace('%', '', $value);
                $args[$key] = $this->hyper['input']->get($valueKey, 0);
            }
        }

        $segments = array_replace(['option' => HP_OPTION], $args);
        $query    = Url::build($segments);
        $route    = BaseRoute::_('index.php?' . $query, $xhtml, $ssl);

        return ($isFull === true) ? Uri::root() . trim($route, '/') : $route;
    }

    /**
     * Root address.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function root()
    {
        return rtrim(Uri::root(), '/');
    }
}
