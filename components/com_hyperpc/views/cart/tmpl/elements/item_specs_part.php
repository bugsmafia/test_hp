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

use HYPERPC\Joomla\Model\Entity\Interfaces\PartMarker;

defined('_JEXEC') or die('Restricted access');

/**
 * @var string     $itemKey
 * @var PartMarker $item
 */

$isMobile = $this->hyper['detect']->isMobile();
?>

<?php if ($item->fields) : ?>

    <?php if ($isMobile) : ?>
        <div id="<?= $itemKey ?>" hidden>
            <?= $this->hyper['helper']['render']->render('part/fields', ['part' => $item, 'fields' => $item->fields]) ?>
        </div>
    <?php else : ?>
        <?= $this->hyper['helper']['uikit']->modal($itemKey, implode(PHP_EOL, [
            '<div class="uk-container-small uk-margin-auto">',
                '<div class="uk-h2">' . $item->name . '</div>',
                $this->hyper['helper']['render']->render('part/fields', ['part' => $item, 'fields' => $item->fields]),
            '</div>'
        ])); ?>
    <?php endif; ?>

<?php endif;
