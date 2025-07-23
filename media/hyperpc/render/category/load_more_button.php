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
 *
 * @var         RenderHelper $this
 * @var         array $ajaxLoadArgs
 */

use Joomla\CMS\Language\Text;
use HYPERPC\Helper\RenderHelper;

defined('_JEXEC') or die('Restricted access');

$layout = isset($ajaxLoadArgs['layout']) ? $ajaxLoadArgs['layout'] : 'default';
?>

<?php if (!empty($ajaxLoadArgs)) :
    $attrs = [
        'class' => 'jsLoadMoreProducts uk-button',
        'type'  => 'button',
        'data'  => $ajaxLoadArgs
    ];
    ?>
    <?php if ($layout === 'table') :
        $attrs['class'] .= ' uk-button-link uk-text-small uk-link-muted uk-text-uppercase uk-width-1-1 uk-padding-small';
        ?>
        <tr class="jsLoadMoreProductsWrapper uk-text-center">
            <td colspan="99" class="uk-padding-remove">
                <button <?= $this->hyper['helper']['html']->buildAttrs($attrs) ?>>
                    <?= Text::_('COM_HYPERPC_SHOW_MORE') ?>
                </button>
            </td>
        </tr>
    <?php else :
        $attrs['class'] .= ' uk-button-text';
        ?>
        <div class="jsLoadMoreProductsWrapper uk-text-center uk-width-1-1 uk-margin-remove-top">
            <hr class="uk-margin-top uk-margin-small-bottom">
            <button <?= $this->hyper['helper']['html']->buildAttrs($attrs) ?>>
                <?= Text::_('COM_HYPERPC_SHOW_MORE') ?>
            </button>
        </div>
    <?php endif; ?>
<?php endif;
