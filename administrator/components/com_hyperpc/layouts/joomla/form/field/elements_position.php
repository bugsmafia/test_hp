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
 * @var         \JFormFieldElementsPosition $field
 * @var         Element                     $element
 * @var         array                       $displayData
 */

use JBZoo\Utils\Str;
use HYPERPC\Data\JSON;
use Cake\Utility\Inflector;
use HYPERPC\Elements\Manager;
use Joomla\CMS\Language\Text;
use HYPERPC\Elements\Element;

defined('_JEXEC') or die('Restricted access');

$data    = new JSON($displayData);
$field   = $data->get('field');
$groups  = (array) $data->get('elements');
$values  = (array) $data->find('value');
$manager = Manager::getInstance();
?>
<div id="<?= $data->get('uniqueId') ?>" class="row hp-elements-position">
    <div class="col-12">
        <fieldset>
            <legend class="border-bottom mb-1 fw-bold">
                <?= Text::_('COM_HYPERPC_ELEMENT_' . Str::up($field->fieldname) . '_SETTINGS') ?>
            </legend>
        </fieldset>
        <div class="accordion jsEditElements" id="hp-group-<?= $field->fieldname ?>">
            <ul class="jsElementEditList list-unstyled" data-position="<?= $field->fieldname ?>">
                <?php
                foreach ($values as $identifier => $config) {
                    $config  = new JSON($config);
                    if ($config->get('group')) {
                        $element = $manager->create(
                            $config->get('type'),
                            $config->get('group'),
                            $config->getArrayCopy()
                        );

                        if (!$element) {
                            continue;
                        }

                        $element->initialize();
                        $element->setFormControl($data->get('name'));

                        echo $element->hyper['helper']['render']->render('elements/edit_element', [
                            'element' => $element,
                            'id'      => 'hp-group-' . $field->fieldname
                        ]);
                    }
                }
                ?>
            </ul>
        </div>
    </div>
    <div id="hp-add-element" class="col-12">
        <fieldset class="hp-elements">
            <legend><?= Text::_('COM_HYPERPC_ELEMENTS_LIBRARY') ?></legend>
            <?php foreach ($groups as $group => $elements) : ?>
                <div class="hp-elements-group">
                    <div class="hp-elements-group-name">
                        <?= Text::_('COM_HYPERPC_ELEMENT_' . Str::up($group) . '_GROUP') ?>
                    </div>

                    <ul class="hp-element-list unstyled jsElement<?= Inflector::camelize($group) ?>"
                        data-group="<?= $group ?>">
                        <?php foreach ($elements as $element) :
                            $attrs = [
                                'class' => [
                                    'hp-element',
                                    'jsAddNewElement'
                                ],
                                'data'  => [
                                    'type'    => $element->getType(),
                                    'core'    => (int) $element->isCore(),
                                    'control' => $data->get('name')
                                ]
                            ];

                            if ($element->getMetaData('disable')) {
                                unset($attrs['class']);
                                $attrs['class'] = [
                                    'hp-element'
                                ];

                                $attrs['style'] = 'text-decoration: line-through;color: #ccc;cursor: inherit;';
                            }

                            if ($description = $element->getMetaData('description')) {
                                $attrs['class'][] = 'hasTooltip';
                                $attrs['title']   = $description;
                                $attrs['data']['placement'] = 'left';
                            }
                            ?>
                            <li <?= $field->hyper['helper']['html']->buildAttrs($attrs) ?>>
                                <?= $element->getConfig('name') ?>
                                <span class="hp-element-type">
                                    (<?= $element->getType() ?>)
                                </span>
                                <?php if ($element->isCore()) : ?>
                                    <em>(<?= Text::_('COM_HYPERPC_ELEMENT_CORE') ?>)</em>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endforeach; ?>
        </fieldset>
    </div>
</div>
<hr />
