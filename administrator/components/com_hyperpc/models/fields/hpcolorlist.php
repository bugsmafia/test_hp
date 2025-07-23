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

use HYPERPC\App;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die('Restricted access');

/**
 * Class JFormFieldColorList
 *
 * @since 2.0
 */
class JFormFieldHpColorList extends ListField
{
    /**
     * The form field type.
     *
     * @var string
     */
    protected $type = 'HpColorList';

    /**
     * Method to get the field options.
     *
     * @return  array
     */
    protected function getOptions()
    {
        $options = [[
            'value' => 0,
            'text'  => Text::_('JFIELD_COLOR_SELECT')
        ]];

        $app = App::getInstance();

        $colorsFieldId = $app['params']->get('product_colors_field', 0, 'int');
        $colorsField = $app['helper']['fields']->getFieldById($colorsFieldId);

        if (!$colorsField) {
            return $options;
        }

        $langSef = $app->getLanguageSef();

        foreach ($colorsField->fieldparams->get('options', [], 'arr') as $rowKey => $data) {
            $key = $data['key'];
            $text = $data['name'];
            if (\is_array($text)) {
                $text = $text[$langSef] ?? Text::_('JGLOBAL_SELECT_NO_RESULTS_MATCH');
            }

            $options[$rowKey]['value'] = $key;
            $options[$rowKey]['text'] = $text;
        }

        return $options;
    }
}
