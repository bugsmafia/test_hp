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
use JBZoo\Data\Data;
use JBZoo\Utils\Str;
use Joomla\CMS\Language\Text;
use HYPERPC\Cart\Elements\Manager;
use Joomla\CMS\Form\Field\ListField;

defined('_JEXEC') or die('Restricted access');

/**
 * Class JFormFieldHpElements
 *
 * @since   2.0
 */
class JFormFieldHpElements extends ListField
{

    /**
     * The form field type.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $type = 'HpElements';

    /**
     * Method to get the field options.
     *
     * @return array
     *
     * @throws \Exception
     * @throws \RuntimeException
     *
     * @since   2.0
     */
    protected function getOptions()
    {
        $elementType = ((string) $this->element['elements']) ? (string) $this->element['elements'] : Manager::FIELD_TYPE_ORDER;
        $hideDefault = ((bool) $this->element['hideDefault']) ? true : false;
        $app         = App::getInstance();
        $params      = (array) $app['params']->get('cart', []);

        $list = [];
        if (!$hideDefault) {
            $list[] = Text::_('COM_HYPERPC_CHOOSE_CART_ELEMENT');
        }

        if (count($params)) {
            foreach ((array) $params as $type => $cartElements) {
                $cartElements = (array) $cartElements;
                if (count($cartElements)) {
                    foreach ($cartElements as $identifier => $data) {
                        $data = new Data($data);
                        if ($data->get('type') === $elementType) {
                            $langKey = 'COM_HYPERPC_CART_ELEMENT_' . Str::up($elementType) . '_' . Str::up($data->get('element'));
                            $name    = ($data->get('name')) ? $data->get('name') : Text::_($langKey);
                            $list[$identifier] = $name;
                        }
                    }
                }
            }
        }

        return $list;
    }
}
