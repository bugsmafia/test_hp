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

use HYPERPC\App;
use JBZoo\Data\JSON;
use Joomla\CMS\Language\Text;
use HYPERPC\Joomla\Model\Entity\MoyskladPart;
use HYPERPC\Joomla\Model\Entity\ProductFolder;
use JFormFieldMoyskladConfigurator as ModelField;

defined('_JEXEC') or die('Restricted access');

/**
 * @var         MoyskladPart  $part
 * @var         JSON          $value
 * @var         ModelField    $field
 * @var         ProductFolder $group
 * @var         ProductFolder $child
 * @var         ProductFolder $producer
 * @var         array         $parts
 * @var         array         $partOptions
 * @var         array         $displayData
 */

$j = 0;

$app            = App::getInstance();
$field          = $displayData['field'];
$value          = $displayData['value'];
$items          = $displayData['items'];
$variants       = $displayData['variants'];
$fieldName      = $displayData['name'];
$productFolders = $displayData['productFolders'];

$productsUrl = $app['helper']['route']->url([
    'view'          => 'positions',
    'tmpl'          => 'component',
    'option'        => 'com_hyperpc',
    'layout'        => 'modal',
    'category_id'   => '1',
]);
?>
<div class="hp-configurator-field jsGieldConfigurator">
    <div class="row">
        <div class="col-12 col-lg-4">
            <div class="btn-group">
                <a data-type="iframe" data-src="<?= $productsUrl ?>" data-id="<?= $app['input']->getInt('id') ?>" class="btn btn-success jsSetByExample">
                    <?= Text::_('COM_HYPERPC_FIELD_RELATED_PRODUCTS_INSTALL_ITEM_BY_EXAMPLE') ?>
                </a>
            </div>
        </div>
    </div>
    <hr />
    <div class="row">
        <div class="col-12 col-lg-2">
            <?= $field->partial('nav_tab_group', [
                'productFolders' => $productFolders,
                'field'  => $field
            ]) ?>
        </div>
        <div class="col-12 col-lg-7">
            <div id="groupTabContent" class="tab-content">
                <?php foreach ($productFolders as $productFolder) :
                    $firstActiveClass = ($j === 0) ? ' active' : '';
                    ?>
                    <div class="tab-pane <?= $firstActiveClass ?>" id="<?= $productFolder->alias ?>">
                        <?php
                        $children = $productFolder->get('children', []);
                        if (count($children)) {
                            echo $field->partial('content_category', [
                                'value'       => $value,
                                'items'       => $items,
                                'variants'    => $variants,
                                'displayData' => $displayData,
                                'children'    => $children
                            ]);
                        }
                        ?>
                    </div>
                    <?php
                    $j++;
                endforeach; ?>
            </div>
        </div>
        <div class="col-12 col-lg-3">
            <div class="control-group">
                <div class="controls">
                    <div class="input-group">
                        <?= $field->getForm()->getInput('list_price') ?>
                        <span class="input-group-text"><?= Text::_('COM_HYEPRPC_MONEY_RUS') ?></span>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header text-white bg-info">
                    <?= Text::_('COM_HYPERPC_CONFIGURATOR_SELECTED_CONFIGURATION') ?>
                </div>
                <div class="card-body p-0">
                    <ul class="jsConfigurationSummary list-group">
                        <?php foreach ($productFolders as $productFolder) :
                            $subfolders = $productFolder->getSubfolders();
                            if (empty($subfolders)) {
                                continue;
                            }
                            ?>
                            <li class="list-group-item">
                                <?= $productFolder->title ?>

                                <ul class="list-group list-group-flush">
                                    <?php foreach ($subfolders as $subfolder) : ?>
                                        <li class="list-group-item py-1" data-group-id="<?= $subfolder->id ?>">
                                            <span class="text-muted"><?= $subfolder->title ?></span>
                                            <div class="jsSummaryValue small"></div>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $('a[data-tab="group"]').on('shown.bs.tab', function (e) {
        $("#groupTab .nav-link.active").removeClass("active");
        $(this).addClass('active');

        const tabId          = $(this).attr('href'),
            $childTabs       = $(tabId).find('.tab-pane'),
            $childFirstTab   = $childTabs.first(),
            $categoryTabLink = $(this).siblings('#categoryTab').find('li').first().find('a');

        $childTabs.removeClass('active show')
        $childFirstTab.addClass('active show');
        $categoryTabLink.addClass('active');
    });
</script>
