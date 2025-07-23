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
 */

use HYPERPC\App;
use HYPERPC\Joomla\Model\Entity\ProductFolder;

/**
 * @var array   $displayData
 */

$products   = $displayData['products'];
/** @var ProductFolder[][] $categories */
$categories = array_chunk($displayData['categories'], 4);

$app      = App::getInstance();
$setValue = $displayData['field']->isSaveAndSetValue();
$values   = $displayData['field']->getCurrentValue();
?>
<script>
    jQuery(function($) {
        $('.jsBuildPartCheckAll').on('click', function () {
            if ($(this).prop('checked')) {
                $(this)
                    .closest('.hp-part-related-product')
                    .find('input[type=checkbox]').prop('checked', true);
            } else {
                $(this)
                    .closest('.hp-part-related-product')
                    .find('input[type=checkbox]').prop('checked', false);
            }
        });
    });
</script>
<div class="hp-part-related-product">
    <div class="row">
        <div class="col-12">
            <label>
                <input class="jsBuildPartCheckAll" type="checkbox" />
                Выбрать все
            </label>
        </div>
    </div>
    <?php foreach ($categories as $row) : ?>
        <div class="row">
            <?php foreach ($row as $category) : ?>
                <div class="col-12 col-lg-3 my-3">
                    <div class="card card-body bg-light">
                        <h4><?= $category->title ?></h4>
                        <?php if (isset($products[$category->id])) : ?>
                        <table>
                            <?php foreach ((array) $products[$category->id] as $product) :
                                $checkBoxAttrs = [
                                    'type'  => 'checkbox',
                                    'id'    => 'product-' . $product->id,
                                    'value' => $product->id
                                ];

                                if ($setValue && in_array($product->id, $values)) {
                                    $checkBoxAttrs['checked'] = 'checked';
                                }

                                if ($setValue) {
                                    $checkBoxAttrs['name'] = $displayData['name'];
                                } else {
                                    $checkBoxAttrs['name'] = $displayData['name'] . '[]';
                                }

                                $checkBoxAttrs = $app['helper']['html']->buildAttrs($checkBoxAttrs);
                                ?>
                                <tr>
                                    <td>
                                        <span class="muted">┊&nbsp;&nbsp;&nbsp;</span>–&nbsp;
                                    </td>
                                    <td>
                                        <input <?= $checkBoxAttrs ?> />
                                    </td>
                                    <td>
                                        <label for="product-<?= $product->id ?>">
                                            <?= $product->name ?>
                                        </label>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
</div>
