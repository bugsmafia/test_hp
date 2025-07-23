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
 * @author      Sergey Kalistratov <kalistratov.s.m@gmail.com>
 * @author      Artem Vyshnevskiy
 *
 * @var         RenderHelper $this
 */

use JBZoo\Utils\Str;
use Joomla\CMS\Language\Text;
use HYPERPC\Helper\RenderHelper;

$form         = $this->hyper['helper']['auth']->getRegistrationForm();
$customFields = $this->hyper['helper']['auth']->getCustomRegistrationFields($form);
?>

<form class="jsRegistrationAjaxForm" action="#" method="post">
    <?php foreach ($form->getFieldsets() as $fieldset) :
        unset($fieldset->label);
        $fields = $form->getFieldset($fieldset->name);
        foreach ($customFields as $customField) {
            if (isset($fields[$customField->id])) {
                unset($fields[$customField->id]);
            }
        }

        $fields = array_merge($customFields, array_values($fields));
        ?>
        <?php if (count($fields)) : ?>
            <?php foreach ($fields as $field) :
                if (in_array(Str::low($field->type), ['spacer'])) {
                    continue;
                }

                if (!$field->hidden && $field->type !== 'Captcha') {
                    $field->class .= ' uk-input uk-form-large';
                }

                $field->size = 0;

                $icon = 'phone';
                switch ($field->fieldname) {
                    case 'name':
                        $icon = 'user';
                        $field->hint = Text::_('COM_HYPERPC_FORM_PLACEHOLDER_NAME');
                        break;

                    case 'email1':
                        $icon = 'mail';
                        $field->hint = Text::_('COM_HYPERPC_FORM_PLACEHOLDER_EMAIL');
                        break;

                    case 'phone':
                        $field->hint = Text::_('COM_HYPERPC_FORM_PLACEHOLDER_MOBILE_PHONE');
                        break;
                }
                ?>
                <div class="uk-margin">
                    <div class="uk-inline uk-width-1-1">
                        <span class="uk-form-icon uk-icon" uk-icon="icon: <?= $icon ?>"></span>
                        <?= $field->input ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    <?php endforeach; ?>

    <div class="uk-margin-top">
        <button type="submit" class="uk-button uk-button-primary uk-button-large uk-width-1-1">
            <?= Text::_('JREGISTER') ?>
        </button>

        <div class="uk-text-small uk-text-center uk-margin-top">
            <?= Text::sprintf('COM_HYPERPC_REGISTRATION_PRIVACY_TEXT', '/legal-info', strtoupper($this->hyper['params']->get('site_context'))) ?>
        </div>
    </div>

</form>
