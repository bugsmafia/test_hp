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
 * @author      Roman Evsyukov
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\Filesystem\Path;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use HYPERPC\Joomla\Model\Entity\ProductFolder;

/**
 * @var HyperPcViewPositions $this
 * @var ProductFolder        $folder
 */

?>
<?php foreach ($this->folders as $i => $folder) :
    $categoryLink = $this->hyper['helper']['route']->url([
        'view'      => 'positions',
        'folder_id' => $folder->id
    ]);
    ?>
    <tr class="hp-group-row">
        <td class="cell-image">
            <div style="display: none;"><?= HTMLHelper::_('grid.id', $folder->id, $folder->id) ?></div>
            <?= HTMLHelper::image(Path::clean('media/hyperpc/img/icons/folder.png'), '') ?>
        </td>
        <td colspan="6">
            <a href="<?= $categoryLink ?>" title="<?= $folder->title ?>">
                <?= $folder->title ?>
            </a>
            <div class="btn-group ms-3">
                <a href="<?= $folder->getEditUrl() ?>" class="text-reset"
                   title="<?= Text::sprintf('COM_HYPERPC_CATEGORY_TITLE_EDIT', $folder->title) ?>">
                    <span class="icon-edit"></span> <?= Text::_('JACTION_EDIT') ?>
                </a>
            </div>
        </td>
        <td class="text-center hp-status-row">
            <?php
            if (in_array($folder->published, [HP_STATUS_PUBLISHED, HP_STATUS_UNPUBLISHED])) {
                echo HTMLHelper::_('jgrid.published', (int) $folder->published, $i);
            } else {
                echo $this->hyper['helper']['html']->published($folder->published);
            }
            ?>
        </td>
    </tr>
<?php endforeach;
