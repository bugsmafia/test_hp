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
 * @author      Roman Evsyukov <roman_e@hyperpc.ru>
 * @author      Artem Vyshnevskiy
 */

defined('_JEXEC') or die('Restricted access');

$cols = $params->get('cols', HP_DEFAULT_ROW_COLS);
if ($cols === '__global__') {
    $cols = HP_DEFAULT_ROW_COLS;
}

$containerClass = $hp['helper']['uikit']->getProductsContainerClassByCols($cols);
?>

<?php if (count($products)) : ?>
    <div class="<?= $containerClass ?>">
        <?php if ($title = trim($params->get('title'))) : ?>
            <div class="uk-h2 uk-text-center">
                <?= $title ?>
            </div>
        <?php endif; ?>

        <?php if ($content_after_header = trim($params->get('content_after_header'))) : ?>
            <div class="uk-margin-medium-bottom">
                <?= $content_after_header ?>
            </div>
        <?php endif; ?>

        <?= $hp['helper']['render']->render('product/teaser/2024-grid-default', [
            'groups'    => $hp['helper']['productFolder']->getList(),
            'products'  => $products,
            'jsSupport' => false
        ], 'renderer', false);
        ?>
    </div>
<?php endif;
