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

use HYPERPC\Helper\FpsHelper;
use HYPERPC\Helper\GameHelper;
use HYPERPC\Helper\RenderHelper;
use Joomla\CMS\Language\Text;

/**
 * @var RenderHelper    $this
 * @var array           $productFps
 * @var string          $active
 */

if (!isset($active)) {
    $active = '';
}

/** @var FpsHelper */
$fpsHelper = $this->hyper['helper']['fps'];
/** @var GameHelper */
$gameHelper = $this->hyper['helper']['game'];

$productFps = array_filter($productFps);
?>

<?php if (count($productFps)) :
    $games       = count($gameHelper->getDefaultGames()) ? $gameHelper->getDefaultGames() : $gameHelper->getGames();
    $fpsTopLimit = $fpsHelper->getFpsTopLimit();

    $resolutions = $fpsHelper->getResolutions();

    $axisItems = [];
    for ($i = 0; $i < $fpsTopLimit; $i += 50) {
        $axisItems[] = $i;
    }
    $axisItems[] = 'FPS';

    $axisStep = (100 / (count($axisItems) - 1)) . '%';

    list($activeGame, $activeResolution) = explode('@', $active . '@')
    ?>
    <table class="uk-table uk-table-small uk-table-justify uk-table-divider uk-table-middle tm-fps-table" data-uk-scrollspy="target: .tm-fps-table__bar; delay: 33; repeat: false">
        <tr>
            <th class="uk-visible@s uk-table-shrink"></th>
            <td class="uk-table-expand uk-padding-remove-left">
                <div class="uk-grid">
                    <?php foreach ($resolutions as $resolution) : ?>
                        <div class="uk-flex uk-flex-middle">
                            <span class="tm-fps-table__color-sample tm-fps-table__color-sample--<?= $resolution ?> uk-badge uk-margin-small-right"></span>
                            <span>
                                <?= Text::_('COM_HYPERPC_FPS_RESOLUTION_' . strtoupper($resolution)) ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </td>
        </tr>

        <?php
        foreach ($games as $game) :
            if (!array_key_exists($game->alias, $productFps)) {
                continue;
            }

            $gameFps = $productFps[$game->alias]['ultra'];
            ?>
            <tr class="<?= $activeGame === $game->alias ? 'uk-active' : '' ?>">
                <td class="uk-text-right uk-text-nowrap uk-visible@s"><?= $game->name ?></td>
                <td class="tm-fps-table__fps-cell" style="background-size: <?= $axisStep ?>">
                    <div class="uk-hidden@s"><?= $game->name ?></div>
                    <?php foreach ($gameFps as $resolution => $fpsValue) :
                        $percentValue = min(100, $fpsValue / ($fpsTopLimit * 0.01));
                        ?>
                        <div class="tm-fps-table__bar tm-fps-table__bar--<?= $resolution ?>" style="width: <?= $percentValue ?>%">
                            <span class="tm-fps-table__fps-value">
                                <?= $fpsValue ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </td>
            </tr>
        <?php endforeach; ?>

        <tr>
            <th class="uk-visible@s"></th>
            <td class="tm-fps-table__fps-cell" style="background-size: <?= $axisStep ?>">
                <div class="tm-fps-table__axis uk-flex uk-flex-between">
                    <?php
                    foreach ($axisItems as $item) : ?>
                        <div><?= $item ?></div>
                    <?php endforeach; ?>
                </div>
            </td>
        </tr>
    </table>
<?php endif;
