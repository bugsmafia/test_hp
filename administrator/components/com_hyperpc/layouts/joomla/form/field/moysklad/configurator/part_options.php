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

use JBZoo\Data\JSON;
use Joomla\CMS\Language\Text;
use MoySklad\Entity\Product\ProductFolder;
use HYPERPC\Joomla\Model\Entity\MoyskladPart;
use HYPERPC\Joomla\Model\Entity\MoyskladVariant;

/**
 * @var         MoyskladVariant[]   $variants
 * @var         ProductFolder       $folder
 * @var         MoyskladPart        $part
 * @var         JSON                $value
 * @var         string              $fieldName
 * @var         string              $isMultiply
 * @var         bool                $isPropertyChecked
 */
?>

<?php foreach ($variants as $variant) :
    $app                  = $variant->hyper;
    $isCheckedOption      = \key_exists($variant->id, $value->get('options', []));
    $isCheckedOptionMini  = \key_exists($variant->id, $value->get('options_mini', []));
    $isDefaultRadioOption = \in_array($variant->id, $value->get('option', []));

    if ($variant->isDiscontinued() && !$isDefaultRadioOption) {
        continue;
    }

    $optionCheckOptions = [
        'data-type' => 'option',
        'value'     => $part->id,
        'class'     => 'jsOptionCheck form-check-input',
        'type'      => 'checkbox',
        'data-id'   => $variant->id,
        'checked'   => $isCheckedOption,
        'id'        => 'option-' . $variant->id,
        'name'      => $fieldName . '[options][' . $variant->id . ']'
    ];

    $optionCheckOptionsMini = [
        'data-type' => 'option',
        'value'     => $part->id,
        'class'     => 'jsOptionMini form-check-input',
        'type'      => 'checkbox',
        'data-id'   => $variant->id,
        'checked'   => $isCheckedOptionMini,
        'name'      => $fieldName . '[options_mini][' . $variant->id . ']'
    ];

    $radioDefaultAttrs = [
        'type'         => 'radio',
        'data-type'    => 'option',
        'data-part-id' => $part->id,
        'data-id'      => $variant->id,
        'value'        => $variant->id,
        'checked'      => $isDefaultRadioOption,
        'class'        => 'hasTooltip jsCheckDefault form-check-input',
        'title'        => Text::_('JTOOLBAR_DEFAULT'),
        'name'         => $fieldName . '[option][' . $part->id . ']'
    ];

    if ($isDefaultRadioOption) {
        $optionCheckOptionsMini['readonly'] = $optionCheckOptions['readonly'] = 'readonly';
    }

    if ($isPropertyChecked) {
        $optionCheckOptionsMini['readonly'] = 'readonly';
        unset($optionCheckOptionsMini['checked']);
    }

    $optionCheckOptions     = $app['helper']['html']->buildAttrs($optionCheckOptions);
    $optionCheckOptionsMini = $app['helper']['html']->buildAttrs($optionCheckOptionsMini);
    $radioDefaultAttrs      = $app['helper']['html']->buildAttrs($radioDefaultAttrs);

    $optionEditUrl = $variant->getViewUrl([
        'layout'            => 'edit',
        'product_folder_id' => $part->product_folder_id,
        'part_id'           => $variant->part_id
    ]);

    $class = '';
    $isArchived = $part->isArchived() || $variant->isArchived();
    if ($isArchived) {
        $class = ' hp-row-archive';
        if ($isDefaultRadioOption) {
            $app['cms']->enqueueMessage(
                Text::sprintf(
                    'COM_HYPERPC_CONFIGURATOR_POSITION_IS_ARCHIVED',
                    $part->name . ' ' . $variant->name
                ),
                'warning'
            );
        }
    }
    ?>
    <tr class="jsOptionRow <?= $class ?>" data-id="<?= $variant->id ?>" data-part-id="<?= $part->id ?>">
        <td class="center">
            <input <?= $optionCheckOptions ?> />
        </td>
        <td class="center">
            <input <?= $optionCheckOptionsMini ?> />
        </td>
        <td class="p-0">
            <a href="<?= $optionEditUrl ?>" target="_blank"
                title="<?= Text::sprintf('COM_HYPERPC_PART_EDIT_LINK', $variant->name) ?>">
            </a>
        </td>
        <td>
            <span class="text-muted">┊&nbsp;&nbsp;–&nbsp;</span>
            <span class="small option-name row-name">
                <?php if ($isArchived) : ?>
                    <span class="badge bg-dark">A</span>
                <?php endif; ?>
                <?= $variant->getConfigurationName() ?>
            </span>
        </td>
        <td></td>
        <td>
            <label class="w-100 text-center">
                <input <?= $radioDefaultAttrs ?> />
            </label>
        </td>
        <td class="text-end text-nowrap small">
            <?= $variant->list_price->html() ?>
        </td>
    </tr>
<?php endforeach;
