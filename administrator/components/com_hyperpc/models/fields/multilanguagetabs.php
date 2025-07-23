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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\LanguageHelper;

FormHelper::loadFieldClass('tabs');

/**
 * Class JFormFieldMultilanguageTabs
 */
class JFormFieldMultilanguageTabs extends JFormFieldTabs
{
    /**
     * The form field type.
     *
     * @var     string
     */
    public $type = 'MultilanguageTabs';

    /**
     * The form field parent type.
     *
     * @var     string
     */
    protected $parentType = 'Tabs';

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
}
