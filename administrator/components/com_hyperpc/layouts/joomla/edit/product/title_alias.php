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
 * @var         \Joomla\CMS\Layout\FileLayout $this
 * @var         \Joomla\CMS\Form\Form $form
 * @var         HyperPcViewProduct $displayData
 */

defined('_JEXEC') or die('Restricted access');

$form  = $displayData->getForm();
$title = $form->getField('title') ? 'title' : ($form->getField('name') ? 'name' : '');
?>
<div class="row title-alias form-vertical mb-3">
    <div class="col-12 col-md-6">
        <?php echo $title ? $form->renderField($title) : ''; ?>
    </div>
    <div class="col-12 col-md-6">
        <?php echo $form->renderField('alias'); ?>
    </div>
    <div class="col-12 col-md-6">
        <?php echo $displayData->app['helper']['layout']->renderFieldset($displayData, ['fields' => [
            'title'
        ]]); ?>
    </div>
    <div class="col-12 col-md-6">
        <?php echo $displayData->app['helper']['layout']->renderFieldset($displayData, ['fields' => [
            'show_title'
        ]]); ?>
    </div>
</div>
