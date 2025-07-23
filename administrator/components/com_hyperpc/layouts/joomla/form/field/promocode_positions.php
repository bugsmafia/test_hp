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
 * @author      Sergey Kalistratov <kalistratov.s.m@gmail.com>
 * @var         array $displayData
 */

defined('_JEXEC') or die('Restricted access');

use HYPERPC\App;
use JBZoo\Data\Data;
use Joomla\CMS\Language\Text;

$app = App::getInstance();

$deleteIconUrl = $app['path']->url('img:icons/delete.png', false);
$partListLink  = $app['helper']['route']->url([
    'view'   => 'positions',
    'layout' => 'modal',
    'tmpl'   => 'component'
]);

$data      = new Data($displayData);
$value     = $data->get('value', []);
$fieldName = $data->get('name');
?>
<div class="jsPromoCodePositions field-related-parts">
    <ul class="jsItemWrapper list-group">
        <?php if (is_array($value)) : ?>
            <?php foreach ((array) $value as $id => $name) :
                $imgAttrs = [
                    'data-id'   => $id,
                    'src'       => $deleteIconUrl,
                    'class'     => 'jsDeleteItem hasTooltip',
                    'title'     => Text::_('COM_HYPERPC_FIELD_PROMO_CODE_POSITION_REMOVE_ITEM')
                ];
                ?>
                <li class="list-group-item" data-id="<?= $id ?>">
                    <a href="#" class="li-link">
                        <?= $name ?>
                        <img <?= $app['helper']['html']->buildAttrs($imgAttrs) ?>/>
                    </a>
                    <input type="hidden" name="<?= $fieldName ?>[<?= $id ?>]" value="<?= $name ?>" />
                </li>
            <?php endforeach; ?>
        <?php endif; ?>
    </ul>
    <a data-type="iframe" data-src="<?= $partListLink ?>" href="javascript:;" class="btn jsAddItem">
        <span class="icon-save-new"></span>
        <?= Text::_('COM_HYPERPC_FIELD_PROMO_CODE_POSITION_ADD_ITEM') ?>
    </a>
</div>
