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

use HYPERPC\Money\Type\Money;
use Joomla\CMS\Language\Text;
use HYPERPC\Elements\Element;
use Joomla\CMS\HTML\HTMLHelper;
use HYPERPC\Helper\MoyskladVariantHelper;
use HYPERPC\Joomla\Model\Entity\MoyskladPart;
use HYPERPC\Helper\HtmlHelper as HPHtmlHelper;
use HYPERPC\Joomla\Model\Entity\MoyskladProduct;
use HYPERPC\Joomla\Model\Entity\MoyskladVariant;

/**
 * @var         HyperPcViewOrder    $this
 * @var         MoyskladPart        $cPart
 * @var         Element             $element
 */

$formAction = $this->hyper['helper']['route']->url([
    'view'     => 'order',
    'layout'   => 'edit',
    'id'       => $this->hyper['input']->get('id', 0)
]);

/** @var MoyskladVariantHelper */
$moyskladVariantHelper = $this->hyper['helper']['moyskladVariant'];

/** @var HPHtmlHelper */
$htmlHelper = $this->hyper['helper']['html'];

$parts      = $this->order->getParts();
$elements   = $this->order->getElements();
$products   = $this->order->getProducts(false, 'a.id ASC', true);
$positions  = $this->order->getPositions(false, 'a.id ASC', true);
$partOrder  = $this->hyper['params']->get('product_teaser_parts_order', 'a.product_folder_id ASC');
$delivery   = $this->order->getDelivery();
$worker     = $this->order->getWorker();
$variants   = $moyskladVariantHelper->getVariants();
?>
<div class="hp-wrapper-form">
    <form action="<?= $formAction ?>" method="post" name="adminForm" id="adminForm">
        <div class="row">
            <div class="col-12">
                <?= $this->order->getRender()->statusHistory() ?>
            </div>
        </div>
        <?php if ($this->order->isCredit()) : ?>
            <div class="row">
                <div class="col-12">
                    <?= $this->order->getRender()->creditStatusHistory() ?>
                </div>
            </div>
        <?php endif; ?>

        <?= HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'general']); ?>
        <?= HTMLHelper::_('uitab.addTab', 'myTab', 'general', Text::_('COM_HYPERPC_SIDEBAR_ORDER')) ?>
        <div class="row">
            <div class="col-12 col-lg-8">
                <div class="page-header">
                    <h3>
                        <?= Text::sprintf('COM_HYPERPC_ORDER_HEADER', $this->order->getName()) ?>
                        (<?= Text::_('COM_HYPERPC_ORDER_IS_COMPANY_' . $this->order->getBuyerOrderType()) ?>)
                    </h3>
                    <?= Text::_('COM_HYPERPC_ORDER_LINK_TO_SITE_VIEW') ?>:
                    <a href="<?= '/' . trim(str_replace('/administrator', '', $this->order->getViewUrl()), '/') ?>" target="_blank">
                        <?= '/' . trim(str_replace('/administrator', '', $this->order->getViewUrl()), '/') ?>
                    </a>
                    <?php
                    $amoLeadUrl = $this->order->getAmoLeadUrl();
                    if ($amoLeadUrl) : ?>
                        <br />
                        <?= Text::_('COM_HYPERPC_ORDER_LINK_TO_AMO_VIEW') ?>:
                        <a href="<?= $amoLeadUrl ?>" target="_blank">
                            <?= $amoLeadUrl ?>
                        </a>
                    <?php endif; ?>
                    <?php
                    $moyskladEditUrl = $this->order->getMoyskladEditUrl();
                    if ($moyskladEditUrl) : ?>
                        <br />
                        <?= Text::_('COM_HYPERPC_ORDER_LINK_TO_MOYSKLAD_VIEW') ?>:
                        <a href="<?= $moyskladEditUrl ?>" target="_blank">
                            <?= $moyskladEditUrl ?>
                        </a>
                    <?php endif; ?>
                    <?php if ($this->hyper->isDevUser()) : ?>
                        <br />
                        <a href="index.php?option=com_users&task=user.edit&id=<?= $this->order->created_user_id ?>" target="_blank">
                            <?= Text::_('COM_HYPERPC_ORDER_VIEW_USER_TITLE') ?>
                        </a>
                    <?php endif; ?>
                </div>
                <div class="hp-order-items">
                    <table class="table table-striped table-hover align-middle">
                        <thead>
                        <tr>
                            <td width="45%">
                                <?= Text::_('COM_HYPERPC_ORDER_ITEM_TITLE') ?>
                            </td>
                            <td>
                                <?= Text::_('COM_HYPERPC_ORDER_ITEM_PRICE') ?>
                            </td>
                            <td>
                                <?= Text::_('COM_HYPERPC_ORDER_ITEM_QUANTITY_HEADING') ?>
                            </td>
                            <td>
                                <?= Text::_('COM_HYPERPC_ORDER_ITEM_TOTAL') ?>
                            </td>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (count($products)) : ?>
                            <tr class="separator">
                                <th colspan="4">
                                    <?= Text::_('COM_HYPERPC_ORDER_PRODUCTS_TITLE') ?>
                                </th>
                            </tr>
                            <?php foreach ($products as $product) : ?>
                                <tr>
                                    <td>
                                        <?= $product['name'] ?>
                                    </td>
                                    <td>
                                        <?php
                                        $price         = (new Money($product['price']));
                                        $priceWithRate = (new Money($product['priceWithRate']));
                                        if ($priceWithRate->val() != $price->val()) {
                                            echo '<s>' . $price->text() . '</s> / ';
                                            echo $priceWithRate->text();
                                        } else {
                                            echo $price->text();
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?= Text::sprintf('COM_HYPERPC_ORDER_ITEM_QUANTITY', $product['quantity']) ?>
                                    </td>
                                    <td>
                                        <?= $priceWithRate->multiply($product['quantity']) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <?php if (count($parts)) : ?>
                            <tr class="separator">
                                <th colspan="4">
                                    <?= Text::_('COM_HYPERPC_ORDER_PARTS_TITLE') ?>
                                </th>
                            </tr>
                            <?php foreach ($parts as $part) : ?>
                                <tr>
                                    <td>
                                        <?= $part['name'] ?>
                                    </td>
                                    <td>
                                        <?php
                                        $price         = (new Money($part['price']));
                                        $priceWithRate = (new Money($part['priceWithRate']));
                                        if ($priceWithRate->val() != $price->val()) {
                                            echo '<s>' . $price->text() . '</s> / ';
                                            echo $priceWithRate->text();
                                        } else {
                                            echo $price->text();
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?= Text::sprintf('COM_HYPERPC_ORDER_ITEM_QUANTITY', $part['quantity']) ?>
                                    </td>
                                    <td>
                                        <?= $priceWithRate->multiply($part['quantity']) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <?php if (count($positions)) : ?>
                            <tr class="separator">
                                <th colspan="4">
                                    <?= Text::_('COM_HYPERPC_ORDER_POSITIONS_TITLE') ?>
                                </th>
                            </tr>
                            <?php foreach ($positions as $position) :
                                $target  = null;
                                $fields  = [];
                                $editUrl = $position->getEditUrl();

                                $listPrice = $position->getListPrice();
                                $salePrice = $position->getSalePrice();
                                $hasPromo  = $listPrice->val() > $salePrice->val();
                                $linePrice = $salePrice->multiply($position->quantity, true);

                                $configParts = [];
                                if ($position instanceof MoyskladProduct) {
                                    $data = [
                                        'class' => 'jsToggleProductConfig',
                                        'data-id' => $position->id
                                    ];

                                    $configParts = $position->get('parts');
                                } else {
                                    $data = [
                                        'href' => $editUrl,
                                        'target' => '_blank'
                                    ];

                                    if (isset($position->option) && $position->option instanceof MoyskladVariant && $position->option->id) {
                                        $position->set('name', $position->name . ' (' . $position->option->name . ')');
                                        $target = $position->id . '-' . $position->option->id;
                                        $fields = $position->option->getFields();
                                    }
                                }

                                $data['title'] = $position->name;
                                ?>
                                <tr>
                                    <td>
                                        <a <?= $htmlHelper->buildAttrs($data) ?>>
                                            <?= $position->name ?>
                                        </a>
                                        <?php if ($position instanceof MoyskladProduct) :
                                            $viewUrl = $position->saved_configuration ? $position->getConfigUrl($position->saved_configuration) : $position->getViewUrl();
                                            ?>
                                            |
                                            <a href="<?= $this->hyper['helper']['route']->getSiteSefUrl($viewUrl) ?>"
                                               title="<?= $position->name ?>" target="_blank">
                                                <?= str_replace($position->name, '', $position->getName()) ?>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        if ($hasPromo) {
                                            echo '<s>' . $listPrice->text() . '</s> / ';
                                            echo $salePrice->text();
                                        } else {
                                            echo $listPrice->text();
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?= Text::sprintf('COM_HYPERPC_ORDER_ITEM_QUANTITY', $position->quantity) ?>
                                    </td>
                                    <td>
                                        <?= $linePrice->text() ?>
                                    </td>
                                </tr>
                                <?php foreach ($configParts as $cPart) :
                                    if ($cPart->isDetached()) {
                                        continue;
                                    }

                                    $partName     = $cPart->getConfiguratorName($position->id);
                                    $partVariants = $moyskladVariantHelper->getPartVariants($cPart->id, $variants);

                                    $partEditUrl = $cPart->getEditUrl();
                                    if (count($partVariants) > 0 && !empty($cPart->option)) {
                                        $option = $cPart->option;
                                        if ($option->id !== null) {
                                            $partEditUrl = $this->hyper['route']->build([
                                                'layout'            => 'edit',
                                                'view'              => 'moysklad_variant',
                                                'part_id'           => $cPart->id,
                                                'id'                => $option->id,
                                                'product_folder_id' => $cPart->product_folder_id
                                            ]);
                                            $partName .= sprintf(' (%s)', $option->getConfigurationName());
                                        }
                                    }
                                    ?>
                                    <tr class="hp-product-<?= $position->id ?>-config jsProductConfig" style="display: none;">
                                        <td>
                                            <span class="muted">┊&nbsp;&nbsp;&nbsp;</span>
                                            –&nbsp;
                                            <a href="<?= $partEditUrl ?>" target="_blank" title="<?= $partName ?>">
                                                <?= $partName ?>
                                            </a>
                                        </td>
                                        <td>
                                            <?php
                                            echo $cPart->getListPrice();
                                            if ($cPart->list_price->compare($cPart->get('original_price'), '!=')) {
                                                echo ' (сейчас ' . $cPart->get('original_price')->text() . ')';
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <?= Text::sprintf('COM_HYPERPC_ORDER_ITEM_QUANTITY', $cPart->quantity) ?>
                                        </td>
                                        <td>
                                            <?php
                                                /** @todo fix when discounts work */
                                                echo $cPart->getQuantityPrice(false)->text()
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                        <tfoot>
                        <tr>
                            <td colspan="3">
                                <div class="text-right">
                                    <?= Text::_('COM_HYPERPC_ORDER_DELIVERY_PRICE') ?>
                                    <br />
                                    <?= Text::_('COM_HYPERPC_ORDER_TOTAL_PRICE') ?>
                                </div>
                            </td>
                            <td>
                                <?php
                                $deliveryPrice = $delivery->getPrice();
                                if (!$deliveryPrice->isEmpty() && $deliveryPrice->val() > 0) {
                                    echo $deliveryPrice->text();
                                } else {
                                    echo sprintf('<strong>%s</strong>', Text::_('COM_HYPERPC_ORDER_DELIVERY_NOT_SETUP'));
                                }
                                ?>
                                <br />
                                <?= $this->order->calculateTotal()->add(max($deliveryPrice->val(), 0))->text() ?>
                            </td>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="col-12 col-lg-4">
                <div class="page-header">
                    <h3><?= Text::_('COM_HYPERPC_ORDER_STATUS') ?>: <?= $this->order->getStatus()->name ?></h3>
                    <?php
                    $worker = $this->order->getWorker();
                    if (!$worker->id) {
                        $worker->set('id', 1);
                        $worker->set('name', 'HYPERPC');
                    }
                    ?>
                    <?= Text::_('COM_HYPERPC_AMO_RESPONSIBLE_USER_LABEL') ?>:
                    <a href="<?= $worker->getViewUrl() ?>" target="_blank"><?= $worker->name ?></a>
                </div>
                <?php if (count($elements)) : ?>
                    <dl class="dl-horizontal">
                        <?php
                        foreach ($elements as $element) {
                            echo $element->renderAdmin();
                        }
                        ?>
                    </dl>
                <?php endif; ?>
                <?php if ($this->order->promo_code) : ?>
                    <div class="page-header">
                        <h3><?= Text::_('COM_HYPERPC_PROMO_CODE_TITLE') ?>: <?= $this->order->promo_code ?></h3>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?= HTMLHelper::_('uitab.endTab') ?>

        <?php
        if ($this->hyper->isDevUser()) {
            echo HTMLHelper::_('uitab.addTab', 'myTab', 'params', Text::_('COM_HYPERPC_JPARAMS_FIELDSET_LABEL'));
            echo $this->form->renderField('id');
            echo $this->form->renderField('created_user_id');
            echo HTMLHelper::_('uitab.endTab');
        }
        ?>

        <?= HTMLHelper::_('uitab.endTabSet') ?>

        <input type="hidden" name="task" />
        <?= HTMLHelper::_('form.token'); ?>
    </form>
</div>
