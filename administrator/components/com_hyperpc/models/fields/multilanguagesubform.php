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

use Joomla\Registry\Registry;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Form\Field\SubformField;

/**
 * Class JFormFieldMultilanguageSubform
 */
class JFormFieldMultilanguageSubform extends SubformField
{
    /**
     * The form field type.
     *
     * @var     string
     */
    public $type = 'MultilanguageSubform';

    /**
     * The form field parent type.
     *
     * @var     string
     */
    protected $parentType = 'Subform';

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

        $contentLangs = LanguageHelper::getContentLanguages();
        $langsCount = count($contentLangs);

        $tabsId = $this->fieldname . '-tabs';

        $html = $langsCount > 1 ? HtmlHelper::_('uitab.startTabSet', $tabsId) : '';
        foreach ($contentLangs as $lang) {
            $langField = clone $this;
            $langField->type = $this->parentType;
            $langField->name .= "[{$lang->sef}]";
            $langField->id .= "_{$lang->sef}";
            $langField->value = $langField->value[$lang->sef] ?? '';

            if ($langsCount > 1) {
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
     * Method to filter a field value.
     *
     * @param   mixed      $value  The optional value to use as the default for the field.
     * @param   string     $group  The optional dot-separated form group path on which to find the field.
     * @param   ?Registry  $input  An optional Registry object with the entire data set to filter
     *                             against the entire form.
     *
     * @return  mixed       The filtered value.
     *
     * @throws  \UnexpectedValueException
     */
    public function filter($value, $group = null, Registry $input = null)
    {
        $contentLangs = LanguageHelper::getContentLanguages();
        foreach ($contentLangs as $lang) {
            $langSef = $lang->sef;
            $langValue = $value->$langSef ?? [];

            $value->$langSef = parent::filter($langValue, $group, $input);
        }

        return $value;
    }

    /**
     * Method to validate a FormField object based on field data.
     *
     * @param   mixed      $value  The optional value to use as the default for the field.
     * @param   string     $group  The optional dot-separated form group path on which to find the field.
     * @param   ?Registry  $input  An optional Registry object with the entire data set to validate
     *                             against the entire form.
     *
     * @return  boolean|\Exception  Boolean true if field value is valid, Exception on failure.
     *
     * @throws  \InvalidArgumentException
     * @throws  \UnexpectedValueException
     */
    public function validate($value, $group = null, Registry $input = null)
    {
        $valid = true;

        $contentLangs = LanguageHelper::getContentLanguages();
        foreach ($contentLangs as $lang) {
            $langSef = $lang->sef;
            $langValue = $value->$langSef ?? [];

            $valid = parent::validate($langValue, $group, $input);
            if ($valid instanceof \Exception) {
                return $valid;
            }
        }

        return $valid;
    }
}
