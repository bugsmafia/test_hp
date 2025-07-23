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
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use HYPERPC\Helper\RenderHelper;
use HYPERPC\Joomla\Model\Entity\Interfaces\Stockable;
use HYPERPC\Joomla\Model\Entity\Interfaces\ProductMarker;

/**
 * @var RenderHelper    $this
 * @var string          $price
 * @var ProductMarker   $product
 */

$availability = $product->getAvailability();
?>

<div class="hp-product-head__conditions uk-margin-bottom">

    <div class="uk-grid" data-uk-grid>
        <div>
            <span class="hp-conditions-item uk-flex uk-flex-middle">
                <span class="hp-conditions-item__icon">
                    <span data-uk-icon="icon:<?= $availability === Stockable::AVAILABILITY_INSTOCK ? 'check' : 'clock' ?>" class="uk-icon"></span>
                </span>
                <span>
                    <span class="hp-conditions-item__text">
                        <?php if ($availability === Stockable::AVAILABILITY_PREORDER) : ?>
                            <span class="uk-text-warning">
                                <?= Text::_('COM_HYPERPC_PRODUCT_PREORDER_TEXT') ?>
                            </span>
                        <?php elseif ($availability === Stockable::AVAILABILITY_INSTOCK) : ?>
                            <span class="uk-text-success">
                                <?= Text::_('COM_HYPERPC_PRODUCT_INSTOCK_TEXT') ?>
                            </span>
                        <?php elseif ($availability === Stockable::AVAILABILITY_OUTOFSTOCK) : ?>
                            <span class="uk-text-warning">
                                <?= Text::_('COM_HYPERPC_PRODUCT_OUTOFSTOCK_TEXT') ?>
                            </span>
                        <?php endif; ?>
                    </span>

                    <?php if (\in_array($availability, [Stockable::AVAILABILITY_INSTOCK, Stockable::AVAILABILITY_PREORDER])) : ?>
                        <span class="hp-conditions-item__sub">
                            <a href="#delivery-options" class="uk-link-muted tm-link-dashed" data-uk-toggle>
                                <?= Text::_('COM_HYPERPC_WAYS_TO_RECEIVE') ?>
                            </a>
                        </span>
                    <?php elseif ($availability === Stockable::AVAILABILITY_OUTOFSTOCK) : ?>
                        <span class="hp-conditions-item__sub">
                            <?= Text::_('COM_HYPERPC_PRODUCT_OUTOFSTOCK_TEXT_SUB') ?>
                        </span>
                    <?php endif; ?>
                </span>
            </span>
        </div>

        <?php if ($product->isPublished() && \in_array($availability, [Stockable::AVAILABILITY_INSTOCK, Stockable::AVAILABILITY_PREORDER])) : ?>
            <div>
                <a href="<?= $product->getConfigUrl() ?>" class="uk-link-reset jsGoToConfigurator" title="<?= Text::_('COM_HYPERPC_GO_TO_CONFIGURATOR') ?>">
                    <span class="hp-conditions-item hp-conditions-item--link uk-flex uk-flex-middle">
                        <span class="hp-conditions-item__icon">
                            <span data-uk-icon="icon:cog" class="uk-icon"></span>
                        </span>
                        <span>
                            <span class="hp-conditions-item__text">
                                <?= Text::_('COM_HYPERPC_CONFIGURATOR') ?>
                            </span>
                            <span class="hp-conditions-item__sub">
                                <?= Text::_('COM_HYPERPC_CREATE_YOUR_OWN') ?>
                            </span>
                        </span>
                    </span>
                </a>
            </div>
        <?php endif; ?>

        <div>
            <?= $this->hyper['helper']['render']->render('common/full/question/button', [
                'itemName' => $product->get('name'),
                'price'    => $price,
                'type'     => 'product'
            ]); ?>
        </div>

    </div>

</div>

<?= $this->hyper['helper']['render']->render('common/full/shipping-modal', [
    'entity'     => $product,
    'parcelData' => $product->getDimensions()
]);
