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
 * @var         Requisite           $requisite
 * @var         string              $errorMessage
 * @var         SaveConfiguration   $configuration
 */

$configuration  = $this->getConfiguration();
$product        = $configuration->getProduct();
$assetsPath     = $this->getPath('assets');
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
<?= $this->hyper['helper']['render']->render('pdf/header.php', [
    'heading' => Text::_('COM_HYPERPC_COMMERCIAL_PROPOSAL')
]); ?>

<?= $this->hyper['helper']['render']->render('pdf/footer.php'); ?>
<div class="page">
    <h1 class="pdf-text-center">
        <?= $product->name ?>
    </h1>
    <h2 class="pdf-text-primary pdf-text-normal">
        <?= Text::_('NOTICE') . ': ' . $errorMessage  ?>
    </h2>
</div>

<hr class="pdf-pagebreak">

<div class="page">
    <div style="height: 420px"></div>

    <div style="height: 130px"></div>

    <p class="pdf-text-medium">
        <?= $requisite->name ?><br />
        <?= $requisite->legal_address ?><br />
        <?= $this->hyper['params']->get('schedule_string', Text::_('COM_HYPERPC_ORDER_PICKUP_STORE_WORKING_HOURS')) ?><br />
        <?= sprintf('Телефоны: %s', $requisite->phones) ?><br />

        <?= Text::_('COM_HYPERPC_FOLLOW_US_PDF') ?><br />
        <a href="https://vk.com">VK</a><br />
        <a href="https://facebook.com">Facebook</a><br />
        <a href="https://instagram.com">Instagram</a><br />
        <a href="https://youtube.com">YouTube</a>
    </p>

</div>
</body>

</html>
