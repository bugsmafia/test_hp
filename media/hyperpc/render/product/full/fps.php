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
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use HYPERPC\Helper\FpsHelper;
use HYPERPC\Helper\RenderHelper;
use HYPERPC\Joomla\Model\Entity\Interfaces\ProductMarker;

/**
 * @var RenderHelper  $this
 * @var ProductMarker $product
 */

/** @var FpsHelper */
$fpsHelper = $this->hyper['helper']['fps'];

$categoryId = $product->getFolderId();

$showFps = $fpsHelper->showFps($categoryId);

$productFps = $showFps ? array_filter($fpsHelper->getFps($product)) : [];
?>
<?php if (count($productFps)) : ?>
    <div id="product-performance" class="uk-section tm-background-gray-5">
        <div class="uk-container uk-container-small">
            <h2 class="uk-h1 uk-text-center uk-margin-medium-bottom">
                <?= Text::_('COM_HYPERPC_FPS_TITLE_PRODUCT') ?>
            </h2>

            <?php
            echo $this->render('/product/common/fps/fps_table', [
                'productFps' => $productFps
            ]);
            ?>

            <?= $this->render('/product/common/fps/disclaimer') ?>

        </div>
    </div>
<?php endif;
