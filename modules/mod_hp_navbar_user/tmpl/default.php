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

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Language\Text;
use Joomla\Input\Input;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use HYPERPC\App;
use HYPERPC\Module\NavbarUser\Site\Helper\NavbarUserHelper;

/**
 * @var \stdClass $module
 * @var SiteApplication $app
 * @var Input $input
 * @var Registry $params
 * @var string $template
 */

$hp = App::getInstance();

$wa = $app->getDocument()->getWebAssetManager();
$wa->registerAndUseScript(
    'mod_hp_navbar_user',
    'com_hyperpc/apps/dist/modules/navbar-user.js',
    attributes:['defer' => true],
    dependencies:['vue', 'core']
);

/** @var NavbarUserHelper $helper */
$helper = $app->bootModule('mod_hp_navbar_user', 'site')->getHelper('NavbarUserHelper');

$cartData = $helper->getCartData();

$compareAttrs = $helper->getCompareAttrs();
$compareAttrs['aria-label'] = Text::_('MOD_HP_NAVBAR_USER_GO_TO_COMPARE');
$compareAttrs['class'] = 'uk-icon uk-position-relative';
if (key_exists('href', $compareAttrs) && strpos($compareAttrs['href'], 'tmpl=component') !== false) {
    $compareAttrs['class'] .= ' jsLoadIframe';
}

$compareCount = $helper->getCompareCount();

$userMenuData = $helper->getUserData();

Text::script('MOD_HP_NAVBAR_USER_CART');
Text::script('MOD_HP_NAVBAR_USER_CART_GO_TO_CART');
Text::script('MOD_HP_NAVBAR_USER_CART_IS_EMPTY');
Text::script('MOD_HP_NAVBAR_USER_CART_MORE_ITEMS');
Text::script('MOD_HP_NAVBAR_USER_CREATE_NEW_CONFIGURATION');
Text::script('MOD_HP_NAVBAR_USER_CART_SPECIFICATION_NUMBER');
Text::script('MOD_HP_NAVBAR_USER_LOAD');
Text::script('MOD_HP_NAVBAR_USER_LOAD_CONFIGURATION');
Text::script('MOD_HP_NAVBAR_USER_LOAD_CONFIGURATION_DESCRIPTION');
Text::script('MOD_HP_NAVBAR_USER_LOAD_CONFIGURATION_INPUT_PLACEHOLDER');
Text::script('MOD_HP_NAVBAR_USER_LOAD_CONFIGURATION_NUMBER');
Text::script('MOD_HP_NAVBAR_USER_LOAD_CONFIGURATION_LENGTH_ERROR');
Text::script('MOD_HP_NAVBAR_USER_MENU_GO_TO_PROFILE');
Text::script('MOD_HP_NAVBAR_USER_MENU_OR');
Text::script('MOD_HP_NAVBAR_USER_MENU_PROFILE');
Text::script('MOD_HP_NAVBAR_USER_MENU_REGISTER');
Text::script('MOD_HP_NAVBAR_USER_MENU_SIGN_IN');
Text::script('MOD_HP_NAVBAR_USER_MENU_CONFIGURATIONS');
Text::script('MOD_HP_NAVBAR_USER_MENU_ORDERS');
Text::script('MOD_HP_NAVBAR_USER_MENU_REVIEWS');
Text::script('JLOGOUT');

?>

