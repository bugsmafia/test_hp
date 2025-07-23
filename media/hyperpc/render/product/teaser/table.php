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
use HYPERPC\Helper\RenderHelper;
use HYPERPC\Helper\ProductFolderHelper;
use HYPERPC\Helper\MoyskladProductHelper;
use HYPERPC\Joomla\Model\Entity\Interfaces\ProductMarker;
use HYPERPC\Joomla\Model\Entity\Interfaces\CategoryMarker;

/**
 * @var RenderHelper    $this
 * @var array           $groups
 * @var ProductMarker[] $products
 */

?>
<?php foreach ($products as $product) :
    /** @var CategoryMarker $category */
    $category = $product->getFolder();
    if (!$category->id) {
        continue;
    }

    /** @var MoyskladProductHelper */
    $productHelper = $product->getHelper();

    /** @var ProductFolderHelper */
    $groupHelper = $this->hyper['helper']['productFolder'];

    $price = $product->getConfigPrice(true);

    $teaserParts = $productHelper->getTeaserParts($product, 'table', false, true);
    $tableGroups = $category->getGroupsInTeaserTable();
    if (!isset($groups)) {
        $groups = $groupHelper->findById($tableGroups);
    }
    ?>
    <tr class="hp-product-teaser">
        <td>
            <a href="<?= $product->getViewUrl() ?>" class="uk-link-text">
                <?= $product->getNameWithoutBrand() ?>
            </a>
        </td>
        <?php foreach ($tableGroups as $groupId) :
            $cellValue = ['-'];
            if (isset($teaserParts[$groupId])) {
                $parts = $teaserParts[$groupId];
                $cellValue = [];
                foreach ($parts as $part) {
                    $value = $part->getName();

                    $group = $groups[$groupId];
                    $fieldForValue = $group->params->get('teaser_table_field');

                    if ($fieldForValue && $fieldForValue > 1) {
                        $fieldValue = $part->getFieldValueById($fieldForValue);
                        if (!empty(trim($fieldValue))) {
                            $value = $fieldValue;
                        }
                    }

                    if ($part->get('quantity', 1, 'int') > 1) {
                        $value = trim($part->quantity) . ' x ' . $value;
                    }

                    $cellValue[] = $value;
                }
            }
            ?>
            <td>
                <?= join('<br>', $cellValue) ?>
            </td>
        <?php endforeach; ?>
        <td class="uk-text-bold uk-text-nowrap">
            <?= Text::sprintf('COM_HYPERPC_STARTS_FROM', $price->text()) ?>
        </td>
        <td>
            <?php if ((bool) $product->on_sale === true) : ?>
                <a href="<?= $product->getViewUrl() ?>" class="uk-button uk-button-default uk-button-small">
                    <?= Text::_('COM_HYPERPC_PRODUCT_TEASER_DETAILS') ?>
                </a>
            <?php else : ?>
                <a class="uk-disabled uk-button uk-button-default uk-button-small">
                    <?= Text::_('COM_HYPERPC_ITEM_NO_BALANCE') ?>
                </a>
            <?php endif; ?>
        </td>
    </tr>
<?php endforeach;
