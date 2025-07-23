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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\Field\ListField;

defined('_JEXEC') or die('Restricted access');

/**
 * Class JFormFieldHyperboxType
 *
 * @since 2.0
 */
class JFormFieldHyperboxType extends ListField
{

    /**
     * The form field type.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $type = 'hyperboxtype';

    /**
     * Method to get the field options.
     *
     * @return  array
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    protected function getOptions()
    {
        $app = App::getInstance();

        $options = [[
            'value' => null,
            'text'  => Text::_('COM_HYPERPC_SELECT_HYPERBOX_TYPE')
        ]];

        $boxes = $app['params']->get('hyperbox_types', []);

        if ($app['input']->get('view') === 'moysklad_product') {
            $product = $app['helper']['moyskladProduct']->getById($app['input']->get('id', 0));
            $category = $product->getFolder();
            if ($category->id) {
                $categoryBoxType = $category->params->get('hyperbox_type');
                if (isset($boxes[$categoryBoxType])) {
                    $boxFromCategoryTitle = $this->getHyperboxTitle($boxes[$categoryBoxType]);
                    $options[0]['text'] = Text::sprintf('COM_HYPERPC_SELECT_HYPERBOX_TYPE_FROM_CATEGORY', $boxFromCategoryTitle);
                }
            }
        }

        foreach ($boxes as $key => $box) {
            $text = $this->getHyperboxTitle($box);
            $options[$key]['text'] = $text;
            $options[$key]['value'] = $key;
        }

        return array_merge(parent::getOptions(), $options);
    }

    /**
     * Get hyperbox title
     *
     * @param   array
     * @return  string
     *
     * @since   2.0
     */
    protected function getHyperboxTitle($box)
    {
        return $box['title'] . ' (' . implode(' / ', [$box['width'], $box['length'], $box['height']]) . ')';
    }
}
