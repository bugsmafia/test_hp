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

/**
 * @var array $images
 */
?>

<?php if (count($images) === 1) :
    $image = array_shift($images);
    if (!isset($image['thumb'])) {
        return null;
    }

    $original = $image['original'];
    $thumb = $image['thumb'];
    ?>
    <div uk-lightbox>
        <a href="<?= '/' . ltrim($original->getPath(), '/') ?>">
            <img src="<?= '/' . ltrim($thumb->getPath(), '/') ?>" alt="" width="<?= $thumb->getWidth() ?>" height="<?= $thumb->getHeight() ?>"/>
        </a>
    </div>
<?php elseif (count($images) > 1) : ?>
    <div class="uk-grid uk-grid-small" uk-grid uk-lightbox="animation: slide">
        <?php foreach ($images as $image) :
            if (!isset($image['thumb'])) {
                continue;
            }
            ?>
            <div>
                <a href="<?= '/' . ltrim($original->getPath(), '/') ?>">
                    <img src="<?= '/' . ltrim($thumb->getPath(), '/') ?>" alt="" width="<?= $thumb->getWidth() ?>" height="<?= $thumb->getHeight() ?>" />
                </a>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif;
