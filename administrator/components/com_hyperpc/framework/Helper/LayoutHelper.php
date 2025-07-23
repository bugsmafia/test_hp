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

namespace HYPERPC\Helper;

use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Layout\LayoutHelper as JLayoutHelper;

/**
 * Class LayoutHelper
 *
 * @package HYPERPC\Helper
 *
 * @since   2.0
 */
class LayoutHelper extends AppHelper
{

    /**
     * Render layout for params
     *
     * @param   HtmlView    $data
     * @param   array       $options
     * @param   string      $layout
     * @param   string      $fieldSet
     *
     * @return  string
     *
     * @since   2.0
     */
    public function renderFieldset($data, $options = [], $layout = 'joomla.edit.fieldsetfields', $fieldSet = 'jparams')
    {
        $fieldKey = str_replace('j', '', $fieldSet);
        $prefix   = JOOMLA_FORM_CONTROL . '_' . $fieldKey . '_';

        if (isset($options['fields'])) {
            foreach ($options['fields'] as $key => $field) {
                $field = $prefix . str_replace($prefix, '', $field);
                $options['fields'][$key] = $field;
            }
        }

        $paramsSet = clone $data;
        $paramsSet->fieldset = $fieldSet;

        return JLayoutHelper::render($layout, $paramsSet, '', $options);
    }
}
