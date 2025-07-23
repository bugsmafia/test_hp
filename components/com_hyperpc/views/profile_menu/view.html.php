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

use Joomla\CMS\Router\Route;
use Joomla\Registry\Registry;
use HYPERPC\Joomla\View\ViewLegacy;
use HYPERPC\ORM\Entity\User;

defined('_JEXEC') or die('Restricted access');

class HyperPcViewProfile_Menu extends ViewLegacy
{
    public Registry $jsAppData;

    /**
     * Display action.
     *
     * @param   string $tpl
     *
     * @return  bool|mixed|void
     *
     * @throws  \Exception
     */
    public function display($tpl = null)
    {
        $this->jsAppData = $this->getJsAppData();

        return parent::display($tpl);
    }

    /**
     * Get data for render js app
     *
     * @return Registry
     */
    protected function getJsAppData(): Registry
    {
        /** @var User $user */
        $user = $this->hyper['user'];

        $isAuthorized = (bool) $user->id;

        $data = [
            'user' => ['isAuthorized' => $isAuthorized],
            'cartCount' => count($this->hyper['helper']['cart']->getItemsShortList()),
            'compareCount' => $this->hyper['helper']['compare']->countItems(),
            'routes' => [
                'profile' => Route::_('index.php?option=com_users&view=profile'),
                'compare' => Route::_('index.php?option=com_hyperpc&view=compare'),
                'cart' => Route::_('index.php?option=com_hyperpc&view=cart'),
                'configurations' => Route::_('index.php?option=com_hyperpc&view=profile_configurations'),
                'orders' => Route::_('index.php?option=com_hyperpc&view=profile_orders'),
                'reviews' => Route::_('index.php?option=com_hyperpc&view=profile_reviews'),
                'logout' => Route::_('index.php?option=com_users&view=login&layout=logout&task=user.menulogout')
            ]
        ];

        if ($isAuthorized) {
            $data['user'] = [
                'isAuthorized' => $isAuthorized,
                'name' => (string) $user->name,
                'email' => $user->email,
                'phone' => $user->getPhone(),
                'avatar' => $user->getAvatar()
            ];
        }

        return new Registry($data);
    }
}
