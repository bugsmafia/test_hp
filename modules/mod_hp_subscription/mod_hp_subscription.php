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
use HYPERPC\Data\JSON;
use Joomla\CMS\Uri\Uri;
use JBZoo\Utils\Filter;
use HYPERPC\Joomla\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Helper\ModuleHelper;

$module = new JSON($module);
$hyper  = App::getInstance();

$formPath = $hyper['path']->get('site:models/forms');

Form::addFormPath($formPath);
$form = Form::getInstance(HP_OPTION . '.lead', 'lead', ['control' => 'jform']);

if ($params->get('enable_phone')) {
    $element = new SimpleXMLElement('
        <field name="phone" maxlength="18" default="+7 ("  type="tel"  required="required"
            class="uk-input uk-form-width-large" 
            label="MOD_HP_SUBSCRIPTION_PHONE_LABEL" 
            pattern="' . HP_PHONE_REGEX . '"
            hint="MOD_HP_SUBSCRIPTION_PHONE_HINT"/>');

    $form->setField($element);
}

$form->bind([
    'module_id' => $module->get('id'),
    'type'      => $params->get('leads_type', 1),
    'params'    => [
        'page_url' => Uri::current()
    ]
]);

$formId     = 'hp-form-' . $module->get('id');
$formAction = '/index.php?option=com_hyperpc';

$btnTitle      = $params->get('btn_label', Text::_('MOD_HP_SUBSCRIPTION_BUTTON_LABEL'));
$modalTitle    = $params->get('modal_title', Text::_('MOD_HP_SUBSCRIPTION_MODAL_TITLE'));
$modalBtnTitle = $params->get('modal_btn_label', Text::_('MOD_HP_SUBSCRIPTION_BUTTON_SUBMIT_LABEL'));
$saveUrl       = Filter::bool($params->get('save_url', 0));

/** @noinspection PhpIncludeInspection */
require ModuleHelper::getLayoutPath('mod_hp_subscription', $params->get('layout', 'default'));
