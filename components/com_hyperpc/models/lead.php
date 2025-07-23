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

use HYPERPC\Joomla\Model\ModelAdmin;

/**
 * Class HyperPcModelLead
 *
 * @since   2.0
 */
class HyperPcModelLead extends ModelAdmin
{

    /**
     * Getting the form from the model.
     *
     * @param   array $data
     * @param   bool $loadData
     *
     * @return  bool|\Joomla\CMS\Form\Form
     *
     * @since   2.0
     */
    public function getForm($data = [], $loadData = true)
    {
        $form = $this->loadForm(HP_OPTION . '.lead', 'lead', [
            'control'   => 'jform',
            'load_data' => $loadData
        ]);

        if ($form === null) {
            return false;
        }

        return $form;
    }

    /**
     * Method to validate the form data.
     *
     * @param   \JForm  $form   The form to validate against.
     * @param   array   $data   The data to validate.
     * @param   string  $group  The name of the field group to validate.
     *
     * @return  array|boolean  Array of filtered data if valid, false otherwise.
     *
     * @see     \JFormRule
     * @see     \JFilterInput
     * @since   1.6
     */
    public function validate($form, $data, $group = null)
    {
        if (isset($data['phone'])) {
            $element = new SimpleXMLElement('
                <field name="phone" type="tel" class="uk-input uk-form-width-large" required="required"
                    label="MOD_HP_SUBSCRIPTION_PHONE_LABEL" 
                    pattern="' . HP_PHONE_REGEX . '"
                    hint="MOD_HP_SUBSCRIPTION_PHONE_HINT" maxlength="18" default="+7 ("/>');

            $form->setField($element);
        }

        return parent::validate($form, $data, $group);
    }
}
