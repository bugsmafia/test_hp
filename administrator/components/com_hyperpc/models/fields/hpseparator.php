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

use Joomla\CMS\Language\Text;
use HYPERPC\Joomla\Form\FormField;

defined('_JEXEC') or die('Restricted access');

/**
 * Class JFormFieldHPSeparator
 *
 * @since   2.0
 */
class JFormFieldHPSeparator extends FormField
{

    /**
     * The form field type.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $type = 'HPSeparator';

    /**
     * Name of the layout being used to render the field
     *
     * @var    string
     *
     * @since   2.0
     */
    protected $layout = 'form.field.separator';

    /**
     * Method to get a control group with label and input.
     *
     * @param   array $options
     * @return  string
     *
     * @since   2.0
     */
    public function renderField($options = [])
    {
        $title = (string) $this->element['title'];

        if (empty($title)) {
            $title = (string) $this->element['label'];
        }

        return '<div class="control-group" style="color: #da1313; font-weight: bold;">-=' .
            Text::_($title) .
        '=-</div>';
    }

    /**
     * Method to get the field input markup.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getInput()
    {
        $title = (string) $this->element['title'];
        return '<div style="color: #da1313; font-weight: bold;">-=' .
            Text::_($title) .
        '=-</div>';
    }
}
