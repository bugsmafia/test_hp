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

use HYPERPC\Helper\RenderHelper;

/**
 * @var     array           $rootGroups
 * @var     RenderHelper    $this
 */
?>

<table class="pdf-table-specification">
    <?php foreach ($rootGroups as $rootGroup) : ?>
        <tr class="pdf-table-specification__root-heading">
            <td>
                <?= $rootGroup['title'] ?>
            </td>
        </tr>
        <?php foreach ($rootGroup['groups'] as $group) : ?>
            <?php foreach ($group['parts'] as $part) :
                $quantity = $part['quantity'] > 1 ? $part['quantity'] . ' x ' : '';
                $partName = $quantity . $part['partName'] . (isset($part['optionName']) ? ' ' . $part['optionName'] : '');
                ?>
                <tr>
                    <td>
                        <?= $group['title'] ?>:
                        <?= $partName ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endforeach; ?>
    <?php endforeach; ?>
</table>
