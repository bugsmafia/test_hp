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
 * @var         \Joomla\CMS\Form\Form $form
 * @var         \Joomla\CMS\Layout\FileLayout $this
 * @var         HyperPcViewProduct $displayData
 */

use Joomla\CMS\Language\Text;

defined('_JEXEC') or die('Restricted access');

$form = $displayData->getForm();

// JLayout for standard handling of metadata fields in the administrator content edit screens.
$fieldSets = $form->getFieldsets('metadata');

echo $displayData->app['helper']['layout']->renderFieldset($displayData, ['fields' => [
    'jform_params_title',
    'jform_params_show_title'
]]);
?>
<hr />
<?php foreach ($fieldSets as $name => $fieldSet) : ?>
    <?php if (isset($fieldSet->description) && trim($fieldSet->description)) : ?>
        <p class="alert alert-info">
            <?= $this->escape(Text::_($fieldSet->description)); ?>
        </p>
    <?php endif; ?>

    <?php
    // Include the real fields in this panel.
    if ($name === 'jmetadata') {
        echo $form->renderField('metadesc');
        echo $form->renderField('metakey');
        echo $form->renderField('xreference');
    }

    foreach ($form->getFieldset($name) as $field) {
        if ($field->name !== 'jform[metadata][tags][]') {
            echo $field->renderField();
        }
    } ?>
<?php endforeach; ?>