<li class="uk-visible@s">
    <a <?= ArrayHelper::toString($compareAttrs) ?>>
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
            <path fill-rule="evenodd" clip-rule="evenodd" d="M0.8 0C1.24183 0 1.6 0.358172 1.6 0.8V12C1.6 13.3255 2.67452 14.4 4 14.4H15.2C15.6418 14.4 16 14.7582 16 15.2C16 15.6418 15.6418 16 15.2 16H4C1.79086 16 0 14.2091 0 12V0.8C0 0.358172 0.358172 0 0.8 0Z" fill="#F5F5F5"/>
            <path fill-rule="evenodd" clip-rule="evenodd" d="M4.80781 7.19531C5.24964 7.19531 5.60781 7.55348 5.60781 7.99531L5.60781 11.1953C5.60781 11.6371 5.24964 11.9953 4.80781 11.9953C4.36598 11.9953 4.00781 11.6371 4.00781 11.1953L4.00781 7.99531C4.00781 7.55348 4.36598 7.19531 4.80781 7.19531Z" fill="#F5F5F5"/>
            <path fill-rule="evenodd" clip-rule="evenodd" d="M8.8 1.58594C9.24183 1.58594 9.6 1.94411 9.6 2.38594L9.6 11.1859C9.6 11.6278 9.24183 11.9859 8.8 11.9859C8.35817 11.9859 8 11.6278 8 11.1859L8 2.38594C8 1.94411 8.35817 1.58594 8.8 1.58594Z" fill="#F5F5F5"/>
            <path fill-rule="evenodd" clip-rule="evenodd" d="M12.8078 4.00781C13.2496 4.00781 13.6078 4.36598 13.6078 4.80781L13.6078 11.2078C13.6078 11.6496 13.2496 12.0078 12.8078 12.0078C12.366 12.0078 12.0078 11.6496 12.0078 11.2078L12.0078 4.80781C12.0078 4.36598 12.366 4.00781 12.8078 4.00781Z" fill="#F5F5F5"/>
        </svg>
        <span class="jsCompareBadge" data-props='{"count": <?= $compareCount ?>}' hidden></span>
    </a>
</li>

<li class="uk-visible@s">
    <button class="uk-icon uk-position-relative">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="16" viewBox="0 0 20 16" fill="none">
            <path d="M7.03401 11.9819H17.0555C17.4602 11.9819 17.8132 11.6926 17.8132 11.2747C17.8132 10.8569 17.4602 10.5676 17.0555 10.5676H7.22342C6.80155 10.5676 6.54326 10.2863 6.483 9.87644L6.35385 9.04068H17.1244C18.4331 9.04068 19.1218 8.29332 19.3198 7.07986L19.9656 3.03767C19.9828 2.92516 20 2.78855 20 2.70819C20 2.24209 19.647 1.92868 19.0616 1.92868H5.24322L5.10547 1.0447C5.00215 0.353591 4.70943 0 3.75377 0H0.792079C0.370211 0 0 0.345555 0 0.755399C0 1.15721 0.370211 1.50276 0.792079 1.50276H3.50409L4.84718 10.0372C5.03659 11.2426 5.72536 11.9819 7.03401 11.9819ZM18.235 3.34304L17.684 6.93521C17.6238 7.35309 17.3827 7.61828 16.9522 7.61828L6.13 7.62632L5.46707 3.34304H18.235ZM7.73138 16C8.56651 16 9.22084 15.3812 9.22084 14.6097C9.22084 13.8302 8.56651 13.2115 7.73138 13.2115C6.90486 13.2115 6.24193 13.8302 6.24193 14.6097C6.24193 15.3812 6.90486 16 7.73138 16ZM15.6952 16C16.5303 16 17.1933 15.3812 17.1933 14.6097C17.1933 13.8302 16.5303 13.2115 15.6952 13.2115C14.8687 13.2115 14.1972 13.8302 14.1972 14.6097C14.1972 15.3812 14.8687 16 15.6952 16Z" fill="#F5F5F5"></path>
        </svg>
        <span class="jsCartBadge" data-props='{"count": <?= count($cartData['cartItems']) ?>}' hidden></span>
    </button>
    <div class="uk-drop uk-navbar-dropdown jsMainnav2024Drop tm-mainnav-dropdown"
         data-uk-drop="mode: click; animation: none; delay-show: 150; delay-hide: 250; cls-drop: uk-navbar-dropdown; boundary: !.uk-navbar; stretch: x; flip: false; target-y: !.uk-navbar; dropbar: true;"
    >
        <div id="mainnav-cart-drop" data-props='<?= $cartData->toString('JSON', ['bitmask' => JSON_UNESCAPED_UNICODE]) ?>'></div>
    </div>
</li>

