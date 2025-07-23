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

use JBZoo\Utils\Filter;
use Joomla\CMS\Language\Text;
use HYPERPC\Joomla\Model\ModelList;
use Joomla\CMS\Form\Field\ListField;

defined('_JEXEC') or die('Restricted access');

/**
 * Class JFormFieldHPGroups
 *
 * @since 2.0
 */
class JFormFieldHPGroups extends ListField
{

    /**
     * The form field type.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $type = 'HPGroups';

    /**
     * Name of the layout being used to render the field
     *
     * @var    string
     *
     * @since  2.0
     */
    protected $layout = 'joomla.form.field.list-fancy-select';

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
        /** @var HyperPcModelProduct_Folders $model */
        $model = ModelList::getInstance('Product_folders');

        $options = [[
            'value' => 1,
            'text'  => Text::_('JGLOBAL_ROOT_PARENT')
        ]];

        $items    = $model->getFolders(false);
        $showRoot = ((string) $this->element['show-root']) ? Filter::bool($this->element['show-root']) : true;
        foreach ((array) $items as $i => $item) {
            if ($item->alias === 'root') {
                $item->set('title', Text::_('COM_HYPERPC_ROOT_GROUP_MENU_NAME'));
                if ($showRoot === false) {
                    continue;
                }
            }

            $options[$i]['value'] = $item->id;
            $options[$i]['text']  = str_repeat('- ', $item->level) . $item->title;
        }

        return array_merge(parent::getOptions(), $options);
    }
}
