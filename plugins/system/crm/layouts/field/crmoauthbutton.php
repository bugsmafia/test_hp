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
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;

/**
 * @var FileLayout $this
 * @var array $displayData
 */

/** @var JFormFieldCrmOauthButton $field */
$field = $displayData['field'];

$platform = $field->getPlatform();
?>

<?php if (empty($platform)) : ?>
    <div class="alert alert-info">
        <?= Text::_('PLG_SYSTEM_CRM_ALERT_OAUTH_EMPTY_PLATFORM') ?>
    </div>
    <?php return; ?>
<?php endif; ?>

<?php if ($field->checkAuthState()) : ?>
    <div class="alert alert-success">
        <?= Text::_('PLG_SYSTEM_CRM_ALERT_OAUTH_SUCCESS') ?>
    </div>
    <?php return; ?>
<?php endif; ?>

<?php // auth failed
$app = App::getInstance();

$clientId = $field->getClientId();
$clientSecret = $field->getClientSecret();

$buttonJsSourceUrl = $field->getButtonJsSourceUrl();

$buttonAttrs = [
    'class' => "{$platform}_oauth",
    'charset' => 'utf-8',
    'src' => $buttonJsSourceUrl,
    'data' => [
        'compact' => 'false',
        'color'   => 'default',
        'state'   => $field->getStateHash(),
        'mode'    => 'popup',
    ]
];

if (!empty($clientId) && !empty($clientSecret)) :
    $buttonAttrs['data']['title'] = Text::_('PLG_SYSTEM_CRM_OAUTH_BUTTON_SETUP_TITLE');
    $buttonAttrs['data']['client-id'] = $clientId;
    ?>
    <div class="alert alert-error">
        <?= Text::sprintf('PLG_SYSTEM_CRM_ALERT_OAUTH_CHECK_KEYS', $field->getOauthDirPath(), $field->getClientFilePath()) ?>
    </div>
    <p>
        <?= Text::_('PLG_SYSTEM_CRM_INTEGRATION_ID') ?>:<br>
        <input type="text" disabled="disabled" value="<?= $clientId ?>" />
    </p>
    <p>
        <?= Text::_('PLG_SYSTEM_CRM_INTEGRATION_SECRET_KEY') ?>:<br>
        <input type="text" disabled="disabled" value="<?= $clientSecret ?>" />
    </p>
<?php else :
    $siteUrl = rtrim(Uri::root(), '/');
    $logoUrl = $siteUrl . '/plugins/system/crm/assets/img/logo.png';
    $date = Factory::getDate()->format('M Y', true, false);

    $buttonAttrs['data']['title'] = Text::_('PLG_SYSTEM_CRM_OAUTH_BUTTON_INSTALL_TITLE');
    $buttonAttrs['data']['name'] = Text::_('PLG_SYSTEM_CRM_INTEGRATION_NAME');
    $buttonAttrs['data']['description'] = Text::sprintf('PLG_SYSTEM_CRM_INTEGRATION_DESCRIPTION', $siteUrl, $date);
    $buttonAttrs['data']['redirect_uri'] = $siteUrl;
    $buttonAttrs['data']['secrets_uri'] = $siteUrl . '/index.php?option=' . HP_OPTION . '%26task=amocrm.secrets';
    $buttonAttrs['data']['logo'] = $logoUrl;
    $buttonAttrs['data']['scopes'] = 'crm';
    ?>
<?php endif; ?>

<script <?= $app['helper']['html']->buildAttrs($buttonAttrs) ?>></script>
