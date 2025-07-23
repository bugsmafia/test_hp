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

defined('_JEXEC') or die('Restricted access');

use HYPERPC\App;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;
use HYPERPC\Helper\AssetsHelper;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Application\SiteApplication;

/**
 * @var SiteApplication $app
 * @var Registry $params
 */

$hp = App::getInstance();

$user = $hp['user'];
$isAjaxAuth = (bool) $params->get('ajax_auth', true);
$isAuthorized = !$user->get('guest');

$configuratorMenuItemId = $params->get('configurator_menu_item', 0);
if (!empty($configuratorMenuItemId)) {
    $configuratorMenuItem = $app->getMenu()->getItem($configuratorMenuItemId);
    $configuratorQuery    = $configuratorMenuItem->link;
}

$configuratorRoute = !empty($configuratorQuery) ? Route::_($configuratorQuery, false) : '/configurator';

$wa = $app->getDocument()->getWebAssetManager();
$wa
    ->registerAndUseScript('mod_hp_cart_module', 'modules/mod_hp_cart/assets/js/module.js', dependencies:['jquery-factory'])
    ->registerAndUseScript('mod_hp_cart_cart', 'modules/mod_hp_cart/assets/js/cart.js', dependencies:['jquery-factory'])
    ->registerAndUseScript('mod_hp_cart_compare', 'modules/mod_hp_cart/assets/js/compare.js', dependencies:['jquery-factory'])
    ->registerAndUseScript('mod_hp_cart_load_configuration', 'modules/mod_hp_cart/assets/js/load-configuration.js', dependencies:['jquery-factory']);

$lang = $app->getLanguage()->getTag();

/** @var AssetsHelper $assetsHelper */
$assetsHelper = $hp['helper']['assets'];

$assetsHelper->widget('.jsCartModuleUser', 'HyperPC.CartModule', []);
$assetsHelper->widget('.jsCartModuleUser', 'HyperPC.CartModuleCart', [
    'lang' => $lang,
    'langItemForms' => Text::_('MOD_HP_CART_ITEM_FORMS')
]);
$assetsHelper->widget('.jsCartModuleCompare', 'HyperPC.CartModuleCompare', []);
$assetsHelper->widget(".jsCartModuleUser [action*='task=configurator.find_configuration']", 'HyperPC.CartModuleLoadConfiguration', [
    'ajaxErrorMessage' => Text::_('COM_HYPERPC_AJAX_ERROR_TRY_AGAIN')
]);

if ($isAjaxAuth && !$isAuthorized) {
    $wa->registerAndUseScript('mod_hp_cart_auth', 'modules/mod_hp_cart/assets/js/auth.js', dependencies:['jquery-factory']);

    $assetsHelper->widget('#login-form-modal', 'HyperPC.CartModuleAuth', [
        'lang' => $lang,
        'profileUrl' => $hp['route']->getUserProfile()
    ]);
}

$isConfiguratorTmpl = $app->getInput()->get('tmpl') === 'configurator';

/** @noinspection PhpIncludeInspection */
require ModuleHelper::getLayoutPath('mod_hp_cart', $params->get('layout', 'default'));
