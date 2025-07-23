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
 * @var         JFormFieldCreditStatus $field
 */

use HYPERPC\Data\JSON;

defined('_JEXEC') or die('Restricted access');

$displayData = new JSON($displayData);
$field       = $displayData->get('field');
?>
<div class="row">
    <div class="col-12">
        <?= $field->renderOrderStatus() ?>
        <?= $field->renderLeadPipelines() ?>
    </div>
</div>
<hr />
