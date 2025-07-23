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
 */

defined('_JEXEC') or die('Restricted access');

JHtml::_('jquery.framework');

JHtmlBehavior::core();

JFactory::getDocument()->addScriptDeclaration('
    jQuery(document).ready(function($) {
        if (window.toggleSidebar) {
            toggleSidebar(true);
        } else {
            $("#j-toggle-sidebar-header").css("display", "none");
            $("#j-toggle-button-wrapper").css("display", "none");
        }
    });
');
?>

<div id="sidebar" class="sidebar col-md-3" style="height: 700px; overflow: hidden scroll;">
    <button class="btn btn-sm btn-secondary my-2 options-menu d-md-none" type="button" data-bs-toggle="collapse" data-bs-target=".sidebar-nav" aria-controls="sidebar-nav" aria-expanded="false" aria-label="Скрыть меню">
        <span class="icon-align-justify" aria-hidden="true"></span>
        Скрыть меню
    </button>
    <div class="sidebar-nav">
        <?php if ($displayData->displayMenu) : ?>
            <ul id="submenu" class="nav flex-column">
                <?php foreach ($displayData->list as $item) :
                    $parse = parse_url($item[1]);
                    parse_str($parse['query'], $query);
                    $class = (array_key_exists('view', $query)) ? $query['view'] : 'dashboard';

                    if (isset ($item[2]) && $item[2] == 1) : ?>
                        <li class="active <?= $class ?>">
                    <?php else :

                        ?>
                        <li class="<?= $class ?>">
                    <?php endif;
                    if ($displayData->hide) : ?>
                        <a class="nolink"><?= $item[0] ?></a>
                    <?php else :
                        if ($item[1] !== '') : ?>
                            <a href="<?= JFilterOutput::ampReplace($item[1]) ?>"><?= $item[0] ?></a>
                        <?php else : ?>
                            <?= $item[0] ?>
                        <?php endif;
                    endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <?php if ($displayData->displayMenu && $displayData->displayFilters) : ?>
            <hr/>
        <?php endif; ?>
        <?php if ($displayData->displayFilters) : ?>
            <div class="filter-select hidden-phone">
                <h4 class="page-header"><?= JText::_('JSEARCH_FILTER_LABEL') ?></h4>
                <?php foreach ($displayData->filters as $filter) : ?>
                    <label for="<?= $filter['name'] ?>"
                           class="element-invisible"><?= $filter['label'] ?></label>
                    <select name="<?= $filter['name'] ?>" id="<?= $filter['name'] ?>"
                            class="col-12 small" onchange="this.form.submit()">
                        <?php if (!$filter['noDefault']) : ?>
                            <option value=""><?= $filter['label'] ?></option>
                        <?php endif; ?>
                        <?= $filter['options'] ?>
                    </select>
                    <hr class="hr-condensed"/>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
