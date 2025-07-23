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
use HYPERPC\ORM\Entity\User;
use Joomla\Registry\Registry;
use Joomla\CMS\Language\Text;

/**
 * @var     App         $hp
 * @var     object      $module
 * @var     Registry    $params
 * @var     bool        $isAuthorized
 * @var     string      $configuratorRoute
 * @var     bool        $isAjaxAuth
 * @var     User        $user
 * @var     bool        $isConfiguratorTmpl
 */

$configsHref  = Route::_('index.php?option=com_hyperpc&view=profile_configurations');
$ordersHref   = Route::_('index.php?option=com_hyperpc&view=profile_orders');
$reviewsHref  = Route::_('index.php?option=com_hyperpc&view=profile_reviews');

$compareUrlArgs = [];
if ($isConfiguratorTmpl) {
    $compareUrlArgs['tmpl'] = 'component';
}

$comparedItemsCount = $hp['helper']['compare']->countItems();

$compareAttrs = [
    'href' => $hp['route']->getCompareUrl($compareUrlArgs),
    'class' => 'uk-icon' . ($isConfiguratorTmpl ? ' jsLoadIframe' : ''),
    'target' => $isConfiguratorTmpl ? '_blank' : '',
    'title' => Text::_('MOD_HP_CART_GO_TO_COMPARE'),
    'aria-label' => Text::_('MOD_HP_CART_GO_TO_COMPARE'),
];

$compareAttrs = $hp['helper']['html']->buildAttrs($compareAttrs);

$isCompareHidden = $isConfiguratorTmpl && empty($comparedItemsCount);

// Cart items
$cartItems = $hp['helper']['cart']->getItemsShortList();
$cartItemsCount = count($cartItems);

?>
<li class="jsCartModuleCompare"<?= $isCompareHidden ? ' hidden' : '' ?>>
    <a <?= $compareAttrs ?>>
        <svg width="30" height="30" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd" clip-rule="evenodd" d="M3.87109 25.9717L3.87109 14.5135L12.227 14.5135L12.227 25.9717L3.87109 25.9717ZM5.27734 24.5654L10.8208 24.5654L10.8208 15.9197L5.27734 15.9197L5.27734 24.5654Z" fill="currentColor"/>
            <path fill-rule="evenodd" clip-rule="evenodd" d="M10.8203 25.9717L10.8203 9.7634L19.1763 9.7634L19.1763 25.9717L10.8203 25.9717ZM12.2266 24.5654L17.77 24.5654L17.77 11.1696L12.2266 11.1697L12.2266 24.5654Z" fill="currentColor"/>
            <path fill-rule="evenodd" clip-rule="evenodd" d="M17.7695 25.9717L17.7695 4.02739L26.1255 4.02739L26.1255 25.9717L17.7695 25.9717ZM19.1758 24.5654L24.7192 24.5654L24.7192 5.43364L19.1758 5.43364L19.1758 24.5654Z" fill="currentColor"/>
        </svg>
        <span class="jsCartModuleCompareBadge uk-badge"<?= $comparedItemsCount ? '' : ' hidden' ?>>
            <?= $comparedItemsCount ?>
        </span>
    </a>
