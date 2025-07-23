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

use HYPERPC\App;
use JBZoo\Utils\Str;
use HYPERPC\Data\JSON;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\Field\ListField;

defined('_JEXEC') or die('Restricted access');

/**
 * Class JFormFieldFieldsGroups
 *
 * @since 2.0
 */
class JFormFieldFieldsGroups extends ListField
{

    /**
     * The form field type.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $type = 'FieldsGroups';

    /**
     * Method to get the field options.
     *
     * @return  array
     *
     * @throws  \Exception
     * @throws  \RuntimeException
     *
     * @return  array
     *
     * @since   2.0
     */
    protected function getOptions()
    {
        $app     = App::getInstance();
        $context = ((string) $this->element['context'] !== '')   ? (string) $this->element['context'] : HP_OPTION . '.part';

        $options = [[
            'value' => 1,
            'text'  => Text::_('COM_HYPERPC_SELECT_FIELD_GROUP')
        ]];

        $context = Str::low($context);
        $items   = $app['helper']['fields']->getGroups($context);

        /** @var JSON $item */
        foreach ((array) $items as $item) {
            $options[$item->id]['value'] = $item->id;
            $options[$item->id]['text']  = $item->title;
        }

        return array_merge(parent::getOptions(), $options);
    }
}
