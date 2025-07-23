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
use Joomla\CMS\HTML\HTMLHelper;

/**
 * @var HyperPcViewMoysklad_Part $this
 */

$review = $this->part->getReview();

$fullWidth = $this->part->getParams()->get('full_width', false, 'bool');

$containerClass = $fullWidth ? '' : 'uk-container uk-container-large';
$sectionClass   = $fullWidth ? '' : 'uk-section uk-section-small';
$tabHr          = $fullWidth ? '' : '<hr class="uk-margin-large">';

$currentPrice = $this->part->getListPrice();

$compareItems  = $this->hyper['helper']['compare']->getItems('position');
$hasProperties = $this->hyper['helper']['position']->hasProperties($this->properties);

$image = $this->part->getItemImage();
$image = $image['original'];

$onlyForUpgrade = $this->part->isOnlyForUpgrade();

$tmpl = $this->hyper['input']->get('tmpl');

$pageTitle = $this->part->getPageTitle();

$shortDescription = \strip_tags($this->part->getParams()->get('short_desc'), '<span>');

echo $this->hyper['helper']['microdata']->getEntityMicrodata($this->part);
?>

<div class="hp-part">
    <div class="uk-overflow-hidden">
        <div class="uk-container uk-container-large">
            <div class="uk-grid uk-grid-small uk-flex-middle uk-margin-bottom">
                <div class="hp-part-image uk-width-1-2@m uk-visible@m">
                    <img
                        src="<?= $image->getPath() ?>"
                        alt="<?= $this->escape($pageTitle) ?>"
                        width="<?= $image->getWidth() ?>"
                        height="<?= $image->getHeight() ?>"
                        class="hp-part-image__image"
                    >
                </div>
                <div class="uk-width-expand uk-position-z-index">
                    <div class="uk-margin-small-bottom">
                        <h1 class="uk-h2 uk-margin-remove">
                            <?= $this->escape($pageTitle) ?>
                        </h1>
                        <?php if ($this->part->vendor_code) : ?>
                            <div class="uk-text-small uk-text-muted" >
                                <?= Text::sprintf('COM_HYPERPC_PRODUCT_VENDOR_CODE', $this->part->vendor_code) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if ($tmpl !== 'component' && $hasProperties) : ?>
                        <?= $this->hyper['helper']['render']->render('part/full/head/compare', [
                            'part' => $this->part,
                            'compareItems' => $compareItems
                        ]); ?>
                    <?php endif; ?>

                    <?php if (!empty($shortDescription)) : ?>
                        <p class="uk-margin-small-top">
                            <?= $shortDescription ?>
                        </p>
                    <?php endif; ?>

                    <div class="hp-part-image uk-margin-bottom uk-hidden@m">
                        <img
                            src="<?= $image->getPath() ?>"
                            alt="<?= $this->escape($pageTitle) ?>"
                            width="<?= $image->getWidth() ?>"
                            height="<?= $image->getHeight() ?>"
                            class="hp-part-image__image"
                        >
                    </div>

                    <?php if ($this->retail && $tmpl !== 'component') : ?>
                        <?php if ($this->showPurchaseBlock) : ?>
                            <hr class="uk-visible@s">

                            <div>
                                <?php if ($onlyForUpgrade) : ?>
                                    <div class="uk-alert uk-alert-warning">
                                        <?php
                                        $link = $this->hyper['params']->get('upgrade_center_link', '');
                                        if (!empty($link)) {
                                            echo Text::sprintf('COM_HYPERPC_PART_AVAILABLE_ONLY_FOR_UPGRADE_W_LINK', $link);
                                        } else {
                                            echo Text::_('COM_HYPERPC_PART_AVAILABLE_ONLY_FOR_UPGRADE');
                                        }
                                        ?>
                                    </div>
                                <?php endif; ?>

                                <div class="uk-grid uk-grid-small uk-child-width-1-1 uk-child-width-auto@s uk-flex-middle" data-uk-grid>
                                    <?= $this->hyper['helper']['render']->render('part/full/head/purchase', [
                                        'part' => $this->part,
                                        'partPrice' => $currentPrice
                                    ]); ?>
                                </div>

                                <hr>

                                <div class="hp-part-head__conditions-wrapper uk-margin">
                                    <?php
                                    $conditionsTmpl = 'part/full/head/conditions';
                                    if (!$onlyForUpgrade && $this->part->isInStock()) {
                                        $conditionsTmpl = 'part/full/head/conditions-shipping';
                                    }
                                    ?>
                                    <?= $this->hyper['helper']['render']->render($conditionsTmpl, [
                                        'part'           => $this->part,
                                        'price'          => $currentPrice->text(),
                                        'onlyForUpgrade' => $onlyForUpgrade
                                    ]); ?>
                                </div>

                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php
        $galleryHtml = $this->part->getRender()->gallery('image_gallery', 405);
        if (!empty($galleryHtml)) {
            $this->part->params->set('has_gallery', true);
        }

        $hasDescription = \trim($this->part->description) !== '' || !empty($review);
        $hasGallery = !empty($galleryHtml);

        $hasContent = $hasDescription || $hasGallery || $hasProperties;
    ?>

    <?php if ($hasContent) : ?>
        <?= $this->hyper['helper']['render']->render('part/full/nav', [
            'review'        => $review,
            'part'          => $this->part,
            'partPrice'     => $currentPrice,
            'hasProperties' => $hasProperties,
            'showPurchase'  => $this->showPurchaseBlock
        ]) ?>

        <div class="<?= $sectionClass ?>">
            <div class="<?= $containerClass ?>">
                <?php if (\trim($this->part->description) !== '') : ?>
                    <div id="part-description">
                        <?= HTMLHelper::_('content.prepare', $this->part->description); ?>
                    </div>
                <?php endif; ?>

                <?php foreach ($review as $tab) : ?>
                    <?= $tabHr ?>
                    <div id="part-block-<?= $tab['sorting'] ?>">
                        <?= HTMLHelper::_('content.prepare', $tab['description']); ?>
                    </div>
                <?php endforeach; ?>

                <?php if (!empty($galleryHtml)) : ?>
                    <div id="part-gallery">
                        <div class="uk-section<?= $fullWidth ? ' tm-background-gray-15' : '' ?>">
                            <?= $galleryHtml ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($hasProperties) : ?>
                    <div id="part-properties">
                        <div class="uk-section<?= $fullWidth ? ' uk-background-muted' : '' ?>">
                            <div class="uk-container uk-container-small">
                                <h2 class="uk-h1 uk-text-center@s">
                                    <?= Text::sprintf('COM_HYPERPC_PART_PROPERTY_TITLE', $pageTitle) ?>
                                </h2>
                                <?= $this->hyper['helper']['render']->render('part/full/properties', [
                                    'part'       => $this->part,
                                    'properties' => $this->properties
                                ]) ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>
