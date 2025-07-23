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

defined('_JEXEC') or die('Restricted access');

use JBZoo\Data\Data;
use Joomla\CMS\Language\Text;

/**
 * @var Data $dimensions
 */
?>
<div class="uk-margin"><div class="uk-grid uk-flex-middle" data-uk-grid-margin="">
    <?php if ($dimensions->get('image') !== '') : ?>
        <div class="uk-width-medium-3-5 uk-text-center uk-row-first">
            <img src="<?= $dimensions->get('image') ?>" alt="<?= $dimensions->get('image_alt') ?>">
        </div>
    <?php endif; ?>
    <div class="uk-width-medium-2-5">
        <h2><?= Text::_('COM_HYPERPC_TAB_SIZE_WEIGHT') ?></h2>

        <?php if (!empty($dimensions->get('height'))) : ?>
            <div class="uk-flex uk-margin">
                <div style="width: 130px;"><?= Text::_('COM_HYPERPC_HEIGHT') ?>:</div>
                <div>
                    <strong><?= $dimensions->get('height') ?></strong>&nbsp;
                    <?= Text::_('COM_HYEPRPC_WEIGHT_CM') ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($dimensions->get('width'))) : ?>
            <div class="uk-flex uk-margin">
                <div style="width: 130px;"><?= Text::_('COM_HYPERPC_WIDTH') ?>:</div>
                <div>
                    <strong><?= $dimensions->get('width') ?></strong>&nbsp;
                    <?= Text::_('COM_HYEPRPC_WEIGHT_CM') ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($dimensions->get('depth'))) : ?>
            <div class="uk-flex uk-margin">
                <div style="width: 130px;"><?= Text::_('COM_HYPERPC_DEPTH') ?>:</div>
                <div>
                    <strong><?= $dimensions->get('depth') ?></strong>&nbsp;
                    <?= Text::_('COM_HYEPRPC_WEIGHT_CM') ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($dimensions->get('weight'))) : ?>
            <div class="uk-flex uk-margin">
                <div style="width: 130px;"><?= Text::_('COM_HYPERPC_WEIGHT') ?>:</div>
                <div>
                    <strong><?= $dimensions->get('weight') ?></strong>&nbsp;
                    <?= Text::_('COM_HYEPRPC_WEIGHT_KG') ?><sup>1</sup>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<hr class="uk-margin-large"></div>
