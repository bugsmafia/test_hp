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
 * @author      Sergey Kalistratov <kalistratov.s.m@gmail.com>
 */

use Joomla\CMS\Language\Text;
use HYPERPC\Money\Type\Money;
use HYPERPC\Helper\RenderHelper;
use HYPERPC\Joomla\Model\Entity\Interfaces\PartMarker;
use HYPERPC\Joomla\Model\Entity\Interfaces\OptionMarker;

/**
 * @var array           $review
 * @var bool            $hasProperties
 * @var bool            $showPurchase
 * @var RenderHelper    $this
 * @var Money           $partPrice
 * @var PartMarker      $part
 * @var ?OptionMarker[] $options
 * @var ?OptionMarker   $optionDefault
 */
?>

<div class="hp-goods-nav tm-page-sticky-nav uk-navbar-container<?= $showPurchase ? ' jsSeparatedNavbar' : '' ?>" uk-sticky>
    <nav class="uk-container uk-container-large">
        <div class="uk-navbar uk-flex-wrap">
            <div class="uk-navbar-left tm-page-sticky-nav__menu">
                <ul class="uk-navbar-nav jsScrollableNav" uk-scrollspy-nav="closest: li; scroll: true; overflow: true; offset: -8">
                    <?php if (trim($part->get('description')) !== '') : ?>
                        <li>
                            <a href="#part-description"><?= Text::_('JGLOBAL_DESCRIPTION') ?></a>
                        </li>
                    <?php endif; ?>
                    <?php foreach ($review as $tab) : /** Begin render review tabs header */ ?>
                        <li>
                            <a href="#part-block-<?= $tab['sorting'] ?>" class="uk-text-nowrap">
                                <?= \htmlspecialchars($tab['name']) ?>
                            </a>
                        </li>
                    <?php endforeach; /** End render review tabs header */ ?>
                    <?php if ($part->getParams()->get('has_gallery', false, 'bool')) : ?>
                        <li>
                            <a href="#part-gallery" class="uk-text-nowrap">
                                <?= Text::_('COM_HYPERPC_PART_GALLERY') ?>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if ($hasProperties) : ?>
                        <li>
                            <a href="#part-properties"><?= Text::_('COM_HYPERPC_PART_PROPERTIES_NAV_TITLE') ?></a>
                        </li>
                    <?php endif; ?>
                    <?php if (isset($options) && count($options)) : ?>
                        <li>
                            <a href="#choose-model">
                                <?= Text::_('COM_HYPERPC_PART_MODIFICATIONS') ?>
                            </a>
                        </li>
                    <?php endif; ?>
                    <li class="tm-page-sticky-nav__totop">
                        <a href="#" data-uk-scroll data-uk-totop></a>
                    </li>
                </ul>
            </div>
            <?php if ($showPurchase) : ?>
                <div class="uk-navbar-right">
                    <div class="hp-part-purchase uk-navbar-item uk-text-nowrap">
                        <?= $this->hyper['helper']['render']->render('common/price/item-price', [
                            'price'      => $partPrice,
                            'entity'     => $part,
                            'htmlPrices' => true
                        ]); ?>

                        <div class="uk-margin-small-left">
                            <?php if (!isset($options) || !count($options)) : ?>
                                <?= $part->getRender()->getCartBtn('button', [
                                    'size' => 'navbar',
                                ]);
                                ?>
                            <?php else : ?>
                                <?= $part->getRender()->getCartBtn('button', [
                                    'option'           => $optionDefault,
                                    'part'             => $part,
                                    'useDefaultOption' => false,
                                    'size'             => 'navbar'
                                ]);
                                ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </nav>
</div>
