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
 * @var HyperPcViewProfile_Reviews  $this
 */

HTMLHelper::_('behavior.core');

$count      = count($this->reviews);
$formAction = $this->hyper['route']->build(['view' => 'profile_reviews']);

$this->hyper['params']->set('product_img_link', 0);
?>
<style>
    .list-footer .limit {
        display: none;
    }
</style>

<div class="uk-container">
    <div class="uk-text-center">
        <h1 class="uk-margin-medium-bottom">
            <?= Text::_('COM_HYPERPC_MY_REVIEWS') ?>
        </h1>
    </div>
    <div class="uk-grid uk-grid-divider uk-margin-bottom" uk-grid>
        <div class="uk-width-auto uk-visible@m">
            <?= $this->hyper['helper']['render']->render('account/right_menu') ?>
        </div>
        <div class="uk-width-expand uk-overflow-auto">
            <div>
                <?= Text::sprintf('COM_HYPERPC_TOTAL_COUNT', $this->hyper['helper']['review']->getTotalSlant($count)) ?>
            </div>
            <hr class="uk-margin-small-top">
            <form action="<?= $formAction ?>" method="post" name="adminForm" id="adminForm">
                <table class="hp-profile-reviews uk-table uk-table-divider uk-table-responsive">
                    <tbody>
                    <?php
                    foreach ($this->reviews as $review) :
                        $entity = $review->getItem();

                        $viewUrl = null;
                        if ($entity !== null && $entity->id) {
                            $viewUrl = $entity->getViewUrl();
                        }
                        ?>
                        <tr>
                            <td width="200px">
                                <?php
                                if ($review->isContext(HP_OPTION . '.product') && $entity->id) {
                                    echo '<div style="width: 200px;">' . $entity->getRender()->image() . '</div>';
                                }
                                ?>
                                <?php if ($review->published && $viewUrl) : ?>
                                    <div class="uk-margin-small uk-margin-small-bottom uk-text-center@m">
                                        <a class="uk-button uk-button-default uk-button-small"
                                           href="<?= $viewUrl ?>#hp-review-<?= $review->getHref() ?>" target="_blank">
                                            <?= Text::_('COM_HYPERPC_VIEW') ?>
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <article id="hp-review-<?= $review->id ?>" class="uk-card">
                                    <header class="uk-comment-header">
                                        <?php if ($entity && $entity->id) : ?>
                                            <div class="uk-h3 uk-margin-remove">
                                                <?= $entity->getName() ?>
                                            </div>
                                        <?php endif; ?>
                                        <div class="uk-grid uk-grid-small uk-grid-divider uk-flex-middle uk-grid-divider uk-margin-small">
                                            <div class="jsRatingStars uk-first-column" data-score="<?= $review->rating ?>"></div>
                                            <div class="uk-text-small uk-text-muted"><?= $review->dayAgo() ?></div>
                                        </div>
                                    </header>
                                    <div class="uk-margin">
                                        <?= Text::sprintf('COM_HYPERPC_MY_REVIEWS_STATUS', $review->published ? Text::_('COM_HYPERPC_MY_REVIEWS_PUBLISHED') : Text::_('COM_HYPERPC_MY_REVIEWS_ON_MODERATE')); ?>
                                    </div>
                                    <div class="uk-comment-body">
                                        <?php if ($review->virtues) : ?>
                                            <div class="uk-margin">
                                                <div class="uk-text-emphasis uk-text-bold"><?= Text::_('COM_HYPERPC_REVIEW_VIRTUES_LABEL') ?>:</div>
                                                <div><?= nl2br($review->virtues) ?></div>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($review->limitations) : ?>
                                            <div class="uk-margin">
                                                <div class="uk-text-emphasis uk-text-bold"><?= Text::_('COM_HYPERPC_REVIEW_LIMITATIONS_LABEL') ?>:</div>
                                                <div><?= nl2br($review->limitations) ?></div>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($review->comment) : ?>
                                            <div class="uk-margin">
                                                <div class="uk-text-emphasis uk-text-bold"><?= Text::_('COM_HYPERPC_REVIEW_COMMENT_LABEL') ?>:</div>
                                                <div><?= nl2br($review->comment) ?></div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </article>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?= HTMLHelper::_('form.token') ?>
            </form>
        </div>
    </div>
</div>
