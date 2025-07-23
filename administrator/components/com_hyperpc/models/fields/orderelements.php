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
use JBZoo\Data\JSON;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\Field\ListField;

defined('_JEXEC') or die('Restricted access');

/**
 * Class JFormFieldOrderElements
 *
 * @since   2.0
 */
class JFormFieldOrderElements extends ListField
{

    /**
     * The form field type.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $type = 'OrderElements';

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
        $elementType = ((string) $this->element['form']) ? (string) $this->element['form'] : 'order';
        $hideDefault = ((bool) $this->element['hideDefault']) ? true : false;
        $app         = App::getInstance();
        $params      = (array) $app['params']->get('cart.' . $elementType, []);

        $list = [];
        if (!$hideDefault) {
            $list[] = Text::_('COM_HYPERPC_CHOOSE_CART_ELEMENT');
        }

        if (count($params)) {
            foreach ($params as $identifier => $data) {
                $data = new JSON($data);
                $list[$identifier] = $data->get('name');
            }
        }

        return $list;
    }
}