<?php if ($params->get('chat_in_tabbar', 0)) : ?>
    <li class="uk-hidden@s">
        <a href="<?= $helper->getProfileMenuRoute() ?>" class="uk-icon uk-position-relative">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 16 16" fill="none">
                <path d="M8.00455 8.03852C10.2058 8.03852 11.9886 6.20653 11.9886 3.96362C11.9886 1.75495 10.2058 0 8.00455 0C5.81239 0 4.01137 1.78063 4.02047 3.98074C4.02956 6.21509 5.8033 8.03852 8.00455 8.03852ZM8.00455 6.5404C6.74929 6.5404 5.68505 5.41894 5.68505 3.98074C5.67595 2.57678 6.74019 1.49813 8.00455 1.49813C9.278 1.49813 10.324 2.55966 10.324 3.96362C10.324 5.40182 9.2689 6.5404 8.00455 6.5404ZM2.3286 16H13.6714C15.245 16 16 15.5292 16 14.519C16 12.1648 12.88 9.03157 8.00455 9.03157C3.12905 9.03157 0 12.1648 0 14.519C0 15.5292 0.754974 16 2.3286 16ZM2.04662 14.5019C1.82831 14.5019 1.74645 14.4334 1.74645 14.2793C1.74645 12.9695 3.99318 10.5297 8.00455 10.5297C12.0068 10.5297 14.2536 12.9695 14.2536 14.2793C14.2536 14.4334 14.1717 14.5019 13.9534 14.5019H2.04662Z" fill="#F5F5F5"/>
            </svg>
            <span class="jsCompareBadge" data-props='{"count": <?= $compareCount ?>}' hidden></span>
        </a>
    </li>
<?php endif; ?>

<li class="uk-visible@s">
    <button type="button" class="uk-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
            <path d="M8.00455 8.03852C10.2058 8.03852 11.9886 6.20653 11.9886 3.96362C11.9886 1.75495 10.2058 0 8.00455 0C5.81239 0 4.01137 1.78063 4.02047 3.98074C4.02956 6.21509 5.8033 8.03852 8.00455 8.03852ZM8.00455 6.5404C6.74929 6.5404 5.68505 5.41894 5.68505 3.98074C5.67595 2.57678 6.74019 1.49813 8.00455 1.49813C9.278 1.49813 10.324 2.55966 10.324 3.96362C10.324 5.40182 9.2689 6.5404 8.00455 6.5404ZM2.3286 16H13.6714C15.245 16 16 15.5292 16 14.519C16 12.1648 12.88 9.03157 8.00455 9.03157C3.12905 9.03157 0 12.1648 0 14.519C0 15.5292 0.754974 16 2.3286 16ZM2.04662 14.5019C1.82831 14.5019 1.74645 14.4334 1.74645 14.2793C1.74645 12.9695 3.99318 10.5297 8.00455 10.5297C12.0068 10.5297 14.2536 12.9695 14.2536 14.2793C14.2536 14.4334 14.1717 14.5019 13.9534 14.5019H2.04662Z" fill="#F5F5F5"/>
        </svg>
    </button>
    <div class="uk-drop uk-navbar-dropdown jsMainnav2024Drop tm-mainnav-dropdown"
         data-uk-drop="mode: click; animation: none; delay-show: 150; delay-hide: 250; cls-drop: uk-navbar-dropdown; boundary: !.uk-navbar; stretch: x; flip: false; target-y: !.uk-navbar; dropbar: true;"
    >
        <div id="mainnav-user-drop" data-props='<?= $userMenuData->toString('JSON', ['bitmask' => JSON_UNESCAPED_UNICODE]) ?>'></div>
    </div>

    <div id="load-configuration-modal" class="uk-flex-top uk-modal" data-uk-modal="bg-close: false">
        <div class="uk-modal-dialog uk-modal-body uk-margin-auto-vertical uk-margin-remove-bottom uk-margin-auto-bottom@s">
            <button class="uk-modal-close-default uk-close-large" type="button" data-uk-close></button>
            <div id="load-configuration-app" data-props='{"configuratorLink": "<?= $helper->getConfiguratorRoute($params, $app) ?>"}'></div>
        </div>
    </div>

    <?php if (!$userMenuData->get('isAuthorized', false)) : /** @todo use Vue.js component */
        $wa->registerAndUseScript('mod_hp_cart_auth', 'modules/mod_hp_cart/assets/js/auth.js', dependencies:['jquery-factory']);

        $hp['helper']['assets']->widget('#login-form-modal', 'HyperPC.CartModuleAuth', [
            'lang' => $app->getLanguage()->getTag(),
            'profileUrl' => $hp['route']->getUserProfile()
        ]);
        ?>
        <?= $hp['helper']['module']->partial('mod_hp_cart', 'auth') ?>
    <?php endif; ?>
</li>
