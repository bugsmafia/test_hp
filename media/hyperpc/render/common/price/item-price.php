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

use HYPERPC\Money\Type\Money;
use Joomla\CMS\Language\Text;
use HYPERPC\Helper\RenderHelper;
use HYPERPC\Joomla\Model\Entity\Entity;
use HYPERPC\Joomla\Model\Entity\Interfaces\OptionMarker;

/**
 * @var         RenderHelper    $this
 * @var         Money           $price
 * @var         Entity          $entity
 * @var         boolean         $htmlPrices
 */

$htmlPrices = isset($htmlPrices) ? $htmlPrices : false;

$creditEnabled = $this->hyper['params']->get('credit_enable', '0');
if (isset($entity)) {
    $id   = $entity->id;
    $type = 'position';

    $args = [
        'view'  => 'credit_calculator',
        'type'  => $type,
        'id'    => $id,
        'tmpl'  => 'component',
        'price' => $price->val()
    ];

    if (isset($entity->saved_configuration) && !empty($entity->saved_configuration)) {
        $args['configuration_id'] = $entity->saved_configuration;
    }

    if (isset($entity->option) && $entity->option instanceof OptionMarker && !empty($entity->option->id)) {
        
        $args['option_id'] = $entity->option->id;
    }

    $creditUrl = $this->hyper['route']->build($args);
}

$vat = $this->hyper['helper']['money']->getVat($price);
?>

<div class="hp-item-price">
    <div class="hp-item-price__price uk-text-emphasis">
        <span class="<?= $htmlPrices ? 'jsItemPrice' : '' ?>">
            <?= Text::_('COM_HYPERPC_PRICE'); ?>
            <?php if ($htmlPrices) : ?>
                <?= $price->html() ?>
            <?php else : ?>
                <?= $price->text() ?>
            <?php endif; ?>
        </span>

        <?php if ($vat->val() > 0) : ?>
            <button type="button" class="hp-item-price__info uk-link-muted uk-icon">
                <?= $this->hyper['helper']['html']->svgIcon('info', 16) ?>
            </button>

            <div class="hp-item-price__info-drop uk-drop uk-card uk-card-default" uk-drop="mode: click; pos: top-center; offset: 12">
                <div class="uk-flex uk-flex-between">
                    <span><?= Text::_('COM_HYPERPC_TOTAL_COST') ?></span>
                    <?php if ($htmlPrices) : ?>
                        <span class="jsItemPrice"><?= $price->html() ?></span>
                    <?php else : ?>
                        <span><?= $price->text() ?></span>
                    <?php endif; ?>
                </div>
                <div class="uk-flex uk-flex-between">
                    <span><?= Text::_('COM_HYPERPC_INCLUDES_VAT') ?></span>
                    <?php if ($htmlPrices) : ?>
                        <span class="jsItemVat"><?= $vat->html() ?></span>
                    <?php else : ?>
                        <span><?= $vat->text() ?></span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <?php if ($creditEnabled) :
        $monthlyPayment = $this->hyper['helper']['credit']->getMonthlyPayment($price->val());
        $monthlyPaymentStr = $htmlPrices ? $monthlyPayment->html() : $monthlyPayment->text();
        ?>
        <div class="hp-item-price__monthly-payment uk-text-muted<?= $htmlPrices ? ' jsItemMonthlyPayment' : '' ?>">
            <?php if (isset($entity)) : ?>
                <a href="<?= $creditUrl ?>" class="jsLoadIframe tm-link-dashed uk-link-muted">
                    <?= Text::_('COM_HYPERPC_INSTALLMENT_CALCULATE_CREDIT') ?>
                </a>
            <?php else : ?>
                <?php if ($this->hyper['helper']['credit']->getDefaultCreditRate() > 0) : ?>
                    <?= Text::sprintf('COM_HYPERPC_CREDIT_MONTHLY_PAYMENT', $monthlyPaymentStr); ?>
                <?php else : ?>
                    <?= Text::sprintf('COM_HYPERPC_INSTALLMENT_MONTHLY_PAYMENT', $monthlyPaymentStr); ?>
                <?php endif; ?>
                <?= $this->render('common/price/credit-info'); ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
