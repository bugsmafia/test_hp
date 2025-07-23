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
 * @author      Artem Vyshnevskiy
 */

defined('_JEXEC') or die('Restricted access');

/**
 * @var array $displayData
 */

/** @var \JFormFieldPreview $field */
$field = $displayData['field'];
?>
<a <?= $field->getAttrs() ?>>
    <span class="icon-eye" aria-hidden="true"></span>
    <?= $field->getTitle() ?>
</a>
