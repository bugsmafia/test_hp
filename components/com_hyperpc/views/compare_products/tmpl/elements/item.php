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
 * @author      Roman Evsyukov
 */

defined('_JEXEC') or die('Restricted access');

use HYPERPC\Data\JSON;
use Joomla\CMS\Language\Text;
use HYPERPC\Joomla\Model\Entity\Interfaces\Stockable;

/**
 * @var     JSON    $itemKey
 * @var     JSON    $itemData
 * @var     string  $itemPrice
 */

$imgWidth = $this->hyper['params']->get('product_img_teaser_width', 450);
$imgWidth = $this->hyper['params']->get('product_img_teaser_height', 450);
?>

<td data-part-id="<?= $itemKey ?>">
    <a href="<?= $itemData->get('url') ?>" class="uk-display-inline-block uk-background-cover" target="_blank" style="background-image: url(<?= $itemData->get('image') ?>)">
        <canvas width="<?= $imgWidth ?>" height="<?= $imgWidth ?>"></canvas>
    </a>

    <div class="uk-margin-top">
        <?php if ($itemData->get('availability')) :
            $textClass = ' uk-text-';
            switch ($itemData->get('availability')) {
                case Stockable::AVAILABILITY_INSTOCK:
                    $textClass .= 'success';
                    break;
                case Stockable::AVAILABILITY_PREORDER:
                case Stockable::AVAILABILITY_OUTOFSTOCK:
                    $textClass .= 'warning';
                    break;
                case Stockable::AVAILABILITY_DISCONTINUED:
                    $textClass .= 'danger';
                    break;
                default:
                    $textClass .= 'muted';
                    break;
            }
            ?>
            <div class="<?= $textClass ?>">
                <?= Text::_('COM_HYPERPC_AVAILABILITY_LABEL_' . strtoupper($itemData->get('availability'))) ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="uk-flex uk-flex-between">
        <div class="uk-text-emphasis uk-text-medium">
            <a href="<?= $itemData->get('url') ?>" class="uk-link-reset" target="_blank">
                <?= $itemData->get('name') ?>
            </a>
        </div>
        <?php
        $removeAttrs = [
            'class' => 'jsRemoveCompareItem uk-flex-none uk-margin-small-left uk-text-danger',
            'data'  => [
                'id'   => $itemKey,
                'type' => $itemData->get('type'),
            ],
            'uk-icon' => 'icon: trash;'
        ];

        if ($itemData->get('in-stock')) {
            $removeAttrs['data']['in-stock'] = $itemData->get('in-stock');
        }
        ?>
        <a <?= $this->hyper['helper']['html']->buildAttrs($removeAttrs) ?>></a>
    </div>
    <?php if ($itemPrice) : ?>
        <?= Text::sprintf('COM_HYPERPC_PRODUCT_PRICE', $itemPrice) ?>
    <?php endif; ?>
</td>
