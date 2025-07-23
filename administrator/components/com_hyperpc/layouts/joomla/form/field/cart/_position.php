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
 * @var         \HYPERPC\Helper\RenderHelper $this
 * @var         \JBZoo\Data\Data $values
 * @var         \HYPERPC\Cart\Elements\Manager $manager
 * @var         string $position
 * @var         \JFormFieldCartParams $fieldData
 */

use JBZoo\Utils\Str;
use Cake\Utility\Inflector;
use Joomla\CMS\Language\Text;
use Cake\Utility\Text as CakeText;

defined('_JEXEC') or die('Restricted access');
?>
<fieldset class="position-<?= $position ?> hp-fieldset" data-position="<?= $position ?>">
    <legend><?= Text::_('COM_HYPERPC_CART_ELEMENT_POSITION_' . Str::up($position)) ?></legend>
    <fieldset>
        <?php if ($values->get($position) === null) : ?>
            <ul class="hp-sortable sortable-wrapper jsSortable<?= Inflector::camelize($position) ?>">
            </ul>
        <?php else :
            $elements = (array) $values->get($position); ?>
            <ul class="hp-sortable sortable-wrapper jsSortable<?= Inflector::camelize($position) ?>">
                <?php foreach ($elements as $identifier => $data) :
                    /** @var \HYPERPC\Cart\Elements\Element $element */
                    $element = $manager->getElement($data['element'], $data['type']); ?>
                    <?php if ($element) :
                        $data = array_merge($data, [
                            'identifier' => $identifier,
                            'fieldName'  => $fieldData->name . '[' . $data['position'] . ']'
                        ]);
                        $element->setData($data); ?>
                        <li class="<?= $element->getIdentifier() ?>">
                            <div class="accordion-group hp-element-type-<?= $element->getType() ?>">
                                <div class="accordion-heading">
                                    <a href="#" class="hp-icon hp-icon-sort jsFieldSort"></a>
                                    <a class="accordion-toggle" data-toggle="collapse" data-parent="#hp-accordion-<?= $position ?>"
                                       href="#el-<?= $identifier ?>">
                                        <?= ($element->getData('name')) ? CakeText::truncate($element->getData('name')) : $element->getTitle() ?>
                                    </a>
                                    <em class="text-error">(<?= $element->getData('element') ?>)</em>
                                    <div class="accordion-nav">
                                        <a class="accordion-toggle" data-toggle="collapse"
                                           data-parent="#hp-accordion" href="#el-<?= $identifier ?>">
                                            <i class="hp-icon hp-icon-edit"></i>
                                        </a>
                                        <a href="#" class="jsRemoveField">
                                            <i class="hp-icon hp-icon-remove"></i>
                                        </a>
                                    </div>
                                </div>
                                <div id="el-<?= $identifier ?>" class="accordion-body collapse" data-identifier="<?= $identifier ?>">
                                    <div class="accordion-inner form-horizontal">
                                        <?= $element->getEditParams() ?>
                                    </div>
                                </div>
                            </div>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </fieldset>
</fieldset>