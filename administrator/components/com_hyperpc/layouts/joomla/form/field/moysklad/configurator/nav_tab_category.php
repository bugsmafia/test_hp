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
 * @var         array $children
 */

use HYPERPC\App;

defined('_JEXEC') or die('Restricted access');

$i   = 0;
$app = App::getInstance();
?>
<ul id="categoryTab" class="nav nav-pills p-0">
    <?php foreach ((array) $children as $child) :
        $folderActiveClass = ($i++ === 0) ? ' active' : '';
        ?>
        <li class="nav-item me-0">
            <a class="nav-link rounded-0 <?= $folderActiveClass ?>" data-tab="category" href="#<?= $child->alias ?>" tabindex="-1" role="tab">
                <?= $child->title ?>
            </a>
        </li>
    <?php endforeach; ?>
</ul>
