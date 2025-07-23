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
 * @var         HyperPcViewProducts $this
 */

defined('_JEXEC') or die('Restricted access');

$countProducts = count($this->products);
?>

<div id="hp-products-view">
    <?php if ($countProducts) : ?>
        <table>
            <tbody class="jsProductsGrid">
                <?= $this->hyper['helper']['render']->render('product/teaser/' . $this->layout, [
                    'products'   => $this->products,
                    'groups'     => $this->groups,
                    'options'    => $this->options,
                    'showFps'    => $this->showFps,
                    'teaserType' => 'default',
                ], 'renderer', false);
                ?>
            </tbody>
            <tfoot>
                <?php if (isset($this->ajaxLoadArgs)) : ?>
                    <?= $this->hyper['helper']['render']->render('category/load_more_button', [
                        'ajaxLoadArgs' => $this->ajaxLoadArgs
                    ], 'renderer', false);
                    ?>
                <?php endif; ?>
            </tfoot>
        </table>
    <?php endif; ?>
</div>
