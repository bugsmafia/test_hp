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

use HYPERPC\App;
use JBZoo\Utils\Str;
use Joomla\CMS\Language\Text;

/** @var array $displayData */

$app = App::getInstance();

$mediaUrl = $app['helper']['route']->url([
    'view' => 'media',
    'tmpl' => 'component'
]);
?>
<div class="field-hp-image" id="<?= Str::slug($displayData['id']) ?>">
    <div class="input-group">
        <input type="text" name="<?= $displayData['name'] ?>" value="<?= $displayData['value'] ?>" class="jsImageInput form-control" placeholder="<?= JText::_('COM_HYPERPC_FIELD_IMAGE_PLACEHOLDER') ?>" />
        <a href="#" data-href="<?= $mediaUrl ?>" class="btn btn-success add-on jsImageSelect"><?= JText::_('JSELECT') ?></a>
        <a class="btn btn-danger button-clear jsImageClear" title="<?= JText::_('JSEARCH_FILTER_CLEAR') ?>">
            <span class="icon-times" aria-hidden="true"></span>
            <span class="visually-hidden"><?= Text::_('JLIB_FORM_BUTTON_CLEAR'); ?></span>
        </a>
    </div>
</div>
