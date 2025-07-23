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
 * @author      Artem Vyshnevskiy
 *
 * @var         \HYPERPC\Helper\RenderHelper $this
 */

use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;

$plugins = $this->hyper['helper']['auth']->getLoginAuthTypes();
?>

<?php if (count($plugins)) :
    $this->hyper['doc']->addScript(Uri::root() . 'modules/mod_slogin/media/slogin.min.js?v=1');
    ?>
    <div class="uk-text-center uk-margin-small uk-text-muted">
        <?= Text::_('COM_HYPERPC_LOGIN_SOCIAL') ?>
    </div>
    <div id="slogin-buttons" class="slogin-buttons slogin-default uk-grid uk-grid-small uk-flex-center">
        <?php
        foreach ($plugins as $link):
            $icon = '';

            switch ($link['plugin_name']) {
                case 'vkontakte':
                    $icon = 'hp-vk';
                    break;
                default:
                    $icon = $link['plugin_name'];
                    break;
            }

            $linkAttrs = [
                'href'       => Route::_($link['link']),
                'class'      => $link['class'] . ' tm-icon-button tm-icon-button-' . $link['plugin_name'],
                'rel'        => 'nofollow',
                'title'      => !empty($link['plugin_title']) ? Text::_($link['plugin_title']) : '',
                'uk-icon'    => $icon
            ];

            $linkParams = '';
            if (isset($link['params'])) {
                foreach ($link['params'] as $k => $v) {
                    $linkAttrs[$k] = $v;
                }
            }

            $title = (!empty($link['plugin_title'])) ? ' title="' . Text::_($link['plugin_title']) . '"' : '';
            ?>
            <span>
                <a <?= $this->hyper['helper']['html']->buildAttrs($linkAttrs) ?>>
                    <span class="text-socbtn"></span>
                </a>
            </span>
        <?php endforeach; ?>
    </div>
    <div class="uk-grid uk-grid-small uk-flex-nowrap uk-flex-middle">
        <div class="uk-width-expand">
            <hr class="uk-margin-remove">
        </div>
        <div class="uk-flex-nowrap">
            <span class="tm-text-medium"><?= Text::_('COM_HYPERPC_OR') ?></span>
        </div>
        <div class="uk-width-expand">
            <hr class="uk-margin-remove">
        </div>
    </div>
    <div class="uk-text-center uk-margin-small uk-text-muted">
        <?= Text::_('COM_HYPERPC_LOGIN_ACCOUNT') ?>
    </div>
<?php endif;
