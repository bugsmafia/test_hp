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

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use HYPERPC\Helper\RenderHelper;
use HYPERPC\Joomla\Model\Entity\Interfaces\ProductMarker;

/**
 * @var RenderHelper    $this
 * @var ProductMarker   $product
 */

$itemKey = $product->getItemKey();
preg_match('/(\w+)-(.+)/', $itemKey, $matches);
list(,$type, $compareKey) = $matches;

$compareItems = $this->hyper['helper']['compare']->getItems($type);
$isInCompare  = array_key_exists($compareKey, $compareItems);
$titleAttr    = $isInCompare ? 'COM_HYPERPC_CONFIGURATOR_COMPARE_REMOVE' : 'COM_HYPERPC_CONFIGURATOR_COMPARE_ADD';

$buttonClasses = ['jsCompareAdd', 'uk-icon'];
if ($isInCompare) {
    $buttonClasses[] = 'inCompare';
}

$isMobile = $this->hyper['detect']->isMobile();
if (!$isMobile) {
    $buttonClasses[] = 'uk-transition-fade';
}

$buttonAttrs = [
    'class' => join(' ', $buttonClasses),
    'title' => Text::_($titleAttr),
    'data' => [
        'id'      => $product->get('id'),
        'type'    => $type,
        'itemKey' => $itemKey
    ]
];

$stockId = $product->getStockConfigurationId();
if (!empty($stockId)) {
    $buttonAttrs['data']['option-id'] = $stockId;
}
?>
<button <?= $this->hyper['helper']['html']->buildAttrs($buttonAttrs) ?>>
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path fill-rule="evenodd" clip-rule="evenodd" d="M1.2 0C1.86274 0 2.4 0.537258 2.4 1.2V18C2.4 19.9882 4.01178 21.6 6 21.6H22.8C23.4627 21.6 24 22.1373 24 22.8C24 23.4627 23.4627 24 22.8 24H6C2.68629 24 0 21.3137 0 18V1.2C0 0.537258 0.537258 0 1.2 0Z" fill="#9c9c9c"/>
        <path fill-rule="evenodd" clip-rule="evenodd" d="M7.20781 10.8008C7.87055 10.8008 8.40781 11.338 8.40781 12.0008L8.40781 16.8008C8.40781 17.4635 7.87055 18.0008 7.20781 18.0008C6.54507 18.0008 6.00781 17.4635 6.00781 16.8008L6.00781 12.0008C6.00781 11.338 6.54507 10.8008 7.20781 10.8008Z" fill="#9c9c9c"/>
        <path fill-rule="evenodd" clip-rule="evenodd" d="M13.2 2.38672C13.8627 2.38672 14.4 2.92398 14.4 3.58672L14.4 16.7867C14.4 17.4495 13.8627 17.9867 13.2 17.9867C12.5373 17.9867 12 17.4495 12 16.7867L12 3.58672C12 2.92398 12.5373 2.38672 13.2 2.38672Z" fill="#9c9c9c"/>
        <path fill-rule="evenodd" clip-rule="evenodd" d="M19.2078 6.00781C19.8706 6.00781 20.4078 6.54507 20.4078 7.20781L20.4078 16.8078C20.4078 17.4706 19.8706 18.0078 19.2078 18.0078C18.5451 18.0078 18.0078 17.4706 18.0078 16.8078L18.0078 7.20781C18.0078 6.54507 18.5451 6.00781 19.2078 6.00781Z" fill="#9c9c9c"/>
    </svg>
</button>
