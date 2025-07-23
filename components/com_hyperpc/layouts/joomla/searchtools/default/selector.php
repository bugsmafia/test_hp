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
 * @var         array $displayData
 */

defined('_JEXEC') or die('Restricted access');

$data = $displayData;
?>
<div class="js-stools-field-selector">
    <?= $data['view']->filterForm->getField($data['options']['selectorFieldName'])->input ?>
</div>
