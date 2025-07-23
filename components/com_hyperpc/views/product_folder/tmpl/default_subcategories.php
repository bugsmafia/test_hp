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
 * @author      Roman Evsyukov
 */

defined('_JEXEC') or die('Restricted access');

use HYPERPC\Helper\RenderHelper;
use HYPERPC\Joomla\Model\Entity\ProductFolder;

/**
 * @var         RenderHelper    $this
 * @var         ProductFolder[] $productFolders
 */

$responsiveClass = $this->hyper['helper']['uikit']->getResponsiveClassByCols($productFolder->getColumns());
?>

<div class="uk-grid uk-grid-small uk-grid-match <?= $responsiveClass ?>" uk-grid="margin: uk-margin-medium-top">

    <?php foreach ($productFolders as $child) :
        $imageAlt = ($child->getParams()->get('image_alt') !== '') ? $child->getParams()->get('image_alt') : $child->title;
        $image    = (string) $child->params->get('image', '', 'hpimagepath');
        ?>
        <div class="hp-category-teaser">
            <div class="uk-card uk-card-small uk-text-center">
                <div class="hp-category-teaser-image uk-card-media-top uk-flex uk-flex-center uk-flex-middle">
                    <?php if ($image !== '') : ?>
                        <div>
                            <a href="<?= $child->getViewUrl() ?>">
                                <?= $this->hyper['helper']['html']->image($image, ['title' => $imageAlt]) ?>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="uk-card-body uk-padding-remove-vertical uk-margin-small-top">
                    <div class="hp-category-teaser-title">
                        <h2 class="uk-h3 uk-margin-remove uk-card-title uk-link-reset">
                            <a href="<?= $child->getViewUrl() ?>">
                                <?= $child->title ?>
                            </a>
                        </h2>
                    </div>
                </div>
                <a href="<?= $child->getViewUrl() ?>" class="uk-position-cover"></a>
            </div>
        </div>

    <?php endforeach; ?>

</div>
