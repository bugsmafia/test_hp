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
 *
 * @var         array $ajaxLoadArgs
 */

use Joomla\CMS\Language\Text;

$attrs = [
    'class' => 'uk-button uk-button-default jsShowMoreReview',
    'type'  => 'button',
    'data'  => $ajaxLoadArgs
];
?>

<tr class="hp-review-row-ds">
    <td style="visibility: hidden; width: 0; display: none;" data-sort-method="none"></td>
    <td data-sort-method="none" class="uk-text-center">
        <button <?= $this->hyper['helper']['html']->buildAttrs($attrs) ?>>
            <?= Text::_('COM_HYPERPC_SHOW_MORE') ?>
        </button>
    </td>
    <td style="visibility: hidden; width: 0; display: none;" data-sort-method="none"></td>
</tr>