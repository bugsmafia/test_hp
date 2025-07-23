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
use Joomla\Filesystem\Path;

/**
 * @var \ElementConfigurationActionsCommercialProposal $this
 * @var array $htmlBlocks
 */

$assetsPath = $this->getPath('assets');
$fontsPath = JPATH_ROOT . '/media/hyperpc/fonts';
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Text::_('COM_HYPERPC_COMMERCIAL_PROPOSAL') ?></title>
    <style>
        @font-face {
            font-family: 'Inter';
            src: url('<?= Path::clean($fontsPath . '/inter/inter-regular.ttf') ?>') format('truetype');
            font-weight: 400;
            font-style: normal;
        }

        @font-face {
            font-family: 'Inter';
            src: url('<?= Path::clean($fontsPath . '/inter/inter-medium.ttf') ?>') format('truetype');
            font-weight: 500;
            font-style: normal;
        }

        @font-face {
            font-family: 'Inter';
            src: url('<?= Path::clean($fontsPath . '/inter/inter-semibold.ttf') ?>') format('truetype');
            font-weight: 600;
            font-style: normal;
        }
    </style>
    <link rel="stylesheet" href="<?= Path::clean($assetsPath . '/css/2025.css') ?>">
</head>

<body marginwidth="0" marginheight="0">
    Hi there! I'm a new pdf template
</body>
</html>
