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

use Joomla\CMS\Language\Text;
use \HyperPcViewProfile_Menu as View;
use Joomla\CMS\WebAsset\WebAssetManager;

/**
 * @var View $this
 */

/** @var WebAssetManager $wa */
$wa = $this->hyper['wa'];
$wa->registerAndUseScript(
    'view_profile_menu',
    'com_hyperpc/apps/dist/views/profile-menu.js',
    dependencies:['vue', 'core']
);

/** @todo подключить окно загрузки конфигурации */

Text::script('JLOGOUT');
Text::script('COM_HYPERPC_PROFILE_MENU_CART');
Text::script('COM_HYPERPC_PROFILE_MENU_COMPARE');
Text::script('COM_HYPERPC_PROFILE_MENU_CONFIGURATIONS');
Text::script('COM_HYPERPC_PROFILE_MENU_LOAD_CONFIGURATION');
Text::script('COM_HYPERPC_PROFILE_MENU_ORDERS');
Text::script('COM_HYPERPC_PROFILE_MENU_REVIEWS');

if (!$this->jsAppData->get('user.isAuthorized', false)) {
    Text::script('COM_HYPERPC_PROFILE_MENU_SIGN_IN');
    Text::script('COM_HYPERPC_PROFILE_MENU_SIGN_IN_OR_REGISTER');

    /** @todo подключить окно авторизации */
}

?>
<div class="uk-container uk-width-xlarge uk-margin uk-margin-top">
    <div id="profile-menu-app" data-props='<?= $this->jsAppData->toString('JSON', ['bitmask' => JSON_UNESCAPED_UNICODE]) ?>'>
        <div class="uk-flex uk-flex-center uk-flex-middle uk-height-large" data-uk-spinner="ratio: 2"></div>
    </div>
</div>
