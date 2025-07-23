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

use HYPERPC\Data\JSON;

defined('_JEXEC') or die('Restricted access');

jimport('joomla.html.editor');

$data  = new JSON($displayData);
$value = $data->get('value', 0, 'int');
?>
<div class="jsRating">
    <div id="<?= $data->get('id') ?>" data-score="<?= $value ?>"></div>
    <input id="<?= $data->get('id') ?>_input" type="hidden" value="<?= $value ?>"
           name="<?= $data->get('name') ?>" />
</div>
