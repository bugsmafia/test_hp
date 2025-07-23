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

defined('_JEXEC') or die;

use HYPERPC\Plugin\Fields\ColorList\Extension\ColorListPlugin;
use Joomla\Registry\Registry;

/**
 * @var ColorListPlugin $this
 * @var string          $context
 * @var mixed           $item 
 * @var \stdClass       $field
 * @var Registry        $fieldParams
 */

$fieldValue = $field->value;

if (empty($fieldValue)) {
    return;
}

$fieldValue = (array) $fieldValue;
$texts      = [];
$options    = $this->getOptionsFromField($field);

foreach ($options as $value => $name) {
    if (in_array((string) $value, $fieldValue)) {
        $texts[] = $name;
    }
}

echo htmlentities(implode(', ', $texts));
