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
 * Class JFormFieldWorker
 *
 * @since 2.0
 */
class JFormFieldWorker extends ListField
{

    /**
     * The form field type.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $type = 'Worker';

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
     * @return  array
     *
     * @since   2.0
     */
    protected function getOptions()
    {
        $app = App::getInstance();

        $options = [[
            'value' => '',
            'text'  => Text::_('COM_HYPERPC_SELECT_WORKER')
        ]];

        $workers = $app['helper']['worker']->getWorkers();
        /** @var \HYPERPC\Joomla\Model\Entity\Worker $item */
        foreach ((array) $workers as $item) {
            $options[$item->id]['value'] = $item->id;
            $options[$item->id]['text']  = $item->name;
        }

        return array_merge(parent::getOptions(), $options);
    }
}