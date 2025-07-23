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
 *
 * @var         array $displayData
 * @var         JFormFieldPromoCode $field
 */

use Joomla\CMS\Language\Text;

defined('_JEXEC') or die('Restricted access');

$field = $displayData['field'];

$inputAttributes = [
    'type'  => 'text',
    'id'    => $displayData['id'],
    'name'  => $displayData['name'],
    'value' => $this->escape($displayData['value'])
];

if (isset($displayData['hint'])) {
    $inputAttributes['placeholder'] = $displayData['hint'];
}
?>

<div class="input-append">
    <input <?= $field->hyper['helper']['html']->buildAttrs($inputAttributes) ?> />
    <a href="#" class="btn btn-primary jsCreatePassword hasTooltip" title="<?= Text::_('COM_HYPERPC_UPDATE_PROMO_CODE') ?>">
        <span class="icon-loop"></span>
    </a>
</div>

<script>
    jQuery(function($){
        $('.jsCreatePassword').click('on', function (e) {
            var text     = "",
                possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

            for (var i = 0; i < 7; i++) {
                text += possible.charAt(Math.floor(Math.random() * possible.length));
            }

            $('#<?= $displayData['id'] ?>').val(text);

            e.preventDefault();
        });
    });
</script>
