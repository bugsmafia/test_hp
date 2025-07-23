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
 * @author      Artem Vyshnevskiy
 */

namespace HYPERPC\Module\NavbarUser\Site\Helper;

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;
use HYPERPC\App;
use HYPERPC\Helper\CartHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Helper for mod_hp_navbar_user
 */
class NavbarUserHelper
{
    /**
     * Get cart data for render items.
     *
     * @return  Registry
     */
    public function getCartData(): Registry
    {
        $hp = App::getInstance();

        /** @var CartHelper $cartHelper */
        $cartHelper = $hp['helper']['cart'];

        $cartItems = [];
        $itemsList = $cartHelper->getItemsShortList();
        foreach ($itemsList as $item) {
            $cartItems[] = array_intersect_key($item, array_flip(['category', 'name', 'image', 'specification']));
        }

        $result = new Registry([
            'cartUrl' => $cartHelper->getUrl(),
            'cartItems' => $cartItems
        ]);

        return $result;
    }

    /**
     * Get attrs for compare link.
     *
     * @return  array
     */
    public function getCompareAttrs(): array
    {
        $result = [];

        $hp = App::getInstance();

        $isConfiguratorTmpl = $hp['input']->get('tmpl') === 'configurator';
        $compareUrlArgs = [];
        if ($isConfiguratorTmpl) {
            $compareUrlArgs['tmpl'] = 'component';
            $result['target'] = '_blank';
        }

        $result['href'] = $hp['route']->getCompareUrl($compareUrlArgs);

        return $result;
    }

    /**
     * Get count of compared items.
     *
     * @return int
     */
    public function getCompareCount(): int
    {
        $hp = App::getInstance();

        return $hp['helper']['compare']->countItems();
    }

    /**
     * Get link to configurator.
     * 
     * @param   Registry         $params  Object holding the models parameters
     * @param   SiteApplication  $app     The app
     *
     * @return  string
     */
    public function getConfiguratorRoute(Registry $params, SiteApplication $app): string
    {
        $configuratorMenuItemId = $params->get('configurator_menu_item', 0);
        if (!empty($configuratorMenuItemId)) {
            $configuratorMenuItem = $app->getMenu()->getItem($configuratorMenuItemId);
            $configuratorQuery = $configuratorMenuItem->link;
        }

        return !empty($configuratorQuery) ? Route::_($configuratorQuery, false) : '/configurator';
    }

    /**
     * Get homepage url
     *
     * @param   SiteApplication $app
     *
     * @return  string
     */
    public function getHomeRoute(SiteApplication $app): string
    {
        $query = $app->getMenu()->getDefault()->link ?? '/';
        $home = Route::_($query);

        return $home === '/' ? $home : rtrim($home, '/');
    }

    /**
     * Get link to catalog.
     * 
     * @param   Registry         $params  Object holding the models parameters
     * @param   SiteApplication  $app     The app
     *
     * @return  string
     */
    public function getCatalogRoute(Registry $params, SiteApplication $app): string
    {
        $catalogMenuItemId = $params->get('catalog_menu_item', 0);
        if (!empty($catalogMenuItemId)) {
            $catalogMenuItem = $app->getMenu()->getItem($catalogMenuItemId);
            $catalogQuery = $catalogMenuItem->link;
        }

        return !empty($catalogQuery) ? Route::_($catalogQuery, false) : '/catalog';
    }

    /**
     * Get link to profile menu
     *
     * @return string
     */
    public function getProfileMenuRoute(): string
    {
        return Route::_('index.php?option=com_hyperpc&view=profile_menu');
    }

    /**
     * Get user data for render.
     *
     * @return  Registry
     */
    public function getUserData(): Registry
    {
        $hp = App::getInstance();

        $user = $hp['user'];

        $isAutorized = (bool) $user->id;

        $result = [
            'isAuthorized' => $isAutorized,
            'profileLink' => Route::_('index.php?option=com_users&view=profile'),
            'configsLink' => Route::_('index.php?option=com_hyperpc&view=profile_configurations'),
            'ordersLink' => Route::_('index.php?option=com_hyperpc&view=profile_orders'),
            'reviewsLink' => Route::_('index.php?option=com_hyperpc&view=profile_reviews'),
            'logoutLink' => Route::_('index.php?option=com_users&view=login&layout=logout&task=user.menulogout')
        ];

        if ($isAutorized) {
            $result['name'] = (string) $user->name;
            $result['avatar'] = $user->getAvatar();
        }

        return new Registry($result);
    }

    /**
     * Is the path is the current path
     *
     * @param   string $path
     *
     * @return  bool
     */
    public function isActivePath(string $path): bool
    {
        $currentPath = Uri::getInstance()->toString(['path']);
        if ($path === $currentPath) {
            return true;
        }

        return false;
    }
}
