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

use Joomla\Uri\Uri;
use JBZoo\Data\Data;
use JBZoo\Utils\Url;
use JBZoo\Utils\Filter;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Menu\SiteMenu;
use Joomla\CMS\Menu\MenuItem;
use Joomla\CMS\Router\SiteRouter;
use Joomla\CMS\Application\CMSApplication;

/**
 * Class RouteHelper
 *
 * @package HYPERPC\Helper
 *
 * @since   2.0
 *
 * @deprecated Use HYPERPC\Router\Route
 */
class RouteHelper extends AppHelper
{

    /**
     * Get active menu item id
     *
     * @param   array $segments
     * @return  int|null
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getActiveItemId(array $segments)
    {
        $segments = new Data($segments);
        $items    = $this->getMenuItems();
        $view     = $segments->get('view');

        /** @var SiteMenu $siteMenu */
        $siteMenu = $this->hyper['app']->getMenu('site');
        $active   = $siteMenu->getActive();

            /** @var MenuItem $item */
        foreach ((array) $items as $item) {

            if ($item->query['view'] === $view && @Filter::int($active->query['id']) === $segments->get('id')) {
                return $item->id;
            }

            if ($item->query['view'] === $view && @Filter::int($item->query['id']) === $segments->get('id')){
                return $item->id;
            }
        }

        return null;
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
    public function getSiteSefUrl($url)
    {
        $site = CMSApplication::getInstance('site');
        /** @var SiteRouter $router */
        $router = $site::getRouter('site');

        /** @var Uri $uri */
        $uri  = $router->build($url);
        $path = trim(str_replace('/administrator', '', $uri->getPath()), '/');

        return \Joomla\CMS\Uri\Uri::root() . $path;
    }

    /**
     * Get cart url.
     *
     * @param   array $query
     * @return  string
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getCartRoute(array $query = [])
    {
        return $this->hyper['helper']['cart']->getUrl($query);
    }

    /**
     * Get cart url.
     *
     * @param   array $query
     * @return  string
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getCartRouteCredit(array $query = [])
    {
        return $this->hyper['helper']['cart']->getUrlCredit($query);
    }

    /**
     * Get menu items.
     *
     * @return  MenuItem
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getMenuItems()
    {
        return $this->hyper['app']->getMenu('site')->getItems('component', HP_OPTION);
    }

    /**
     * Create Joomla! route url.
     *
     * @param   array $args
     * @param   bool $xhtml
     * @param   null $ssl
     * @return  string
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function url(array $args = [], $xhtml = false, $ssl = null)
    {
        $segments = array_replace(['option' => HP_OPTION], $args);

        if (array_key_exists('view', $segments) && !array_key_exists('Itemid', $segments)) {
            $itemId = $this->getActiveItemId($segments);
            $segments['Itemid'] = $itemId;
        }

        $query = Url::build($segments);
        return Route::_('index.php?' . $query, $xhtml, $ssl);
    }
}
