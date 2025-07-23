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
 * @author      Roman Evsyukov <roman_e@hyperpc.ru>
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use HYPERPC\ORM\Entity\Review;
use HYPERPC\Helper\RenderHelper;
use HYPERPC\Html\Data\Product\Review as ReviewsData;
use HYPERPC\Joomla\Model\Entity\Interfaces\ProductMarker;

/**
 * @var         RenderHelper    $this
 * @var         ReviewsData     $reviewsData
 * @var         ProductMarker   $item
 * @var         Review          $review
 */

if (!isset($reviewsData)) {
    $reviewsData = new ReviewsData($item, 'default', 0, 4);
}

$uniq  = uniqid('hp-reviews-');
$form  = $reviewsData->getForm();

$modalId = uniqid('hp-modal-');

$this->hyper['helper']['assets']
    ->jqueryRaty()
    ->js('js:widget/site/review.js')
    ->widget('.' . $uniq, 'HyperPC.SiteReview', [
        'item_id'       => $item->id,
        'modal_id'      => $modalId,
        'preview_val'   => $reviewsData->getPreviewValue(),
        'rating'        => $reviewsData->getTotalRating()
    ]);

?>
<div id="product-reviews" class="uk-section">
    <div class="uk-container">
        <h2 class="uk-h1 uk-text-center"><?= Text::_('COM_HYPERPC_REVIEWS') ?></h2>
        <div class="hp-reviews <?= $uniq ?>">
            <div class="uk-card uk-card-default uk-card-body uk-margin-bottom">

                <div class="uk-grid uk-flex-middle uk-margin" uk-grid>

                    <div class="uk-width-1-1 uk-width-auto@s uk-text-center">
                        <div>
                            <?= Text::_('COM_HYPERPC_REVIEW_TOTAL_RATING') ?>
                        </div>
                        <div class="uk-heading-medium uk-margin-remove">
                            <?= number_format($reviewsData->getTotalRating(), 1); ?>
                        </div>
                        <div id="hp-review-total-vote" class="uk-margin-small-top"></div>
                        <div class="uk-text-muted">
                            (<?= $reviewsData->getTotalSlant() ?>)
                        </div>
                    </div>

                    <div class="uk-width-expand">
                        <?php foreach ($reviewsData->getRating() as $rKey => $_rItem) :
                            list(, $starPosition) = explode('_', $rKey);
                            ?>
                            <div class="uk-grid uk-grid-small uk-flex-middle uk-flex-nowrap">
                                <div class="uk-width-auto">
                                    <?= $starPosition ?>
                                </div>
                                <div class="uk-width-expand">
                                    <progress id="js-progressbar" class="uk-progress" value="<?= round($_rItem) ?>" max="100"></progress>
                                </div>
                                <div style="width:45px">
                                    <?= round($_rItem) . '%' ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="uk-width-1-1 uk-width-auto@m uk-text-center">
                        <?php if (!$this->hyper['user']->id) : ?>
                            <button class="jsLeaveReview uk-button uk-button-primary" data-modal-id="#<?= $modalId ?>">
                                <?= Text::_('COM_HYPERPC_REVIEW_LEAVE_A_REVIEW') ?>
                            </button>
                        <?php else : ?>
                            <button class="jsLeaveReview uk-button uk-button-primary"
                                uk-toggle="target: #<?= $modalId ?>">
                                <?= Text::_('COM_HYPERPC_REVIEW_LEAVE_A_REVIEW') ?>
                            </button>
                        <?php endif; ?>
                    </div>

                </div>

            </div>
            <div class="uk-margin uk-margin-medium-top">
                <!--
                <div>
                    <span class="uk-margin-small-right">
                        <?= Text::_('COM_HYPERPC_SORT') ?>:
                    </span>
                    <button class="jsReviewsSort uk-button uk-button-link uk-link-muted uk-text-top uk-margin-small-right" data-element="#hp-table-sort-date">
                        <?= Text::_('COM_HYPERPC_SORT_BY_DATE') ?>
                        <span class="uk-icon" uk-icon="triangle-up"></span>
                    </button>
                    <button class="jsReviewsSort uk-button uk-button-link uk-link-muted uk-text-top" data-element="#hp-table-sort-rating">
                        <?= Text::_('COM_HYPERPC_SORT_BY_RATING') ?>
                        <span class="uk-icon" uk-icon="triangle-down"></span>
                    </button>
                    <hr class="uk-margin-small uk-margin-remove-bottom">
                </div>
                -->

                <table id="hp-table-reviews" class="uk-table">
                    <thead>
                    <tr class="uk-hidden">
                        <th style="visibility: hidden; width: 0; display: none;" id="hp-table-sort-date"></th>
                        <th data-sort-method='none'></th>
                        <th style="visibility: hidden; width: 0; display: none;" id="hp-table-sort-rating" aria-sort="ascending"></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    foreach ($reviewsData->getReviews() as $review) :
                        echo $this->hyper['helper']['render']->render('reviews/default/item', [
                            'item'    => $item,
                            'total'   => $reviewsData->getReviewsCount(),
                            'review'  => $review
                        ]);
                    endforeach;

                    if (count($reviewsData->getAjaxArgs())) :
                        echo $this->hyper['helper']['render']->render('reviews/default/load_more', [
                            'ajaxLoadArgs' => $reviewsData->getAjaxArgs()
                        ]);
                    endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if (!$reviewsData->getReviewsCount()) : ?>
                <div class="uk-h3 jsNoReviews">
                    <?= Text::_('COM_HYPERPC_REVIEW_NO_REVIEWS_YET') ?>
                </div>
            <?php endif; ?>
            <?php
            echo $this->hyper['helper']['uikit']->modal(
                $modalId,
                $this->hyper['helper']['render']->render('reviews/default/form', [
                    'item' => $item
                ])
            );
            ?>
        </div>
    </div>
</div>
