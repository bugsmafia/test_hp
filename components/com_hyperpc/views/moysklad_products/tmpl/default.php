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
 * @var         \HyperPcViewMoysklad_Products $this
 */

defined('_JEXEC') or die('Restricted access');

$countProducts = count($this->products);
$hasLoadArgs = isset($this->ajaxLoadArgs);

?>
<div id="hp-products-view">
    <?php if ($countProducts) : ?>
        <div class="uk-section-small">
            <div class="uk-container uk-container-large">
                <?php if ($this->layout === '2024-grid-default') : ?>
                <?= $this->hyper['helper']['render']->render('product/teaser/2024-grid-default', [
                    'products'   => $this->products,
                    'groups'     => $this->groups,
                    'showFps'    => $this->showFps,
                    'game'       => $this->game,
                    'jsSupport'  => true,
                    'instock'    => $this->instock
                ], 'renderer', false);
                ?>
                <?php else : ?>
                    <div class="jsProductsGrid tm-products-grid uk-grid-match"
                        data-uk-height-match="target: .tm-product-teaser__description">
                        <?= $this->hyper['helper']['render']->render('product/teaser/' . $this->layout, [
                            'products'   => $this->products,
                            'groups'     => $this->groups,
                            'options'    => $this->options,
                            'teaserType' => 'default',
                            'showFps'    => $this->showFps,
                            'game'       => $this->game
                            ], 'renderer', false);
                        ?>
                    </div>
                <?php endif; ?>
                <?php if ($hasLoadArgs) : ?>
                    <?= $this->hyper['helper']['render']->render('category/load_more_button', [
                        'ajaxLoadArgs' => $this->ajaxLoadArgs
                    ], 'renderer', false);
                    ?>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>
