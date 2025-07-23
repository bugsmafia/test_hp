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
 * @author      Sergey Kalistratov <kalistratov.s.m@gmail.com>
 * @var         HyperPcViewMedia $this
 */

defined('_JEXEC') or die('Restricted access');

$input = JFactory::getApplication()->input;

$mediaPath = $this->_tmp_folder['path'];
$link = $this->app['helper']['route']->url([
    'view'   => 'media',
    'tmpl'   => 'component',
    'folder' => $mediaPath
]);

list(, $path) = explode(':', $mediaPath);
$dataPath = trim($this->app['path']->url($this->baseURL . $path, false), '/');
?>
<div class="col-md-2 mb-3">
    <a href="<?= $link ?>" class="jsChooseMedia" data-path="<?= $dataPath ?>">
        <div class="border rounded d-flex align-items-center justify-content-center media-folder">
            <div class="folder-icon"><span class="icon-folder fa-2x"></span></div>
        </div>
        <div class="text-dark">
            <?= JHtml::_('string.truncate', $this->_tmp_folder['name'], 10, false) ?>
        </div>
    </a>
</div>