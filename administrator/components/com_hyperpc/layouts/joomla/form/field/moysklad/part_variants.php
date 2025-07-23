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
 * @author      Roman Evsyukov
 * @author      Artem Vyshnevskiy
 */

defined('_JEXEC') or die('Restricted access');

use HYPERPC\App;
use JBZoo\Data\Data;
use JBZoo\Data\JSON;
use Joomla\CMS\Language\Text;
use HYPERPC\Joomla\Model\Entity\MoyskladPart;

/**
 * @var         array $displayData
 * @var         JSON $value
 */

$app = App::getInstance();
$data = new Data($displayData);

/** @var MoyskladPart $part */
$part = $data->get('part');
$variants = $part->getOptions();

$addVariantUrl = $app['helper']['route']->url([
    'layout'    => 'edit',
    'view'      => 'moysklad_variant',
    'part_id'   => $part->id,
    'folder_id' => $part->product_folder_id
]);

$countArchive     = 0;
$defaultVariantID = $part->params->get('default_option', 0, 'int');
?>
<div class="jsFieldVariants">
    <div class="col-12 col-lg-6">
        <div class="hp-field-options">
            <h4><?= Text::_('COM_HYPERPC_PART_RELATED_OPTION_TITLE') ?></h4>
            <?php if (count($variants)) : ?>
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th></th>
                            <th width="8%">
                                <?= Text::_('COM_HYPERPC_NUM') ?>
                            </th>
                            <th>
                                <?= Text::_('COM_HYPERPC_OPTION') ?>
                            </th>
                            <th class="center">
                                <?= Text::_('COM_HYPERPC_PRICE') ?>
                            </th>
                            <th class="center">
                                <?= Text::_('COM_HYPERPC_SALE_PRICE') ?>
                            </th>
                            <th class="center">
                                <?= Text::_('COM_HYPERPC_BALANCE') ?>
                            </th>
                            <th class="center">
                                <?= Text::_('COM_HYPERPC_ACTIONS') ?>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($variants as $i => $variant) :
                            $idDefault = ($defaultVariantID === $variant->id);

                            $editVariantUrl = $app['helper']['route']->url([
                                'layout'   => 'edit',
                                'view'     => 'moysklad_variant',
                                'id'       => $variant->id,
                                'part_id'  => $app['input']->get('id', 0),
                                'product_folder_id' => $app['input']->get('product_folder_id', 0)
                            ]);

                            $freeStocks = $variant->getFreeStocks();

                            $variantClass = [];
                            if ($variant->isArchived()) {
                                $variantClass[] = 'hp-row-archive';

                                if ($freeStocks === 0) {
                                    $variantClass[] = 'jsVariantRow';
                                    $countArchive++;
                                }
                            }
                            ?>
                            <tr class="<?= implode(' ', $variantClass) ?>">
                                <td>
                                    <?php if (!$variant->isArchived()) : ?>
                                        <input type="radio" class="jsToggleDefaultVariant" name="jform[params][default_option]"
                                            value="<?= $variant->id ?>"
                                            data-price="<?= $variant->list_price->val() ?>"
                                            <?= ($idDefault) ? ' checked="checked"' : '' ?>>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-info">
                                        <?= $variant->id ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?= $editVariantUrl ?>" title="<?= Text::_('JACTION_EDIT') ?>">
                                        <?= $variant->name ?>
                                    </a>
                                </td>
                                <td class="text-nowrap">
                                    <?= $variant->list_price->text() ?>
                                </td>
                                <td>
                                    <?= $variant->sale_price->text() ?>
                                </td>
                                <td class="center">
                                    <span class="badge bg-info"><?= $freeStocks ?></span>
                                </td>
                                <td class="center">
                                    <?php if ($variant->isPublished()) : ?>
                                        <a href="#" class="jsChangeState tbody-icon" data-id="<?= $variant->id ?>">
                                            <span class="icon-publish" title="<?= Text::_('JLIB_HTML_UNPUBLISH_ITEM') ?>"></span>
                                        </a>
                                    <?php elseif ($variant->isUnpublished()) : ?>
                                        <a href="#" class="jsChangeState tbody-icon" data-id="<?= $variant->id ?>">
                                            <span class="icon-unpublish" title="<?= Text::_('JLIB_HTML_PUBLISH_ITEM') ?>"></span>
                                        </a>
                                    <?php elseif ($variant->isTrashed()) : ?>
                                        <a href="#" class="jsRemoveVariant tbody-icon" data-id="<?= $variant->id ?>">
                                            <span class="icon-trash border-danger link-danger" title="<?= Text::_('JACTION_DELETE') ?>"></span>
                                        </a>
                                    <?php elseif ($variant->isArchived()) : ?>
                                        <span class="tbody-icon">
                                            <span class="icon-archive"></span>
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <?php if ($countArchive > 0) : ?>
                    <span class="jsToggleArchiveVariants btn btn-info jsNoActive">
                        <?= Text::_('COM_HYPERPC_PART_OPTION_ARCHIVE_SHOW') ?>
                    </span>
                <?php endif; ?>
            <?php else : ?>
                <div class="alert alert-info">
                    <?= Text::_('COM_HYPERPC_PART_OPTIONS_NOT_FOUND') ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
