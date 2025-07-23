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

use JFormFieldMoyskladConfigurator as ModelField;

/**
 * @var         ModelField  $field
 * @var         array       $productFolders
 */

$i = 0;
?>
<ul id="groupTab" class="nav nav-tabs flex-column" role="tablist">
    <?php foreach ($productFolders as $productFolder) :
        $firstActiveClass = ($i === 0) ? ' active' : '';
        ?>
        <li class="nav-item" role="presentation">
            <a class="nav-link <?= $firstActiveClass ?>" data-tab="group" aria-selected="<?= $firstActiveClass ? 'true' : 'false' ?>" href="#<?= $productFolder->alias ?>" role="tab"><?= $productFolder->title ?></a>
            <?php
            echo $field->partial('nav_tab_category', [
                'children' => $productFolder->get('children', [])
            ]);
            ?>
        </li>
        <?php
        $i++;
    endforeach; ?>
</ul>
