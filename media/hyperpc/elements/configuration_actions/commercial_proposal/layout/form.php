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

defined('_JEXEC') or die('Restricted access');

use HYPERPC\Data\JSON;
use HYPERPC\Joomla\Form\Form;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

/**
 * @var     Form        $form
 * @var     FormField   $field
 * @var     JSON        $params
 */

$form = $params->get('form');

$actionUrl = $this->hyper['route']->build([
    'action'     => 'preprocess_build_pdf',
    'tmpl'       => 'component',
    'task'       => 'elements.call',
    'group'      => $this->getGroup(),
    'identifier' => $this->getIdentifier(),
    'id'         => $this->hyper['input']->get('id')
]);
?>

<div class="uk-h2 uk-text-center"><?= Text::_('HYPER_ELEMENT_CONFIGURATION_ACTIONS_COMMERCIAL_PROPOSAL_FORM_TITLE') ?></div>
<form action="<?= $actionUrl ?>" class="jsCommercialProposalPreprocessForm" method="post">
    <?php foreach ((array) $form->getFieldset() as $name => $field) : ?>
        <div class="uk-margin tm-label-infield">
            <?= $field->renderField() ?>
        </div>
    <?php endforeach; ?>
    <div class="uk-margin uk-alert uk-alert-primary">
    <?= Text::_('HYPER_ELEMENT_CONFIGURATION_ACTIONS_COMMERCIAL_PROPOSAL_FORM_ALERT') ?>
    </div>
    <div class="uk-margin uk-text-center">
        <button type="submit" class="uk-button uk-button-primary">
            <?= Text::_('HYPER_ELEMENT_CONFIGURATION_ACTIONS_COMMERCIAL_PROPOSAL_FORM_SUBMIT') ?>
        </button>
    </div>
    <?= HTMLHelper::_('form.token'); ?>
</form>
