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

use HYPERPC\App;
use HYPERPC\Data\JSON;
use Joomla\CMS\Language\Text;
use HYPERPC\Helper\FieldsHelper;
use HYPERPC\Joomla\Model\Entity\Field as FieldEntity;

/**
 * @var array           $displayData
 * @var \JFormFieldFps  $field
 */

$i             = 0;
$app           = App::getInstance();
$field         = $displayData['field'];
$countQuality  = count($field::QUALITY);
$cols          = 12 / $countQuality;
$cpuFolderId   = $app['params']->get('cpu_folder_id', 0, 'int');

$partCpuListLink = $app['route']->build([
    'view'          => 'positions',
    'tmpl'          => 'component',
    'layout'        => 'modal',
    'folder_id'     => $cpuFolderId,
    'key'           => 'id',
    'hide_elements' => 1,
    'show_options'  => 0
]);

$params            = new JSON($field->getForm()->getData()->get('params'));
$sliFactor         = (int) $params->find('fps.sli-factor');
$graphicCoreValues = $params->find('fps.graphic-core-value');
$cpuFactors        = $displayData['cpuFactors'];

$resolutions = ['fullhd', 'qhd', '4k'];

$gpuOptions = [];

$gpuFieldId = $app['params']->get('fps_gpu_field_id');

/** @var FieldsHelper $fieldsHelper */
$fieldsHelper = $app['helper']['fields'];

$gpuField = $fieldsHelper->getFieldById($gpuFieldId);

/** @var FieldEntity $fielfEntity */
$gpuFieldOptions = $gpuField->fieldparams->get('options');
foreach ($gpuFieldOptions as $value) {
    $gpuOptions[$value['value']] = $value['name'];
}

asort($gpuOptions);
?>
<style>
    .hp-fps input.form-control {
        width: 4.5em;
        padding: 2px 4px;
    }
</style>
<div class="hp-fps">
    <div class="row">
        <div class="col-lg-7">
            <div class="row hp-video-cart-wrapper">
                <?php if (count($gpuOptions)) : ?>
                    <fieldset>
                        <legend>
                            GPU Core
                        </legend>
                    </fieldset>
                    <table class="table table-striped table-bordered table-hover">
                        <thead>
                        <tr>
                            <td><?= Text::_('JGLOBAL_TITLE') ?></td>
                            <?php foreach ($field::QUALITY as $qualityName) : ?>
                                <td class="center" colspan="3">
                                    <?= $qualityName ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($gpuOptions as $alias => $name) :
                            if ($alias === 'none') {
                                continue;
                            }
                            ?>
                            <tr class="hp-item-row">
                                <td style="vertical-align: middle">
                                    <span class="hp-part-name"><?= $name ?></span>
                                </td>
                                <?php foreach ($resolutions as $resolution) : ?>
                                    <td class="center">
                                        <input type="number" class="form-control" value="<?= !empty($graphicCoreValues[$alias]) ? $graphicCoreValues[$alias]['ultra'][$resolution] : '' ?>" placeholder="<?= ucfirst($resolution) ?>"
                                               name="<?= $displayData['name'] . '[' . $field::TYPE_GRAPHIC_CORE_VALUES . '][' . $alias . '][ultra][' . $resolution . ']' ?>">
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-lg-3">
            <div id="hp-fps-cpu-factor">
                <fieldset>
                    <legend>
                        <?= Text::_('CPU Factor') ?>
                    </legend>
                </fieldset>
                <table class="table table-striped table-bordered table-hover">
                    <thead>
                        <tr>
                            <td>
                                <?= Text::_('JGLOBAL_TITLE') ?>
                            </td>
                            <td colspan="2" class="w-1 text-center">Factor value</td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cpuFactors as $itemKey => $data) :
                            $name = $data['name'];
                            $value = $data['value'];
                            ?>
                            <tr data-itemkey="<?= $itemKey ?>">
                                <td>
                                    <?= $name ?>
                                </td>
                                <td class="text-center">
                                    <input type="number" class="form-control" value="<?= $value ?>"
                                        name="<?= $displayData['name'] . '[' . $field::TYPE_CPU_FACTOR . '][' . $itemKey . ']' ?>">
                                </td>
                                <td>
                                    <button type="button" class="jsRemoveCpuItem btn btn-sm btn-danger">
                                        <span class="icon-minus" aria-hidden="true"></span>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <button class="jsAddCpuItem btn btn-sm btn-success float-end"
                        data-type="<?= $field::TYPE_CPU_FACTOR ?>"
                        data-href="#hp-fps-cpu-factor"
                        data-src="<?= $partCpuListLink ?>"
                        data-caption="<?= Text::_('CPU Factor') ?>"
                >
                    <span class="icon-plus"></span>
                    <?= Text::_('JGLOBAL_FIELD_ADD') ?>
                </button>
            </div>
        </div>
        <div class="col-lg-2">
            <fieldset>
                <legend>
                    SLI Factor
                </legend>
            </fieldset>
            <input type="number" class="form-control" value="<?= $sliFactor ?>"
                   name="<?= $displayData['name'] . '[' . $field::TYPE_SLI_FACTOR . ']' ?>">
        </div>
    </div>
</div>