</li>
<li class="jsCartModuleUser">
    <button class="uk-icon" type="button">
        <svg width="30" height="30" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
            <rect fill="none" stroke="#000" height="14" width="15" y="4.5" x="2.5" rx="2" ry="2"></rect>
            <path fill="none" stroke="#000" d="M6.5,5C6.5,3,8,1.5,10,1.5c2,0,3.5,1.5,3.5,3.5"></path>
        </svg>
        <span class="jsCartModuleUserBadge uk-badge"<?= $cartItemsCount ? '' : ' hidden' ?>>
            <?= $cartItemsCount ?>
        </span>
    </button>
    <div class="hp-mod-cart-drop uk-drop uk-dropdown uk-dropdown-large" data-uk-dropdown="mode: click; pos: bottom-right; offset: <?= $isConfiguratorTmpl ? 10 : 20 ?>">
        <button class="uk-drop-close" type="button" uk-close></button>

        <div class="jsNavbarUserCartItemsWrapper uk-margin-remove-top"<?= $cartItemsCount ? '' : ' hidden' ?>>
            <ul class="jsNavbarUserCartItems uk-list uk-list-divider uk-text-small uk-margin-small">
                <?php foreach ($cartItems as $item) : ?>
                    <li class="jsNavbarUserCartItem hp-mod-cart-item">
                        <?= !empty($item['url']) ? '<a href="' . $item['url'] . '" class="uk-link-reset" target="_blank">' : '' ?>
                            <span class="uk-flex uk-flex-middle">
                                <span class="uk-flex-none hp-mod-cart-item__image">
                                    <img src="<?= $item['image'] ?>" alt="" class="uk-responsive-height">
                                </span>
                                <span>
                                    <span class="uk-text-muted"><?= $item['category'] ?></span>
                                    <span class="uk-display-block"><?= $item['name'] ?></span>
                                </span>
                            </span>
                        <?= !empty($item['url']) ? '</a>' : '' ?>
                    </li>
                <?php endforeach; ?>
            </ul>

            <div class="uk-text-small uk-text-italic uk-text-muted uk-margin-small" hidden>
                <?= Text::sprintf('MOD_HP_CART_HIDDEN_ITEMS_COUNT', '<span class="jsHiddenItemsCount"></span>') ?>
            </div>

            <div class="uk-margin">
                <a href="<?= $hp['helper']['cart']->getUrl() ?>" class="uk-button uk-button-primary uk-button-small uk-width-1-1" style="line-height: 32px">
                    <?= Text::_('MOD_HP_CART_GO_TO_THE_CART') ?>
                </a>
            </div>

            <hr class="tm-margin-12">
        </div>

        <div class="jsNavbarUserDropItemAuthed"<?= !$isAuthorized ? ' hidden' : '' ?>>
            <?= Text::_('MOD_HP_CART_YOU_SIGN_IN_AS') ?>
            <div class="uk-text-emphasis">
                <a href="<?= $hp['route']->build(['option' => 'com_users', 'view' => 'profile'], true) ?>" class="uk-link-reset">
                    <span class="jsNavbarUserDropUsername uk-display-block uk-text-truncate">
                        <?= htmlspecialchars($user->get('username'), ENT_COMPAT, 'UTF-8') ?>
                    </span>
                </a>
            </div>

            <hr class="jsNavbarUserDropItemAuthed tm-margin-12"<?= !$isAuthorized ? ' hidden' : '' ?>>
        </div>

        <ul class="uk-nav uk-dropdown-nav">
            <li class="jsNavbarUserCartLink"<?= $cartItemsCount ? ' hidden' : '' ?>>
                <a href="<?= $hp['helper']['cart']->getUrl() ?>">
                    <?= Text::_('MOD_HP_CART_CART') ?>
                </a>
            </li>

            <li>
                <a href="#load-configuration-modal" data-uk-toggle role="button">
                    <?= Text::_('MOD_HP_CART_LOAD_CONFIGURATION') ?>
                </a>
            </li>

            <li class="uk-nav-divider"></li>

            <li>
                <a href="<?= $configsHref ?>">
                    <?= Text::_('MOD_HP_CART_MY_CONFIGURATIONS') ?>
                </a>
            </li>

            <li>
                <a href="<?= $ordersHref ?>">
                    <?= Text::_('MOD_HP_CART_MY_ORDERS') ?>
                </a>
            </li>

            <li>
                <a href="<?= $reviewsHref ?>">
                    <?= Text::_('MOD_HP_CART_MY_REVIEWS') ?>
                </a>
            </li>

            <li class="jsNavbarUserDropItemAuthed"<?= !$isAuthorized ? ' hidden' : '' ?>>
                <a href="<?= Route::_('index.php?option=com_users&view=profile&layout=edit'); ?>">
                    <?= Text::_('MOD_HP_CART_SETTINGS') ?>
                </a>
            </li>

            <li class="jsNavbarUserDropItemGuest"<?= $isAuthorized ? ' hidden' : '' ?>>
                <?php if ($isAjaxAuth) : ?>
                    <a href="#login-form-modal" class="jsLoginModalToggle" data-uk-toggle>
                        <?= Text::_('JLOGIN'); ?>
                    </a>
                <?php else : ?>
                    <a href="<?= Route::_('index.php?option=com_users&view=login') ?>">
                        <?= Text::_('JLOGIN'); ?>
                    </a>
                <?php endif; ?>
            </li>

            <li class="jsNavbarUserDropItemAuthed"<?= !$isAuthorized ? ' hidden' : '' ?>>
                <?php $logoutHref = Route::_('index.php?option=com_users&view=login&layout=logout&task=user.menulogout'); ?>
                <a href="<?= $logoutHref ?>" class="jsUserLogoutLink tm-text-medium uk-text-muted">
                    <?= Text::_('JLOGOUT'); ?>
                </a>
            </li>
        </ul>
    </div>
    <?= $hp['helper']['module']->partial('mod_hp_cart', 'load_configuration', ['configuratorRoute' => $configuratorRoute]) ?>

    <?php if ($isAjaxAuth && !$isAuthorized) : ?>
        <?= $hp['helper']['module']->partial('mod_hp_cart', 'auth') ?>
    <?php endif; ?>
</li>
<!-- <li> Cart icon
    <button class="uk-icon" type="button">
        <svg width="30" height="30" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd" clip-rule="evenodd" d="M8.32301 11.8982H3.38281V13.3045H4.04993L6.08334 25.6159H23.9204L25.9538 13.3045H26.6209V11.8982H21.6807L18.518 4.80078L17.2335 5.37317L20.1412 11.8982H9.86256L12.7703 5.37317L11.4858 4.80078L8.32301 11.8982ZM24.5285 13.3045H5.47523L7.27638 24.2096H22.7273L24.5285 13.3045ZM18.948 17.4651H10.6591V16.0589H18.948V17.4651ZM18.948 21.018H10.6591V19.6118H18.948V21.018Z" fill="white"/>
        </svg>
        <span class="uk-badge">1</span>
    </button>
</li> -->
