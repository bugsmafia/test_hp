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
 * @var         \HYPERPC\Cart\Elements\Element $element
 * @var         JFormFieldCartParams $fieldData
 */

use JBZoo\Utils\Str;
use JBZoo\Data\Data;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use HYPERPC\Cart\Elements\Manager;

defined('_JEXEC') or die('Restricted access');

$manager = new Manager();
$manager->build();

$fields    = $manager->getAllElements();
$values    = new Data($fieldData->value);
$types     = $manager->getTypes();

$orderPositions = $manager->getOrderPositions();
$eventPositions = $manager->getEventPositions();
?>
<style>
    .hp-sortable {
        min-height: 20px;
        list-style-type: none;
        margin: 0;
        padding: 5px 0 0 0;
    }
</style>
<div class="row hp-field-basket-params">
    <div class="col-12 col-lg-8">
        <?php
        echo implode(PHP_EOL, [
            HTMLHelper::_('uitab.startTabSet', 'cartParams', ['active' => 'cartOrder']),
                HTMLHelper::_('uitab.addTab', 'cartParams', 'cartOrder', Text::_('COM_HYPERPC_CART_PARAMS_ORDER_TAB')),
                    $manager->renderAdminPositions($orderPositions, $fieldData),
                HTMLHelper::_('uitab.endTab'),
                HTMLHelper::_('uitab.addTab', 'cartParams', 'orderEvents', Text::_('COM_HYPERPC_CART_PARAMS_ORDER_EVENT_TABS')),
                    $manager->renderAdminPositions($eventPositions, $fieldData),
                HTMLHelper::_('uitab.endTab'),
            HTMLHelper::_('uitab.endTabSet')
        ]);
        ?>
    </div>
    <div class="col-12 col-lg-4">
        <?php if (count($fields)) : ?>
            <?php foreach ($fields as $type => $cartField) : ?>
                <fieldset>
                    <legend><?= Text::_('COM_HYPERPC_CART_ELEMENT_' . Str::up($type)) ?></legend>
                    <ul class="hp-cart-elements">
                        <?php foreach ((array) $cartField as $name => $element) :
                            if ($element->getManifestData('hidden', false)) {
                                continue;
                            }
                            ?>
                            <li class="hp-cart-element jsCartElement" data-field="<?= $element->getName() ?>"
                                data-type="<?= $element->getType() ?>">
                                <?= $element->getTitle() ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </fieldset>
                <br />
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
