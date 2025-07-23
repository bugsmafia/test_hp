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

use HYPERPC\App;
use Joomla\CMS\Language\Text;
use HYPERPC\Joomla\Model\ModelList;
use Joomla\CMS\Form\Field\ListField;
use HYPERPC\Joomla\Model\Entity\Interfaces\CategoryMarker;

/**
 * Class JFormFieldHPParent
 *
 * @since   2.0
 */
class JFormFieldHPParent extends ListField
{

    /**
     * The form field type.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $type = 'HPParent';

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
        $modelName  = ($this->element['model'] !== null) ? (string) ucfirst($this->element['model']) : 'Product_folders';
        $titleField = ($this->element['title-field'] !== null) ? (string) $this->element['title-field'] : 'title';

        /** @var HyperPcModelProduct_Folder $model */
        $model = ModelList::getInstance($modelName);

        $options = [[
            'value' => 1,
            'text'  => Text::_('JGLOBAL_ROOT_PARENT')
        ]];

        $app      = App::getInstance();
        $parentId = $app['input']->get('parent_id', 0, 'int');
        $items    = ($parentId === 0) ? $model->getFolders(false) : $model->getTable()->getTree($parentId);

        /**@var CategoryMarker $item */
        foreach ($items as $i => $item) {
            if ($item->alias === 'root') {
                $item->$titleField = Text::_('COM_HYPERPC_ROOT_CATEGORY_MENU_NAME');
            }

            if ($item->id === $app['input']->get('id', 0, 'int')) {
                continue;
            }

            $options[$i]['value'] = $item->id;
            $options[$i]['text']  = str_repeat('- ', $item->level) . $item->$titleField;
        }

        return array_merge(parent::getOptions(), $options);
    }
}
