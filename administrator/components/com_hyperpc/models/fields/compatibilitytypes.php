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

use JBZoo\Utils\Str;
use Joomla\CMS\Language\Text;
use HYPERPC\Compatibility\Manager;
use Joomla\CMS\Form\Field\ListField;
use HYPERPC\Compatibility\Compatibility;

defined('_JEXEC') or die('Restricted access');

/**
 * Class JFormFieldCompatibilityTypes
 *
 * @since 2.0
 */
class JFormFieldCompatibilityTypes extends ListField
{

    /**
     * The form field type.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $type = 'CompatibilityTypes';

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
        $options = [[
            'value' => null,
            'text'  => Text::_('COM_HYPERPC_FIELD_COMPATIBILITY_SELECT_DEFAULT')
        ]];

        $manager = Manager::getInstance();

        /** @var Compatibility $compatibility */
        foreach ((array) $manager->getTypes()->getArrayCopy() as $compatibility) {
            $title = Text::_('COM_HYPERPC_FIELD_COMPATIBILITY_' . Str::up($compatibility->getName()));
            $options[$compatibility->getName()]['text']  = $title;
            $options[$compatibility->getName()]['value'] = $compatibility->getName();
        }

        return array_merge(parent::getOptions(), $options);
    }
}
