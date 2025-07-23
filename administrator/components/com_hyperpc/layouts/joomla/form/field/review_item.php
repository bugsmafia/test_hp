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

defined('_JEXEC') or die('Restricted access');

use HYPERPC\Data\JSON;
use Joomla\CMS\Language\Text;

/**
 * @var JFormFieldReviewItem $field
 * @var array                $displayData
 */

$data  = new JSON($displayData);
$field = $data->get('field');

$itemListAttrs = $field->hyper['route']->build([
    'layout' => 'modal',
    'tmpl'   => 'component'
]);
?>
<div class="field-review-item" data-url="<?= $itemListAttrs ?>"">
    <div class="input-append">
        <input type="text"
               name="review-item-name"
               value="<?= $data->get('item_name') ?>"
               class="field-review-item-input-name"
               id="<?= $data->get('id') ?>"
               readonly
               placeholder="<?= Text::_('COM_HYPERPC_REVIEW_ITEM_CHOOSE_PLACEHOLDER') ?>"
        />
        <button type="button" class="btn btn-primary button-select jsChoseProduct"
                title="<?= Text::_('COM_HYPERPC_REVIEW_ITEM_CHOOSE_PLACEHOLDER') ?>">
            <span class="icon-plus"></span>
        </button>
    </div>
    <input type="hidden"
           name="<?= $data->get('name') ?>"
           value="<?= $data->get('value') ?>"
           class="field-user-input"
           id="<?= $data->get('id') . '_id' ?>"
    />
</div>
