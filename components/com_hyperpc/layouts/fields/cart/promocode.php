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
 * @var         Data $enterPromoCode
 * @var         \JFormFieldCartPromoCode $field
 */

use JBZoo\Data\Data;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die('Restricted access');

$data  = new Data($displayData);
$field = $data->get('field');

$enterPromoCode = $field->hyper['helper']['promocode']->getSessionData();

$inputAttr = [
    'type'        => 'text',
    'class'       => 'uk-input jsPromoCodeInput',
    'id'          => $data->get('id'),
    'placeholder' => $data->get('hint'),
    'name'        => $data->get('name')
];

$btnClass = 'uk-button uk-button-secondary uk-button-small uk-button-normal@m jsPromoCodeSubmit';

$promoCode = $enterPromoCode->get('code');

if ($promoCode) {
    $inputAttr['value']    = $promoCode;
    $inputAttr['readonly'] = 'readonly';
    $btnClass .= ' uk-disabled uk-hidden';
}
?>
<div class="hp-promo-code">
    <div class="uk-inline">
        <input <?= $field->hyper['helper']['html']->buildAttrs($inputAttr) ?>>
    </div>
    <span class="<?= $btnClass ?>">
        <?= Text::_('COM_HYPERPC_CART_PROMO_CODE_BTN_LABEL') ?>
    </span>
    <span class="uk-button uk-button-secondary jsPromoCodeReset<?= (!$promoCode) ? ' uk-hidden' : '' ?>">
        <span uk-icon="icon: trash"></span>
    </span>
</div>
