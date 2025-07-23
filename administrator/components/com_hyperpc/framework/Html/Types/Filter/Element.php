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
 */

namespace HYPERPC\Html\Types\Filter;

use HYPERPC\Html\Types\Type;

defined('_JEXEC') or die('Restricted access');

/**
 * Class Element
 *
 * @property-read   string  $name
 * @property-read   string  $title
 * @property-read   string  $type
 * @property-read   bool    $hasActive
 * @property-read   bool    $hasFilters
 * @property-read   string  $html
 *
 * @package         HYPERPC\Html\Types\Filter
 *
 * @since           2.0
 */
class Element extends Type
{

    /**
     * List name of available properties.
     *
     * @var     array
     *
     * @since   2.0
     */
    protected $_properties = [
        'name',
        'title',
        'type',
        'hasActive',
        'hasFilters',
        'html'
    ];
}
