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

JHtml::_('stylesheet', 'media/popup-imagelist.css', ['version' => 'auto', 'relative' => true]);

$path  = explode('/', $this->app['input']->getString('folder'));
$count = count($path);
$j     = 0;
$root  = $this->app['helper']['route']->url([
    'view'   => 'media',
    'tmpl'   => 'component'
]);
?>

<?php if ($count > 0) : ?>
<ul class="breadcrumb">
    <li>
        <a href="<?= $root ?>"><?= JText::_('COM_HYPERPC_MEDIA_ROOT_FOLDER') ?></a>
        <?php if ($this->app['input']->getString('folder') !== null) : ?>
            <span class="divider">/</span>
        <?php endif; ?>
    </li>
    <?php foreach ($path as $folder) :
        $j++;
        array_pop($path);
        $href = $this->app['helper']['route']->url([
            'view'   => 'media',
            'tmpl'   => 'component',
            'folder' => implode('/', $path)
        ]);
        ?>
        <?php if ($count !== $j) : ?>
            <li>
                <a href="<?= $href ?>"><?= $folder ?></a><span class="divider">/</span>
            </li>
        <?php else : ?>
            <li class="active">
                <?= $folder ?>
            </li>
        <?php endif; ?>
    <?php endforeach; ?>
</ul>
<?php endif; ?>

<?php if (count($this->images) > 0 || count($this->folders) > 0) : ?>
    <div class="manager thumbnails thumbnails-media row">
        <?php for ($i = 0, $n = count($this->folders); $i < $n; $i++) :
            $this->setFolder($i);
            echo $this->loadTemplate('folder');
        endfor; ?>

        <?php for ($i = 0, $n = count($this->images); $i < $n; $i++) :
            $this->setImage($i);
            echo $this->loadTemplate('image');
        endfor; ?>
    </div>
<?php else : ?>
    <div id="media-noimages">
        <div class="alert alert-info"><?= JText::_('COM_HYPERPC_NO_IMAGES_FOUND') ?></div>
    </div>
<?php endif; ?>

<style>
    .media-folder {
        height: 100px;
    }

    .media-image {
        height: 100px;
    }

    .media-image img{
        max-width: 100%;
        max-height: 100%;
        width: auto;
        height: auto;
    }

</style>