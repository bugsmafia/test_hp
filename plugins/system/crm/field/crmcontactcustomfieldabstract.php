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

FormHelper::loadFieldClass('CrmCustomFieldAbstract');

/**
 * Class JFormFieldCrmContactCustomFieldAbstract
 *
 * @since 2.0
 */
abstract class JFormFieldCrmContactCustomFieldAbstract extends JFormFieldCrmCustomFieldAbstract
{
    /**
     * Get custom fields list
     *
     * @return  array
     *
     * @since   2.0
     */
    final protected function getCustomFieldsList(): array
    {
        if (!key_exists(__CLASS__, self::$crmCustomFields)) {
            try {
                self::$crmCustomFields[__CLASS__] = $this->crmHelper->getContactCustomFieldsList()->getArrayCopy();
            } catch (\Throwable $th) {
                self::$crmCustomFields[__CLASS__] = [];
            }
        }

        return self::$crmCustomFields[__CLASS__];
    }
}
