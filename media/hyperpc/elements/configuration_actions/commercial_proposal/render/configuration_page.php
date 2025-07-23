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

use Joomla\CMS\Language\Text;
use HYPERPC\Helper\RenderHelper;
use HYPERPC\Html\Data\Product\Specification;
use HYPERPC\Joomla\Model\Entity\SaveConfiguration;
use HYPERPC\Joomla\Model\Entity\Interfaces\ProductMarker;

/**
 * @var         RenderHelper        $this
 * @var         ProductMarker       $product
 * @var         SaveConfiguration   $configuration
 */

if (!isset($product)) {
    $product = $configuration->getProduct();
}

$specification = new Specification($product, false, true);
$rootGroups    = $specification->getSpecification()['rootGroups'];

$rowsOnPage = 36;
$pages = [];

$currentRowsCount = 0;
$page = 0;

foreach ($rootGroups as $rootGroup) {
    $partsCount = 0;
    foreach ($rootGroup['groups'] as $group) {
        $partsCount += count($group['parts']);
    }

    if ($partsCount === 0) {
        continue;
    }

    $currentRowsCount += 1.5; // root group title height
    $currentRowsCount += $partsCount;
    if ($currentRowsCount <= $rowsOnPage) {
        if (!isset($pages[$page])) {
            $pages[$page] = [];
        }
        $pages[$page][] = $rootGroup;
        continue;
    }

    $page++;
    $pages[$page] = [$rootGroup];
    $currentRowsCount = 1.5; // root group title height
    $currentRowsCount += $partsCount;
}
?>

<?php foreach ($pages as $pageGroups) : ?>
    <div class="pdf-page">
        <h2 class="pdf-page__heading">
            <?= Text::sprintf('COM_HYPERPC_YOUR_COMPLECTATION', '<br>'); ?>
        </h2>

        <?= $this->hyper['helper']['render']->render('pdf/configuration_table', [
            'rootGroups' => $pageGroups
        ]); ?>

        <?= $this->hyper['helper']['render']->render('pdf/footer.php'); ?>
    </div>
<?php endforeach;
