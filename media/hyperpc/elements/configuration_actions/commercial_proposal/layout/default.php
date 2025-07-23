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

use JBZoo\Utils\FS;
use HYPERPC\Data\JSON;
use Joomla\Filesystem\Path;
use Joomla\CMS\Language\Text;
use HYPERPC\Printer\Configurator\Printer;
use HYPERPC\Joomla\Model\Entity\Requisite;
use HYPERPC\Joomla\Model\Entity\SaveConfiguration;
use HYPERPC\Joomla\Model\Entity\Interfaces\PartMarker;
use HYPERPC\Joomla\Model\Entity\Interfaces\CategoryMarker;
use ElementConfigurationActionsCommercialProposal as Element;

/**
 * @var         Element             $this
 * @var         PartMarker          $part
 * @var         CategoryMarker      $group
 * @var         Printer             $printer
 * @var         JSON                $amoLead
 * @var         Requisite           $requisite
 * @var         JSON                $amoContact
 * @var         array               $htmlBlocks
 * @var         SaveConfiguration   $configuration
 */

$configuration      = $this->getConfiguration();
$product            = $configuration->getProduct();
$assetsPath         = $this->getPath('assets');

$issueFieldValue    = $this->getIssue($amoLead);
$purposeFieldValue  = $this->getPurchasePurpose($amoLead);
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Text::_('COM_HYPERPC_COMMERCIAL_PROPOSAL') ?></title>
    <link rel="stylesheet" href="<?= $this->hyper['path']->get('media:hyperpc/css/play.css') ?>">
    <link rel="stylesheet" href="<?= FS::clean($assetsPath . '/css/default.css') ?>">
</head>

<body marginwidth="0" marginheight="0">

<div class="pdf-page">

    <div style="margin: -1.5cm -1cm 0 -2cm">
        <div class="pdf-section-dark">
            <div style="padding: 1cm 0 0 1cm">
                <img src="<?= $this->hyper['path']->get('media:hyperpc/img/pdf/hyperpc-logo-pdf-white.png') ?>" alt="" width="200">
            </div>

            <div class="pdf-text-center">
                <?php $imgSrc = $this->hyper['helper']['cart']->getItemImage($product, 0, 450); ?>
                <img src="<?= Path::clean(JPATH_ROOT . $imgSrc) ?>" alt="" >
            </div>
        </div>

        <div class="pdf-block-asymmetric pdf-section-primary pdf-product-title">
            <h1 class="pdf-margin-remove">
                <?= $product->name ?>
            </h1>
        </div>
    </div>

    <h2>
        <!-- Ваш идеальный компьютер для {для чего} -->
        <?php
        echo $this->hyper['helper']['macros']
                ->set('username', $configuration->params->get('username'))
                ->text($this->getGradationText($product));
        ?>
    </h2>

    <?php if (!empty($purposeFieldValue) || !empty($issueFieldValue)) : ?>
        <div style="position: absolute; bottom: 1em">
            <h2>
                <?= Text::_('COM_HYPERPC_COMMERCIAL_PROPOSAL_CREATE_YOR_PC_IN_A_FEW_DAYS') ?>:
            </h2>

            <table class="pdf-table-layout" style="font-size: 15pt;">
                <tbody>
                    <?php if (!empty($purposeFieldValue)) : ?>
                        <tr>
                            <td style="padding-right: 15px; width: 1%">
                                <img src="<?= $this->hyper['path']->get('media:hyperpc/img/pdf/list-mark-star-light.png') ?>" alt="" width="15" style="margin-top: 5px">
                            </td>
                            <td style="padding-bottom: 1em">
                                <!-- Поможет {цель покупки} -->
                                <?= Text::sprintf('COM_HYPERPC_COMMERCIAL_PROPOSAL_WILL_HELP', $purposeFieldValue) ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                    <?php if (!empty($issueFieldValue)) : ?>
                        <tr>
                            <td style="padding-right: 15px; width: 1%">
                                <img src="<?= $this->hyper['path']->get('media:hyperpc/img/pdf/list-mark-star-light.png') ?>" alt="" width="15" style="margin-top: 5px">
                            </td>
                            <td>
                                <!-- Решит проблему {проблема} -->
                                <?= Text::sprintf('COM_HYPERPC_COMMERCIAL_PROPOSAL_WILL_SOLVE_THE_PROBLEM', $issueFieldValue) ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

</div>

<hr class="pdf-pagebreak">

<?php
$rendererPath = 'configuration_actions/commercial_proposal/render';

$macrosData = [
    'configuration_page' => $this->hyper['helper']['render']->render($rendererPath . '/configuration_page', [
        'product'       => $product,
        'configuration' => $configuration
    ], 'elements'),

    'items_total' => $this->hyper['helper']['render']->render($rendererPath . '/items_table', [
        'product'       => $product,
        'configuration' => $configuration
    ], 'elements'),

    'manager' => $this->hyper['helper']['render']->render($rendererPath . '/manager_contacts', [
        'amoContact' => $amoContact
    ], 'elements')
];

$blocksCount = count($htmlBlocks);
for ($i = 0; $i < $blocksCount; $i++) :
    $blockText = trim($htmlBlocks[$i]->introtext);
    $blockText = preg_replace('/"(\/images\/.+?)"/', '"' . JPATH_ROOT . '$1"', $blockText);
    $blockText = preg_replace('/"(images\/.+?)"/', '"' . JPATH_ROOT . '/$1"', $blockText);

    $blockText = $this->hyper['helper']['macros']
        ->setData($macrosData)
        ->text($blockText);
    ?>
    <?php if ($i !== $blocksCount - 1) : ?>
        <?= $blockText ?>
        <hr class="pdf-pagebreak">
    <?php else : ?>
        <?= $blockText . '</body></html>' ?>
    <?php endif; ?>
<?php endfor;
