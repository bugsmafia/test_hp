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
 * @link        https://github.com/HYPER-PC/HYPERPC".
 *
 * @author      Sergey Kalistratov © <kalistratov.s.m@gmail.com>
 *
 * @var         Form        $form
 * @var         FormField   $field
 * @var         JSON        $params
 */

use HYPERPC\Data\JSON;
use HYPERPC\Joomla\Form\Form;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die('Restricted access');

$form = $params->get('form');

$actionUrl = $this->hyper['route']->build([
    'action'     => 'saveForm',
    'tmpl'       => 'component',
    'task'       => 'elements.call',
    'group'      => $this->getGroup(),
    'identifier' => $this->getIdentifier(),
    'id'         => $this->hyper['input']->get('id')
]);
?>
<div class="uk-container">
    <h1><?= Text::_('HYPER_ELEMENT_CONFIGURATION_ACTIONS_DATA_CLIENT_TITLE') ?></h1>
    <form action="<?= $actionUrl ?>" method="post">
        <?php foreach ((array) $form->getFieldset(ElementConfigurationActionsData::FORM_FIELDSET) as $name => $field) : ?>
            <div class="uk-margin-small">
                <?= $field->renderField() ?>
            </div>
        <?php endforeach; ?>
        <div class="uk-margin-small">
            <button type="submit" class="uk-button uk-button-primary jsSendDataBtn">
                <?= Text::_('COM_HYPERPC_SEND') ?>
            </button>
        </div>
        <input type="hidden" name="from" value="<?= $this->hyper['input']->get('from') ?>" />
    </form>
</div>
