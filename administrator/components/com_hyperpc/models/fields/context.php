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
use Joomla\CMS\Form\Field\ListField;

defined('_JEXEC') or die('Restricted access');

/**
 * Class JFormFieldContext
 *
 * @since 2.0
 */
class JFormFieldContext extends ListField
{

    /**
     * The form field type.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $type = 'Context';

    /**
     * Method to get the field options.
     *
     * @return  array
     *
     * @since   2.0
     */
    protected function getOptions()
    {
        $options = [];

        foreach (HP_ORDER_CONTEXT_LIST as $context) {
            $options[$context]['value'] = $context;
            $options[$context]['text']  = Str::up($context);
        }

        return array_merge(parent::getOptions(), $options);
    }
}
