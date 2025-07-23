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
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;
use HYPERPC\Joomla\Form\FormField;

/**
 * Class JFormFieldNote
 *
 * @since   2.0
 */
class JFormFieldNote extends FormField
{

    /**
     * The form field type.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $type = 'Note';

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
        $note = (string) $this->element['note'];

        if (empty($note)) {
            $note = (string) $this->element['label'];
        }

        $showonAttr = '';
        if ($this->showon) {
            HTMLHelper::_('script', 'jui/cms.js', array('version' => 'auto', 'relative' => true));
            $showonAttr = ' data-showon=\'' . json_encode(FormHelper::parseShowOnConditions($this->showon, $this->formControl, $this->group)) . '\'';
        }

        return
            '<div class="alert alert-info"' . $showonAttr . '>' .
                '<span class="icon-info" aria-hidden="true"></span>' .
                Text::_($note) .
            '</div>';
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
        $note = (string) $this->element['note'];
        return $note;
    }
}
