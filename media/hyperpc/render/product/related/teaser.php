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
 * @author      Sergey Kalistratov <kalistratov.s.m@gmail.com>
 * @var         \HYPERPC\Joomla\Model\Entity\Product $entity
 * @var         \HYPERPC\Joomla\Model\Entity\Product $product
 */

$related = $entity->params->get('related', []);
if (count($related) > 0) : ?>
    <div class="uk-grid">
        <?php foreach ($products as $product) : ?>
            <div class="uk-width-small-1-2 uk-width-medium-1-3 uk-width-large-1-5">
                <div class="hp-product-image">
                    <?= $product->render()->image() ?>
                </div>
                <h3 class="uk-h6 uk-margin-remove">
                    <a title="<?= $product->name ?>" href="<?= $product->getViewUrl() ?>">
                        <?= $product->name ?>
                    </a>
                </h3>
                <hr class="uk-margin-small">
                <div class="uk-text-small">
                    <?= $product->description ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif;
