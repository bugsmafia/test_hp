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
 *
 * @var         array $groups
 * @var         array $options
 * @var         \HYPERPC\Helper\RenderHelper $this
 * @var         \HYPERPC\Joomla\Model\Entity\Product $product
 */

use JBZoo\Utils\Url;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die('Restricted access');
?>

<?php foreach ($products as $product) :
    $category = $product->getFolder();

    if (!$category->id) {
        continue;
    }

    $price             = $product->getConfigPrice(true);
    $viewUrl           = $product->getViewUrl();
    $productTeaserType = 'default';

    if ($category->params->get('teasers_type', 'default') === 'lumen') {
        $viewUrl           = $category->getViewUrl();
        $productTeaserType = 'lumen';
    }
    ?>

    <div class="hp-product-teaser hp-product-teaser--short">
        <?= $this->hyper['helper']['microdata']->getEntityMicrodata($product); ?>
        <div class="hp-product-teaser__image">
            <?= $product->render()->image() ?>
        </div>
        <div class="hp-product-teaser__name">
            <?php
            $linkAttrs = [
                'href'  => $viewUrl,
                'title' => $product->name,
                'class' => 'uk-display-block uk-text-truncate uk-link-reset'
            ];
            $gtmOnClick = $this->hyper['helper']['render']->render('common/teaser/gtmProductClick', ['entity' => $product]);
            ?>
            <a <?= $this->hyper['helper']['html']->buildAttrs($linkAttrs) ?><?= $gtmOnClick ?>>
                <span>
                    <?= $product->name ?>
                </span>
            </a>
        </div>
        <hr class="hp-product-teaser__divider">
        <?php if ($price->val() > 0) : ?>
            <div class="hp-product-teaser__price">
                <?= Text::_('COM_HYPERPC_PRICE') ?>
                <?= $price->text() ?>
            </div>
        <?php endif; ?>
    </div>
<?php endforeach;
