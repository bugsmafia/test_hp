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
use HYPERPC\ORM\Entity\Review;

/**
 * @var Review  $review
 */

$user = $review->getUser();

$reviewWrapperClass = 'uk-comment uk-section-small';

$userName = $user->name;
if (!empty($userName)) {
    $atSymbolPos = stripos($userName, '@');
    if ($atSymbolPos) {
        $userName = substr($userName, 0, $atSymbolPos);
    }
}
?>
<tr class="hp-review-row">
    <td style="visibility: hidden; width: 0; display: none;">
        <?= $review->created_time->format('d-m-Y') ?>
    </td>
    <td data-sort-method='none'>
        <article id="hp-review-<?= $review->getHref() ?>" class="<?= $reviewWrapperClass ?>">
            <header class="uk-comment-header">
                <div class="uk-grid uk-grid-medium" uk-grid>
                    <div class="uk-width-auto">
                        <img class="uk-comment-avatar" src="<?= $user->getAvatar() ?>" width="80">
                    </div>
                    <div class="uk-width-expand">
                        <div class="uk-grid uk-grid-small uk-flex-bottom">
                            <?php if (!empty($userName)) : ?>
                                <div class="uk-h3 uk-comment-title uk-margin-remove uk-display-inline-block">
                                    <?= $userName ?>
                                </div>
                            <?php endif; ?>
                            <span class="uk-text-small uk-text-muted" hidden><?= $review->dayAgo() ?></span>
                        </div>
                        <div class="uk-comment-meta uk-margin-small">
                            <div class="jsRatingStars" data-score="<?= $review->rating ?>"></div>
                            <?php if ($review->params->get('user_review_url')) : ?>
                                <a href="<?= trim($review->params->get('user_review_url')) ?>" target="_blank" rel="nofollow" class="uk-text-success">
                                    <?= Text::_('COM_HYPERPC_REVIEW_VERIFIED_REVIEW') ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </header>
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

                <?php
                $rewImgWidth  = $this->hyper['params']->get('review_img_width', 220);
                $rewImgHeight = $this->hyper['params']->get('review_img_height', 220);
                $images = $review->renderImages($rewImgWidth, $rewImgHeight);
                if ($images) : ?>
                    <div class="uk-margin">
                        <div class="uk-text-emphasis uk-text-bold">
                            <?= Text::_('COM_HYPERPC_REVIEW_IMAGE_LABEL') ?>:
                        </div>
                        <?= $images ?>
                    </div>
                <?php endif; ?>
            </div>
        </article>
    </td>
    <td style="visibility: hidden; width: 0; display: none;">
        <?= (int) $review->rating ?>
    </td>
</tr>
