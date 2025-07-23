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
 * @author      Roman Evsyukov
 */

defined('_JEXEC') or die('Restricted access');

use HYPERPC\App;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\Language\LanguageHelper;

/**
 * Class JFormFieldMultilanguageModules
 */
class JFormFieldMultilanguageModules extends ListField
{
    /**
     * The form field type.
     *
     * @var     string
     */
    public $type = 'MultilanguageModules';

    /**
     * Name of the layout being used to render the field
     *
     * @var    string
     *
     * @since  2.0
     */
    protected $layout = 'joomla.form.field.list-fancy-select';

    /**
     * Method to get the field input markup.
     *
     * @return  string  The field input markup.
     */
    public function getInput()
    {
        if ($this->type === $this->parentType) {
            return parent::getInput();
        }

        $app = App::getInstance();

        $contentLangs = LanguageHelper::getContentLanguages();
        $langsCount = count($contentLangs);

        $tabsId = $this->fieldname . '-tabs';

        $data   = $this->getLayoutData();
        $_value = $data['value'];

        $html = $langsCount > 1 ? HtmlHelper::_('uitab.startTabSet', $tabsId) : '';
        foreach ($contentLangs as $lang) {
            $langField = clone $this;
            $langField->type = $this->parentType;
            $langField->name .= "[{$lang->sef}]";
            $langField->id .= "_{$lang->sef}";
            $langField->value = $langField->value[$lang->sef] ?? '';

            if ($langsCount > 1) {
                if (is_string($_value)) {
                    $langField->value = $app->getDefaultLanguageCode() === $lang->lang_code ? $_value : '';
                }

                $tabId = $langField->id . '_tab';

                $html .= HTMLHelper::_('uitab.addTab', $tabsId, $tabId, $lang->lang_code);
                $html .= $langField->getInput();
                $html .= HTMLHelper::_('uitab.endTab');
            } else {
                $html .= $langField->getInput();
            }
        }

        $html .= $langsCount > 1 ? HTMLHelper::_('uitab.endTabSet') : '';

        return $html;
    }

    /**
     * Method to get the field options.
     *
     * @return  array
     *
     * @throws  \Exception
     * @throws  \RuntimeException
     *
     * @since   2.0
     */
    protected function getOptions()
    {
        $app     = App::getInstance();
        $modules = $app['helper']['module']->load();

        $list = ['' => Text::_('COM_HYPERPC_CHOOSE_MODULE')];
        foreach ($modules as $module) {
            $list[$module->id] = $module->title;
        }

        return $list;
    }
}
