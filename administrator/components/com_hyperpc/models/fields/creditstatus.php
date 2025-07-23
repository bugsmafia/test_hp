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
 */

use Cake\Utility\Xml;
use HYPERPC\Data\JSON;
use HYPERPC\Joomla\Form\Form;
use Joomla\CMS\Form\FormHelper;
use HYPERPC\Joomla\Form\FormField;

defined('_JEXEC') or die('Restricted access');

/**
 * Class JFormFieldCreditStatus
 *
 * @since 2.0
 */
class JFormFieldCreditStatus extends FormField
{

    /**
     * Name of the layout being used to render the field.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $layout = 'joomla.form.field.credit_status';

    /**
     * The form field type.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $type = 'CreditStatus';

    /**
     * Render pipeline field.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function renderLeadPipelines()
    {
        $xml = Xml::build([
            'field' => [
                '@name'  => 'pipeline',
                '@type'  => 'pipelines',
                '@label' => 'COM_HYPERPC_PIPELINE_SITE'
            ]
        ]);

        $value = new JSON($this->value);

        FormHelper::loadFieldClass('Pipelines');

        $formName = 'credit_pipeline_status_' . $this->formControl . $this->fieldname;

        $form = Form::getInstance($formName, $xml->asXML(), [
            'control' => $this->formControl . '[' . $this->fieldname . ']'
        ]);

        $field = new JFormFieldPipelines($form);
        $field->setup($xml, $value->get('pipeline'));

        return $field->renderField();
    }

    /**
     * Render order status field.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function renderOrderStatus()
    {
        $value = new JSON($this->value);
        FormHelper::loadFieldClass('OrderStatus');

        $xml = Xml::build([
            'field' => [
                '@name'  => 'site_status',
                '@type'  => 'orderstatus',
                '@label' => 'COM_HYPERPC_STATUS_SITE'
            ]
        ]);

        $formName = 'credit_order_status' . $this->formControl . $this->fieldname;

        $form = Form::getInstance($formName, $xml->asXML(), [
            'control' => $this->formControl . '[' . $this->fieldname . ']'
        ]);

        $field = new JFormFieldOrderStatus($form);
        $field->setup($xml, $value->get('site_status'));

        return $field->renderField();
    }
}
