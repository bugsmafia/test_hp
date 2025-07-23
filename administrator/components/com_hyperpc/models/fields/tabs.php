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

use Joomla\CMS\Factory;
use Joomla\CMS\Editor\Editor;
use HYPERPC\Joomla\Form\FormField;

defined('_JEXEC') or die('Restricted access');

/**
 * Class JFormFieldPartOptions
 *
 * @since 2.0
 */
class JFormFieldTabs extends FormField
{

    const MAX_HIDDEN_EDITORS = 8;

    /**
     * The form field type.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $type = 'Tabs';

    /**
     * Name of the layout being used to render the field.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $layout = 'joomla.form.field.tabs';

    /**
     * Get Joomla editor.
     *
     * @return  Editor
     *
     * @since   2.0
     */
    public function getEditor()
    {
        $config = Factory::getApplication()->getConfig();
        $editor = $config->get('editor');

        return Editor::getInstance($editor);
    }

    /**
     * Method to get the field input markup.
     *
     * @return  string
     *
     * @since   2.0
     */
    protected function getInput()
    {
        $this->hyper['helper']['assets']
            ->js('js:widget/fields/tabs.js')
            ->widget('.jsTabs', 'HyperPC.FieldTabs');

        return parent::getInput();
    }
}
