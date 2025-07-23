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

namespace HYPERPC\Plugin\Fields\ColorList\Extension;

use HYPERPC\App;
use Joomla\CMS\Language\Text;
use Joomla\Component\Fields\Administrator\Plugin\FieldsListPlugin;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

final class ColorListPlugin extends FieldsListPlugin
{
    /**
     * Returns an array of key values to put in a list from the given field.
     *
     * @param   \stdClass  $field  The field.
     *
     * @return  array
     */
    public function getOptionsFromField($field)
    {
        $data = [
            '0' => Text::_('JOPTION_DO_NOT_USE')
        ];

        // Fetch the options from the plugin
        $params = clone $this->params;
        $params->merge($field->fieldparams);

        $app = App::getInstance();
        $langSef = $app->getLanguageSef();

        foreach ($params->get('options', []) as $option) {
            $op = (object) $option;
            $name = $op->name;
            if (\is_array($name)) {
                $name = $name[$langSef] ?? Text::_('JGLOBAL_SELECT_NO_RESULTS_MATCH');
            }

            $data[$op->value] = $name;
        }

        return $data;
    }
}
