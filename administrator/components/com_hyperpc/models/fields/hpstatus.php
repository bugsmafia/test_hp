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
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use HYPERPC\Joomla\Form\FormField;

/**
 * Class JFormFieldHPStatus
 *
 * @since   2.0
 */
class JFormFieldHPStatus extends FormField
{

    /**
     * Name of the layout being used to render the field
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $layout = '';

    /**
     * The form field type.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $type = 'HPStatus';

    /**
     * Method to get the field input markup.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getInput()
    {
        return '';
    }

    /**
     * Method to get a control group with label and input.
     *
     * @param   array $options
     *
     * @return  string
     *
     * @since   2.0
     */
    public function renderField($options = [])
    {
        $label  = (string) $this->element['label'];
        $hidden = "<input type='hidden' name='{$this->name}' value='{$this->value}' />";

        return implode(PHP_EOL, [
            '<div class="control-group">',
                $hidden,
                Text::_($label) . ' : ' .$this->hyper['helper']['html']->published($this->value) .
            '</div>'
        ]);
    }
}
