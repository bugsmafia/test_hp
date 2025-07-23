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

use HYPERPC\Money\Type\Money;
use Joomla\CMS\Language\Text;
use HYPERPC\Joomla\Model\Entity\Entity;

defined('_JEXEC') or die('Restricted access');

/**
 * @var Entity $item
 * @var Money $price
 */

$onRemove = null;
if ($this->hyper['helper']['google']->enabledGtm()) {
    $onRemove = $this->hyper['helper']['google']->getJsFunctionRemoveFromCartName();
}

$attrs = [
    'class'   => 'jsRemoveItem uk-text-danger',
    'title'   => Text::sprintf('COM_HYPERPC_CART_DELETE_ITEM', $item->name),
    'data'    => [
        'remove-callback' => $onRemove
    ]
];
?>
<a uk-tooltip <?= $this->hyper['helper']['html']->buildAttrs($attrs) ?>>
    <span uk-icon="icon: trash"></span>
</a>
