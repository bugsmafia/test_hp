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
 */

use HYPERPC\Data\JSON;

defined('_JEXEC') or die('Restricted access');

if (!isset($value)) {
    $value = new JSON();
}

$inputSize    = 7;
$inputPattern = '^[0-9]{3,7}';

$attrsTo = [
    'type'      => 'text',
    'size'      => $inputSize,
    'maxlength' => $inputSize,
    'pattern'   => $inputPattern,
    'class'     => 'uk-input uk-form-large jsPriceTo',
    'inputmode' => 'decimal'
];

$attrsFrom = [
    'type'      => 'text',
    'size'      => $inputSize,
    'maxlength' => $inputSize,
    'pattern'   => $inputPattern,
    'class'     => 'uk-input uk-form-large jsPriceFrom',
    'inputmode' => 'decimal'
];

$priceFrom = $value->get('from', 0, 'int');
if ($priceFrom > 0) {
    $attrsFrom['value'] = $priceFrom;
}

$priceTo = $value->get('to', 0, 'int');
if ($priceTo) {
    $attrsTo['value'] = $priceTo;
}
?>
<div class="jsPriceRange uk-grid uk-grid-small uk-flex-middle uk-flex-nowrap">
    <div>
        <input <?= $this->hyper['helper']['html']->buildAttrs($attrsFrom) ?>>
    </div>
    <div>&mdash;</div>
    <div>
        <input <?= $this->hyper['helper']['html']->buildAttrs($attrsTo) ?>>
    </div>
</div>
