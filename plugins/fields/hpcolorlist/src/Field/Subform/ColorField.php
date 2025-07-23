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

namespace HYPERPC\Plugin\Fields\ColorList\Field\Subform;

use Joomla\CMS\Form\Field\SubformField;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

class ColorField extends SubformField
{
    /**
     * The form field type.
     *
     * @var string
     */
    protected $type = 'subform.color';

    /**
     * Method to filter a field value.
     *
     * @param   mixed      $value  The optional value to use as the default for the field.
     * @param   string     $group  The optional dot-separated form group path on which to find the field.
     * @param   ?Registry  $input  An optional Registry object with the entire data set to filter
     *                            against the entire form.
     *
     * @return  mixed   The filtered value.
     *
     * @throws  \UnexpectedValueException
     */
    public function filter($value, $group = null, Registry $input = null)
    {
        $return = parent::filter($value, $group, $input);

        if (\is_array($return)) {
            foreach ($return as $index => $option) {
                if (!key_exists('key', $option) || empty($option['key'])) {
                    $return[$index]['key'] = uniqid();
                }
            }
        }

        return $return;
    }
}
