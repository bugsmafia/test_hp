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
 * @author      Roman Evsyukov
 * @author      Artem Vyshnevskiy
 *
 * @var         array $htmlBlocks
 */

use JBZoo\Utils\FS;
use Joomla\CMS\Language\Text;

$assetsPath = $this->getPath('assets');

$blocksCount = count($htmlBlocks);
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

<?php
for ($i = 0; $i < $blocksCount; $i++) :
    $blockText = trim($htmlBlocks[$i]->introtext);
    $blockText = preg_replace('/"(\/images\/.+?)"/', '"' . JPATH_ROOT . '$1"', $blockText);
    $blockText = preg_replace('/"(images\/.+?)"/', '"' . JPATH_ROOT . '/$1"', $blockText);
    ?>
    <?php if ($i !== $blocksCount - 1) : ?>
        <?= $blockText ?>
        <hr class="pdf-pagebreak">
    <?php else : ?>
        <?= $blockText . '</body></html>' ?>
    <?php endif; ?>
<?php endfor;
