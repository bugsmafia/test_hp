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
 * @author      Roman Evsyukov <roman_e@hyperpc.ru>
 * @author      Artem Vyshnevskiy
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;

/**
 * @var HyperPcViewCompare_Products $view
 */

?>
<div class="hp-compare">
    <div class="uk-container uk-container-large">
        <h1 class="uk-text-center uk-margin-top uk-margin-remove-bottom">
            <?= Text::_('COM_HYPERPC_COMPARE_PRODUCT_PAGE_TITLE') ?>
        </h1>
    </div>

    <div class="jsCompareWrapper hp-compare-wrapper uk-overflow-hidden">
        <?= $view->renderLayout('elements/items', [
            'items' => $view->items
        ], false); ?>
    </div>
</div>

<?php
echo $view->renderLayout('elements/offcanvas', [
    'tree' => $view->categoriesTree
], false);
