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

use Joomla\CMS\Form\FormHelper;

FormHelper::loadFieldClass('CrmLeadCustomFieldAbstract');

/**
 * Class JFormFieldCrmLeadCustomFieldEnum
 *
 * @since 2.0
 */
final class JFormFieldCrmLeadCustomFieldEnum extends JFormFieldCrmLeadCustomFieldAbstract
{

    /**
     * Custom field data type key
     *
     * @var     string
     *
     * @since   2.0
     */
    protected string $dataType = self::CUSTOM_FIELD_DATATYPE_ENUM;

    /**
     * The form field type.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $type = 'CrmLeadCustomFieldEnum';
}
