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
 *
 * @var         \ElementOrderYandexDelivery $this
 */

defined('_JEXEC') or die('Restricted access');

use JBZoo\Utils\Str;
use Joomla\CMS\Language\Text;
use HYPERPC\Helper\DateHelper;

$config = (array) $this->getConfig('data');
ksort($config);
?>
<dt>--------------------</dt>
<dd>--------------------</dd>
<dt>&nbsp;</dt>
<dd><strong><?= $this->getConfig('name') ?></strong></dd>
<?php foreach ($config as $key => $value) :
    if (is_string($value)) {
        $value = strip_tags($value);
    }

    if ($key === 'shipping_cost') {
        if ($value === '-1') {
            $value = '-';
        } else {
            $value = $this->getPrice()->html();
        }
    } elseif (in_array($key, ['days_min', 'days_max']) && !empty($value)) {
        $value = Text::sprintf('HYPER_ELEMENT_ORDER_YANDEX_DELIVERY_DAYS', $value);
    } elseif ($key === 'need_shipping') {
        $value = ($value === '1') ? Text::_('HYPER_ELEMENT_ORDER_YANDEX_DELIVERY_NEED_SHIPPING_TEXT') : Text::_('HYPER_ELEMENT_ORDER_YANDEX_DELIVERY_PICKUP_FROM_SHOP');
    } elseif ($key === 'store') {
        $store = $this->hyper['helper']['store']->findById($value);
        $value = $store->name;
    } elseif ($key === 'store_pickup_dates') {
        if (empty($value)) {
            continue;
        }

        /** @var DateHelper $dateHelper */
        $dateHelper = $this->hyper['helper']['date'];

        $value = $dateHelper->datesRangeToString($dateHelper->parseString($value), year:true);
    }
    ?>
    <?php if (!empty($value)) : ?>
        <dt><?= Text::_('HYPER_ELEMENT_ORDER_YANDEX_DELIVERY_' . Str::up($key)) ?></dt>
        <dd><?= $value ?></dd>
    <?php endif; ?>
<?php endforeach; ?>
<dt>--------------------</dt>
<dd>--------------------</dd>
