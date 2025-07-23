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
 * @author      Artem Vyshnevskiy
 * @var         array $images
 * @var         string $wrapperId
 * @var         string $height
 */

use Joomla\CMS\Language\Text;

$i = 0;
?>
<?php if (count($images) > 0) : ?>

    <div class="uk-container" style="max-width: 1100px;">
        <div uk-slideshow="ratio: 3:2; animation: fade;">
            <div class="uk-position-relative uk-visible-toggle uk-light">
                <ul class="uk-slideshow-items">
                    <?php foreach ($images as $image) : ?>
                        <li>
                            <img src="<?= $image['original']->getUrl() ?>" alt="" uk-cover="">
                        </li>
                    <?php endforeach; ?>
                </ul>

                <a class="uk-position-center-left uk-position-small uk-slidenav-large uk-hidden-hover" href="#" uk-slidenav-previous uk-slideshow-item="previous"></a>
                <a class="uk-position-center-right uk-position-small uk-slidenav-large uk-hidden-hover" href="#" uk-slidenav-next uk-slideshow-item="next"></a>
            </div>
            <div class="uk-margin-small">

                <ul class="uk-thumbnav uk-flex-center" uk-margin>
                    <?php foreach ($images as $image) : ?>
                        <li uk-slideshow-item="<?= $i ?>">
                            <a href="#">
                                <img src="<?= $image['original']->getUrl() ?>" alt="" width="96">
                            </a>
                        </li>
                        <?php $i++; ?>
                    <?php endforeach; ?>
                </ul>

            </div>
        </div>
    </div>

    <div class="uk-margin-medium uk-text-center@s uk-text-muted">
        * <?= Text::_('COM_HYPERPC_PART_GALLERY_DISCLAIMER') ?>
    </div>
<?php endif; ?>
