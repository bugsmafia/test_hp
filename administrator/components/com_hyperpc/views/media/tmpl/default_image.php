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
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;

/**
 * @var HyperPcViewMedia $this
 */

$fileName  = $this->_tmp_img['name'];
$mediaPath = $this->_tmp_img['path'];
list(, $path) = explode(':', $mediaPath);

$imageUrl = $this->app['path']->url($this->baseURL . '/' . $path);
$dataPath = trim($this->app['path']->url($this->baseURL . $path, false), '/');
?>

<div class="col-md-2 mb-3">
    <a class="img-preview jsChooseMedia" href="#" title="<?= $fileName ?>" data-path="<?= $dataPath ?>">
        <div class="border rounded d-flex align-items-center justify-content-center media-image">
            <?= HTMLHelper::image($imageUrl, $fileName, [
                'class'  => 'jsChooseMedia',
                'width'  => $this->_tmp_img['width'],
                'height' => $this->_tmp_img['height']
            ]) ?>
        </div>
        <div class="text-dark">
            <?= JHtml::_('string.truncate', $fileName, 10, false) ?>
        </div>
    </a>
</div>
