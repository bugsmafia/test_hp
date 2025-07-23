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

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use HYPERPC\Helper\RenderHelper;
use HYPERPC\Printer\Configurator\Printer;
use HYPERPC\Joomla\Model\Entity\Requisite;
use HYPERPC\Html\Data\Product\Specification;
use HYPERPC\Joomla\Model\Entity\SaveConfiguration;

/**
 * @var         RenderHelper        $this
 * @var         Printer             $printer
 * @var         Requisite           $requisite
 * @var         SaveConfiguration   $configuration
 */

$product     = $configuration->getProduct();
$siteContext = $this->hyper['params']->get('site_context', HP_CONTEXT_HYPERPC);
$totalPrice  = $configuration->getDiscountedPrice();
$vat         = $this->hyper['helper']['money']->getVat($totalPrice);

$specification = new Specification($product);
$rootGroups    = $specification->getSpecification()['rootGroups'];

$lang = Factory::getApplication()->getLanguage();
?>
<!doctype html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $printer->getPageTitle() ?></title>

    <?php if ($lang->isRtl()) : ?>
        <style>
            body { font-family: 'DejaVu Sans', sans-serif; }
        </style>
    <?php else : ?>
        <link rel="stylesheet" href="<?= $this->hyper['path']->get('media:hyperpc/css/play.css') ?>">
        <style>
            body { font-family: 'Play', 'DejaVu Sans', sans-serif; }
        </style>
    <?php endif; ?>

    <link href="<?= JPATH_ROOT ?>/administrator/components/com_hyperpc/framework/Printer/Configurator/assets/css/styles.css" rel="stylesheet"/>
</head>
<body class="hp-pdf">

<table class="hp-table-header" width="100%">
    <tr>
        <td width="40%">
            <img class="hp-pdf-logo" src="<?= JPATH_ROOT ?>/media/hyperpc/img/logos/<?= $siteContext ?>-logo-pdf.png"/>
        </td>
        <td width="60%" class="hp-text-right">
            <ul class="unstyled hp-pdf-address-list">
                <li><strong><?= $requisite->name ?></strong></li>
                <li><?= Text::_('COM_HYPERPC_PHONES') . ': ' . $requisite->phones ?></li>
                <li>
                    <?= Text::_('COM_HYPERPC_ORDER_PICKUP_STORE_WORKING_HOURS_HEADING') ?>:
                    <?= $this->hyper['params']->get('schedule_string', Text::_('COM_HYPERPC_ORDER_PICKUP_STORE_WORKING_HOURS')) ?>
                </li>
                <li><?= Text::_('COM_HYPERPC_ADDRESS') . ': ' . $requisite->legal_address ?></li>
            </ul>
        </td>
    </tr>
</table>
<div class="hp-text-right">
    <?= $printer->getSiteLink() ?>
</div>
<h2 class="hp-config-title">
    <?= $product->name ?>
    (<?= Text::sprintf('COM_HYPERPC_CONFIGURATION_PDF_NUMBER', sprintf('%06d', $configuration->id)) ?>)
</h2>
<table class="hp-table-content">
    <?php foreach ($rootGroups as $rootGroup) : ?>
        <tr class="hp-table-separator">
            <td>
                <?= $rootGroup['title'] ?>
            </td>
        </tr>
        <?php
        $i = 0;
        foreach ($rootGroup['groups'] as $group) :
            $i++;
            $j = 0;
            ?>
            <?php foreach ($group['parts'] as $part) :
                $j++;
                $quantity = $part['quantity'] > 1 ? $part['quantity'] . ' x ' : '';
                $isLast = $i === count($rootGroup['groups']) && $j === count($group['parts']);
                $partName = $quantity . $part['partName'] . (isset($part['optionName']) ? ' ' . $part['optionName'] : '');
                ?>
                <tr class="hp-table-parts<?= ($isLast) ? ' hp-is-last' : '' ?>">
                    <td>
                        <span class="hp-group-title"><?= $group['title'] ?>: </span>
                        <?= $partName ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endforeach; ?>
    <?php endforeach; ?>
</table>
<p class="hp-text-right hp-config-price">
    <strong>
        <?= Text::_('COM_HYPERPC_PRICE') ?>: <?= $totalPrice->text() ?>
    </strong>
    <?php if ($vat->val() > 0) : ?>
        <span class="hp-text-small"><?= Text::_('COM_HYPERPC_INCLUDES_VAT') ?> <?= $vat->text() ?></span>
    <?php endif; ?>
</p>
<p>
    <?= Text::_('COM_HYPERPC_PDF_PRICE_DISCLAIMER') ?>
    <?= HTMLHelper::date($configuration->getLastModifiedDate(), Text::_('DATE_FORMAT_LC5')) ?>
</p>
<p class="hp-config-link">
    <?= Text::_('COM_HYPERPC_CONFIGURATION_LINK') ?>
    <a href="<?= $printer->getConfigLink() ?>" target="_blank" class="hp-link"
       title="<?= Text::_('COM_HYPERPC_GO_TO_CONFIGURATION_PAGE') ?> #<?= sprintf('%06d', $configuration->id) ?>">
        <?= $printer->getConfigLink() ?>
    </a>
</p>
</body>
</html>
