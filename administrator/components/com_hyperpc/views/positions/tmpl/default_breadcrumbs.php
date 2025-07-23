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
 * @author      Roman Evsyukov
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use HYPERPC\Joomla\Model\Entity\ProductFolder;

/**
 * @var HyperPcViewPositions $this
 * @var ProductFolder        $path
 */

?>
<ol class="breadcrumb">
    <?php foreach ($this->paths as $path) :
        $linkTitle = ($path->alias === 'root') ? Text::_('COM_HYPERPC_BREADCRUMBS_CATALOG') : $path->title;
        $pathLink  = $this->hyper['route']->build([
            'view'     => '%view',
            'folder_id' => $path->id
        ]);
        ?>
        <?php if ($this->folderId === 0 || $path->id === $this->folderId) : ?>
            <li class="active">
                <?= $linkTitle ?>
            </li>
        <?php else : ?>
            <li>
                <a href="<?= $pathLink ?>" title="<?= $linkTitle ?>"><?= $linkTitle ?></a>
                <span class="divider">/</span>
            </li>
        <?php endif; ?>
    <?php endforeach; ?>
</ol>
