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
 * @var HyperPcViewMoysklad_Service $this
 */

$review = $this->service->getReview();

$containerClass = $this->service->params->get('full_width', 0) ? '' : 'uk-container uk-container-large';
$sectionClass   = $this->service->params->get('full_width', 0) ? '' : 'uk-section uk-section-small';
$tabHr          = $this->service->params->get('full_width', 0) ? '' : '<hr class="uk-margin-large">';

$currentPrice = $this->service->getListPrice();

/** @todo Repair compare for services */
//$compareItems  = $this->hyper['helper']['compare']->getItems('part');
$hasProperties = $this->hyper['helper']['position']->hasProperties($this->properties);

$shortDescription = \strip_tags($this->service->getParams()->get('short_desc', ''), '<span>');

echo $this->hyper['helper']['microdata']->getEntityMicrodata($this->service);

$image = $this->service->getItemImage();
?>

<div class="hp-part">
    <div class="uk-overflow-hidden">
        <div class="uk-container uk-container-large">
            <div class="uk-grid uk-grid-small uk-flex-middle uk-margin-bottom">
                <div class="hp-part-image uk-width-1-2@m uk-visible@m">
                    <img src="<?= $image['original']->getPath() ?>" alt="<?= $this->service->getPageTitle() ?>" class="hp-part-image__image">
                </div>
                <div class="uk-width-expand uk-position-z-index">
                    <div class="uk-margin-small-bottom">
                        <h1 class="uk-h2 uk-margin-remove">
                            <?= $this->service->getPageTitle() ?>
                        </h1>
                    </div>

                    <?php if (!empty($shortDescription)) : ?>
                        <p class="uk-margin-small-top">
                            <?= $shortDescription ?>
                        </p>
                    <?php endif; ?>

                    <?php if (!empty($this->service->images->get('image', '', 'hpimagepath'))) : ?>
                        <div class="hp-part-image uk-margin-bottom uk-hidden@m">
                            <img src="<?= $image['original']->getPath() ?>" alt="<?= $this->service->getPageTitle() ?>" class="hp-part-image__image">
                        </div>
                    <?php endif; ?>

                    <?php if ($this->hyper['input']->get('tmpl') !== 'component') : ?>
                        <?php if ($this->showPurchaseBlock) : ?>
                            <hr class="uk-visible@s">

                            <div>
                                <div class="uk-grid uk-grid-small uk-child-width-1-1 uk-child-width-auto@s uk-flex-middle" uk-grid>

                                    <?= $this->hyper['helper']['render']->render('part/full/head/purchase', [
                                        'options'       => [],
                                        'part'          => $this->service,
                                        'partPrice'     => $currentPrice
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
    $galleryHtml = $this->service->getRender()->gallery('image_gallery', 405);
    if (!empty($galleryHtml)) {
        $this->service->params->set('has_gallery', true);
    }

    $hasDescription = trim($this->service->description) !== '' || !empty($review);
    $hasGallery = !empty($galleryHtml);

    $hasContent = $hasDescription || $hasGallery || $hasProperties;
    ?>

    <?php if ($hasContent) : ?>
        <?= $this->hyper['helper']['render']->render('part/full/nav', [
            'review'        => $review,
            'options'       => [],
            'part'          => $this->service,
            'partPrice'     => $currentPrice,
            'hasProperties' => $hasProperties,
            'showPurchase'  => $this->showPurchaseBlock
        ]) ?>

        <div class="<?= $sectionClass ?>">
            <div class="<?= $containerClass ?>">
                <?php if (trim($this->service->description) !== '') : ?>
                    <div id="part-description">
                        <?= HTMLHelper::_('content.prepare', $this->service->description); ?>
                    </div>
                <?php endif; ?>

                <?php foreach ($review as $tab) : /** Begin render review tabs description */ ?>
                    <?= $tabHr ?>
                    <div id="part-block-<?= $tab['sorting'] ?>">
                        <?= HTMLHelper::_('content.prepare', $tab['description']); ?>
                    </div>
                <?php endforeach; /** End render review tabs description */  ?>

                <?php if (!empty($galleryHtml)) : ?>
                    <div id="part-gallery">
                        <div class="uk-section<?= $this->service->params->get('full_width', 0) ? ' tm-background-gray-15' : '' ?>">
                            <?= $galleryHtml ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($hasProperties) : ?>
                    <div id="part-properties">
                        <div class="uk-section<?= $this->service->params->get('full_width', 0) ? ' uk-background-muted' : '' ?>">
                            <div class="uk-container uk-container-small">
                                <h2 class="uk-h1 uk-text-center@s">
                                    <?= Text::sprintf('COM_HYPERPC_PART_PROPERTY_TITLE', $this->service->getPageTitle()) ?>
                                </h2>
                                <?= $this->hyper['helper']['render']->render('part/full/properties', [
                                    'part'       => $this->service,
                                    'properties' => $this->properties,
                                    'option'     => null
                                ]) ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>
