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
use HYPERPC\Data\JSON;
use HYPERPC\ORM\Entity\Field;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\Toolbar;
use HYPERPC\Helper\FilterHelper;
use HYPERPC\Helper\ProductFolderHelper;
use HYPERPC\Joomla\Model\Entity\ProductFolder;

/**
 * @var Field   $field
 * @var Field   $fieldGroup
 * @var array   $displayData
 */

$i   = 0;
$app = App::getInstance();

$data         = new JSON($displayData);
$renderGroup  = $data->get('renderGroup');
$value        = (array) $data->get('value', []);
$fields       = $data->get('fieldList');
$fieldName    = $data->get('name');
$fieldContext = $data->get('fieldContext');

/** @var ProductFolderHelper $groupHelper */
$groupHelper = $app['helper']['productFolder'] ;

/** @var Toolbar $toolbar */
$toolbar = Toolbar::getInstance('position');

$toolbar->appendButton(
    'UpdateProductIndex',
    Text::_('COM_HYPERPC_RECOUNT_PRODUCT_INDEX_LABEL')
);
?>
<style>
    .hp-saved-index-fields {
        padding: 2px 2px 0 0;
    }

    #hp-saved-index-fields .hp-icon {
        position: relative;
        top: 6px;
    }

    .hp-index-field-group-name {
        font-style: italic;
        position: absolute;
        left: 40%;
        top: 6px;
        color: #b9b9b9;
    }

    .hp-index-connected-sortable li {
        border: 1px solid #e5e5e5;
        padding: 2px 8px;
        position: relative;
        -webkit-border-radius: 3px;
        -moz-border-radius: 3px;
        border-radius: 3px;
        margin-bottom: 2px;
    }

    #filter_toolbar {
        background: #f0f0f0;
        border: 1px solid #dedede;
        padding-top: 7px;
    }
</style>
<div class="jsProductIndex" data-context="<?= $fieldContext ?>" data-name="<?= $fieldName ?>">
    <div class="row">
        <div class="col-12">
            <div class="alert alert-info">
                <?= Text::_('COM_HYPERPC_FILTER_RECOUNT_INDEX_ALERT_INFO') ?>
            </div>
        </div>
        <div class="col-12 col-lg-8 jsFiltersWrapper">
            <ul class="hp-index-connected-sortable hp-saved-index-fields unstyled">
                <?php if (count($value)) : ?>
                    <?php foreach ((array) $value as $value) :
                        if (empty($value)) {
                            continue;
                        }

                        $value      = new JSON($value);
                        $fieldTitle = $value->get('title');
                        $fieldId    = $value->get('id', 0, 'int');
                        $groupId    = $value->get('group_id');

                        /** @var ProductFolder $group */
                        $group = $groupHelper->findById($groupId, [
                            'select' => ['a.title', 'a.id']
                        ]);

                        $idControlAttrs = [
                            'type'  => 'hidden',
                            'value' => $fieldId,
                            'name'  => $fieldName . '[' . $i . '][id]'
                        ];

                        $titleControlAttrs = [
                            'type'  => 'text',
                            'value' => $fieldTitle,
                            'name'  => $fieldName . '[' . $i . '][title]'
                        ];

                        $groupControlAttrs = [
                            'type'  => 'hidden',
                            'value' => $group->id,
                            'name'  => $fieldName . '[' . $i . '][group_id]'
                        ];

                        $fromControlAttrs = [
                            'type'  => 'hidden',
                            'name'  => $fieldName . '[' . $i . '][from]',
                            'value' => $value->get('from', FilterHelper::FIELD_TYPE_FIELD_CATEGORY)
                        ];

                        $i++;
                        ?>
                        <li class="hp-index-field-<?= $fieldId ?>" data-group="<?= $group->id ?>">
                            <a href="#" class="hp-icon hp-icon-sort jsFieldSort"></a>
                            <input <?= $app['helper']['html']->buildAttrs($titleControlAttrs) ?>/>
                            <span class="hp-index-field-group-name">
                                (<?= $group->title ?>)
                            </span>
                            <a href="#" class="jsRemoveField pull-right">
                                <i class="hp-icon hp-icon-remove"></i>
                            </a>
                            <input <?= $app['helper']['html']->buildAttrs($idControlAttrs) ?>/>
                            <input <?= $app['helper']['html']->buildAttrs($groupControlAttrs) ?>/>
                            <input <?= $app['helper']['html']->buildAttrs($fromControlAttrs) ?>/>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
        <div class="col-12 col-lg-4">
            <div class="control-group">
                <?= $toolbar->render() ?>
            </div>
            <div class="control-group">
                <?= $data->get('groupTree') ?>
            </div>
            <div class="control-group">
                <?= $data->get('fieldGroups') ?>
            </div>
            <div class="control-group jsAllowedFieldWrapper hidden">
                <lable>
                    <?= Text::_('COM_HYPERPC_PART_ALLOWED_CUSTOM_FIELD_GROUP') ?>
                </lable>
                <div class="form-control">
                    <select class="jsSelectFields"></select>
                </div>
            </div>
        </div>
    </div>
</div>
