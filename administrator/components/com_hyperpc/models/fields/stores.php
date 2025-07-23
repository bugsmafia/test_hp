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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\Field\ListField;

defined('_JEXEC') or die('Restricted access');

/**
 * Class JFormFieldStores
 *
 * @since 2.0
 */
class JFormFieldStores extends ListField
{

    /**
     * The form field type.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $type = 'Stores';

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
        $app = App::getInstance();

        $options = [[
            'value' => '',
            'text'  => Text::_('COM_HYPERPC_SELECT_STORE')
        ]];

        $stores = $app['helper']['store']->findAll();

        foreach ($stores as $store) {
            $options[$store->id] = [
                'value' => $store->id,
                'text'  => $store->name
            ];
        }

        return array_merge(parent::getOptions(), $options);
    }
}
