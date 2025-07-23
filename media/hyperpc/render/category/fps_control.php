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
 *
 * @var         \HYPERPC\Helper\RenderHelper $this
 * @var         string                       $default
 */

use JBZoo\Utils\Url;
use Joomla\CMS\Language\Text;
use HYPERPC\Helper\ImageHelper;

defined('_JEXEC') or die('Restricted access');

$games   = $this->hyper['helper']['game']->getGames();
$default = isset($default) ? $default : '';
?>

<div class="uk-container uk-container-large">
    <hr class="tm-divider uk-margin-medium-top">
    <div class="jsFpsControl uk-container uk-container-small">
        <div class="uk-flex uk-flex-bottom">
            <div>
                <div class="uk-h3 uk-text-normal">
                    <?= text::_('COM_HYPERPC_FPS_TITLE') ?>
                </div>
                <div class="uk-grid uk-grid-small" uk-margin>
                    <div>
                        <select class="jsFpsGameSelect uk-select" style="width: 250px">
                            <?php foreach ($games as $game) :
                                $selected = $game->alias === $default;
                                ?>
                                <option
                                    value="<?= $game->alias ?>"
                                    data-img="<?= '/' . Url::pathToRel($game->params->get('image', '', 'hpimagepath')) ?>"
                                    <?= $selected ? 'selected' : '' ?>
                                    >
                                    <?= $game->name ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <select class="jsFpsResolutionSelect uk-select" style="width: 250px">
                            <?php
                            $resolutions = $this->hyper['helper']['fps']->getResolutions();
                            foreach ($resolutions as $resolution) : ?>
                                <option value="<?= $resolution ?>">
                                    <?= Text::_('COM_HYPERPC_FPS_RESOLUTION_' . strtoupper($resolution)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <p class="uk-text-small uk-text-muted">
                    <?= Text::_('COM_HYPERPC_FPS_DISCLAIMER') ?>
                    <?php if (!empty($this->hyper['params']->get('fps_info_article'))) : ?>
                        <a href="<?= $this->hyper['params']->get('fps_info_article') ?>" target="_blank" class="jsLoadIframe">
                            <?= Text::_('COM_HYPERPC_DETAILS') ?>
                        </a>
                    <?php endif; ?>
                </p>
            </div>
            <div class="uk-flex-none uk-margin-small-left uk-visible@s">
                <img src="<?= ImageHelper::TRANSPARENT_PIXEL ?>" alt="" width='250' height='189' class="jsFpsGameImg">
            </div>
        </div>
    </div>
    <hr class="tm-divider uk-margin-remove-top">
</div>
