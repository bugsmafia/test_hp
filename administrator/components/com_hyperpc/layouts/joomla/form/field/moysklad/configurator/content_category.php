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

use HYPERPC\App;
use JBZoo\Data\JSON;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use JFormFieldMoyskladConfigurator as ModelField;

/**
 * @var array        $items
 * @var JSON         $value
 * @var ModelField   $field
 * @var array        $variants
 * @var array        $children
 * @var array        $displayData
 */

$app = App::getInstance();

$value         = $displayData['value'];
$fieldName     = $displayData['name'];
$field         = $displayData['field'];
$multiply      = new JSON($value->get('multiply', []));
$properties    = new JSON($value->get('properties', []));
$canDeselected = new JSON($value->get('can_deselected', []));
$productId     = $app['input']->get('id', 0);
$product       = $app['helper']['moyskladProduct']->getById($productId);
$listAttribs   = ['class' => 'form-select form-select-sm'];
?>
<div id="categoryTabContent" class="tab-content">
    <?php
    $g = 0;
    foreach ((array) $children as $child) :
        $maxQuantity = $app['helper']['configurator']->groupMaxQuantities($product, $child);

        $folderActiveContentClass = $g++ === 0 ? ' active' : '';
        $isCheckedProperty        = $properties->get($child->id, false, 'bool');
        $propertyChecked          = $isCheckedProperty ? 'checked="checked"' : '';
        $multiplyIsChecked        = $multiply->get($child->id, false, 'bool') ? 'checked="checked"' : '';
        ?>
        <div id="<?= $child->alias ?>" class="jsGroupContent tab-pane<?= $folderActiveContentClass ?>">
           <div class="card mb-4">
                <div class="card-body text-bg-light row gy-2">
                    <div class="col-6">
                        <div class="form-check">
                            <label for="input-multiply-<?= $child->alias ?>" class="hasTooltip"
                                    title="<?= Text::sprintf('COM_HYPERPC_CONFIGURATOR_MULTIPLY_SELECT_DESC', $child->title) ?>">
                                <input type="checkbox" class="jsEnableMultiple form-check-input" id="input-multiply-<?= $child->alias ?>" <?= $multiplyIsChecked ?>
                                        name="jform[configuration][multiply][<?= $child->id ?>]" value="true" data-group-id="<?= $child->id ?>" />
                                <?= Text::_('COM_HYPERPC_CONFIGURATOR_MULTIPLY_SELECT') ?>
                            </label>
                        </div>

                        <div class="form-check hidden"> <!-- @todo remove -->
                            <label for="input-properties-<?= $child->alias ?>" class="hasTooltip"
                                   title="<?= Text::_('COM_HYPERPC_CONFIGURATOR_PROPERTIES_SELECT_DESC') ?>">
                                <input class="jsPartProperties form-check-input" type="checkbox" id="input-properties-<?= $child->alias ?>" <?= $propertyChecked ?>
                                       name="jform[configuration][properties][<?= $child->id ?>]" value="<?= $child->id ?>" />
                                <?= Text::_('COM_HYPERPC_CONFIGURATOR_PROPERTIES_SELECT') ?>
                            </label>
                        </div>
                    </div>

                    <div class="col-6">
                        <label class="jsCanDeselected hasTooltip w-100<?= $multiplyIsChecked ? ' invisible' : '' ?>"
                                title="<?= Text::_('COM_HYPERPC_CONFIGURATOR_GROUP_CAN_DESELECTED_DESC') ?>">
                                <?= Text::_('COM_HYPERPC_CONFIGURATOR_GROUP_CAN_DESELECTED_LABEL') ?>
                            <br>
                            <?php
                                $folderCanDeselected = $child->params->get('configurator_can_deselected', 1);
                                if (array_key_exists($child->id, (array) $canDeselected)) {
                                    $folderCanDeselected = (int) $canDeselected[$child->id];
                                } else {
                                    $folderCanDeselected = -1;
                                }

                                echo HTMLHelper::_(
                                    'select.genericlist',
                                    [
                                        -1 => Text::_('COM_HYPERPC_FROM_GLOBAL_PARAMS') . ' (' . ($child->params->get('configurator_can_deselected', 1, 'bool') ? Text::_('JYES') : Text::_('JNO')) . ')',
                                        0 => Text::_('JNO'),
                                        1 => Text::_('JYES')
                                    ],
                                    'jform[configuration][can_deselected][' . $child->id . ']',
                                    $listAttribs,
                                    null,
                                    null,
                                    $folderCanDeselected,
                                    null
                                );
                            ?>
                        </label>
                    </div>

                    <div class="col-6">
                        <label class="hasTooltip w-100"
                                title="<?= Text::_('COM_HYPERPC_CONFIGURATOR_GROUP_MIN_QUANTITY_DESC') ?>">
                            <?= Text::_('COM_HYPERPC_CONFIGURATOR_GROUP_MIN_QUANTITY_LABEL') ?>
                            <br>
                            <?= $app['helper']['configurator']->quantitySelect($child, $product, 'min', $listAttribs); ?>
                        </label>
                    </div>

                    <div class="col-6">
                        <label class="hasTooltip w-100"
                                title="<?= Text::_('COM_HYPERPC_CONFIGURATOR_GROUP_MAX_QUANTITY_DESC') ?>">
                            <?= Text::_('COM_HYPERPC_CONFIGURATOR_GROUP_MAX_QUANTITY_LABEL') ?>
                            <br>
                            <?= $app['helper']['configurator']->quantitySelect($child, $product, 'max', $listAttribs); ?>
                        </label>
                    </div>

                </div>
           </div>

            <?php if (\key_exists($child->id, $items)) :
                echo $field->partial('items', [
                    'value'              => $value,
                    'optionList'         => $variants,
                    'folder'             => $child,
                    'field'              => $field,
                    'fieldName'          => $fieldName,
                    'groupMaxQuantity'   => $maxQuantity,
                    'isPropertyChecked'  => $isCheckedProperty,
                    'items'              => $items[$child->id],
                    'product'            => $product,
                    'multiplyIsChecked'  => $multiplyIsChecked,
                    'activeContentClass' => $folderActiveContentClass
                ]);
            endif; ?>
        </div>
    <?php endforeach; ?>
</div>
