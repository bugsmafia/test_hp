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

use HYPERPC\App;
use Joomla\Registry\Registry;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Form\Field\TextareaField;

/**
 * Class JFormFieldMultilanguageTextarea
 */
class JFormFieldMultilanguageTextarea extends TextareaField
{

    /**
     * The form field type.
     *
     * @var     string
     */
    protected $type = 'MultilanguageTextarea';

    /**
     * The form field parent type.
     *
     * @var     string
     */
    protected $parentType = 'Textarea';

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

        $data   = $this->getLayoutData();
        $_value = $data['value'];

        $i = 0;

        $html = '';
        foreach ($contentLangs as $langKey => $lang) {
            $langField = clone $this;
            $langField->type = $this->parentType;
            $langField->name .= "[{$lang->sef}]";
            $langField->id .= '_' . $lang->sef;
            $langField->value = $langField->value[$lang->sef] ?? '';

            if (is_string($_value)) {
                $langField->value = $app->getDefaultLanguageCode() === $lang->lang_code ? $_value : '';
            }

            $langLabel = $langsCount > 1 ? '<span>' . $lang->lang_code . '</span>' : '';
            $html .= '<div class="' . (++$i < $langsCount ? 'mb-2' : '') . '">' . $langLabel . $langField->getInput() . '</div>';
        }

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
            $langValue = $value->$langSef ?? '';

            $value->$langSef = parent::filter($langValue, $group, $input);
        }

        return $value;
    }
}
