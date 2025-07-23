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

defined('_JEXEC') or die('Restricted access');

use HYPERPC\Helper\DateHelper;
use Joomla\String\StringHelper;

/**
 * @var \ElementOrderYandexDelivery $this
 */

$language = $this->hyper['helper']['crm']->getCrmLanguage();
$language->load('el_' . $this->getGroup() . '_' .  $this->getType(), $this->getPath());

$output = [
    '---------------' . $language->_('COM_HYPERPC_DELIVERY') . '---------------'
];
foreach ((array) $this->getConfig('data') as $key => $value) {
    if ($key === 'shipping_cost') {
        if ($value === '-1') {
            $value = '-';
        } else {
            $value = $this->getPrice()->text();
        }
    } elseif (in_array($key, ['days_min', 'days_max']) && !empty($value)) {
        $value = sprintf($language->_('HYPER_ELEMENT_ORDER_YANDEX_DELIVERY_DAYS'), $value);
    } elseif ($key === 'need_shipping') {
        $value = ($value === '1') ?
            $language->_('HYPER_ELEMENT_ORDER_YANDEX_DELIVERY_NEED_SHIPPING_TEXT') :
            $language->_('HYPER_ELEMENT_ORDER_YANDEX_DELIVERY_PICKUP_FROM_SHOP');
    } elseif ($key === 'store') {
        $store = $this->hyper['helper']['store']->findById($value);
        $value = $store->name;
    } elseif ($key === 'store_pickup_dates') {
        /** @var DateHelper $dateHelper */
        $dateHelper = $this->hyper['helper']['date'];

        $value = $dateHelper->datesRangeToString($dateHelper->parseString($value), year:true);
    }

    if (!empty($value)) {
        $output[] = $language->_('HYPER_ELEMENT_ORDER_YANDEX_DELIVERY_' . StringHelper::strtoupper($key)) . ': ' . $value;
    }
}

$output[] = '-------------------------------------';

echo implode(PHP_EOL, $output);
