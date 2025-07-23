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
use HYPERPC\Data\JSON;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\Field\ListField;

defined('_JEXEC') or die('Restricted access');

/**
 * Class JFormFieldFields
 *
 * @since 2.0
 */
class JFormFieldOzonCategoryTree extends ListField
{

    /**
     * The form field type.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $type = 'OzonCategoryTree';

    /**
     * Name of the layout being used to render the field
     *
     * @var    string
     *
     * @since  2.0
     */
    protected $layout = 'joomla.form.field.list-fancy-select';

    /**
     * Hold items.
     *
     * @var     array
     *
     * @since   2.0
     */
    private $_items = [];

    /**
     * Build category tree.
     *
     * @param   mixed   $data
     * @param   int     $ns
     *
     * @return  void
     *
     * @since   2.0
     */
    protected function _buildTree($data, $ns = 0)
    {
        if (isset($data['children']) && is_array($data['children'])) {
            $ns++;
            foreach ($data['children'] as $_data) {
                $_data = new JSON($_data);
                if (!array_key_exists($_data->get('category_id'), $this->_items)) {
                    $title = $_data->get('title');
                    if ($ns > 1) {
                        $prefix = '';
                        for ($i = 0; $i <= $ns; $i++) {
                            $prefix .= '-';
                        }
                        $title = $prefix . $title;
                    }

                    $this->_items[$_data->get('category_id')] = $title;
                    $this->_buildTree($_data, $ns);
                }
            }
        }
    }

    /**
     * Setup items.
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    protected function _setItems()
    {
        $app = App::getInstance();
        $ozonCategoryData = $app['path']->get('elements:marketplace/ozon/resource/category_tree.php');
        if ($ozonCategoryData) {
            /** @noinspection PhpIncludeInspection */
            $data = include $ozonCategoryData;
            $this->_buildTree($data);
        }
    }

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
        $this->_setItems();

        $options = [[
            'value' => 1,
            'text'  => Text::_('COM_HYPERPC_SELECT_CATEGORY')
        ]];

        if (count($this->_items)) {
            foreach ($this->_items as $id => $title) {
                $options[$id]['value'] = $id;
                $options[$id]['text']  = $title;
            }
        }

        return array_merge(parent::getOptions(), $options);
    }
}
