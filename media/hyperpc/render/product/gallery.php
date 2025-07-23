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
 * @var array   $images
 * @var string  $wrapperId
 */

?>
<?php foreach ($images as $image) :
    $height = $image['thumb']->getHeight();
    $width = $image['thumb']->getWidth();
    ?>
    <div>
        <div class="uk-inline-clip uk-transition-toggle">
            <img src="<?= $image['thumb']->getUrl() ?>" alt="" width="<?= $width ?>" height="<?= $height ?>" loading="lazy" />
            <div class="uk-transition-fade uk-position-cover uk-overlay uk-overlay-default uk-flex uk-flex-center uk-flex-middle">
                <span uk-icon="icon: search; ratio: 2"></span>
            </div>
            <a href="<?= $image['original']->getUrl() ?>" class="uk-position-cover" target="_blank"></a>
        </div>
    </div>
<?php endforeach;
