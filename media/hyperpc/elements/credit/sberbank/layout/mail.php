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

/**
 * @var ElementCreditSberbank $this
 */

$leadUrl = $this->_order->getAmoLeadUrl();
$cabinetLink = $this->getCabinetLink();

$total = $this->_order->getTotal()->text();
$amount = '';
if ($this->getRequestData()->get('amount')) {
    $amount = $this->hyper['helper']['money']->get(((int) $this->getRequestData()->get('amount')) / 100)->text();
}
?>
<p>
    Заявка по заказу <?= $this->_order->getName() ?> переведена в статус <strong><?= $this->getStatusTitle() ?></strong>
</p>
<ul>
    <li><?= sprintf('Стоимость заказа: %s', $this->_order->getTotal()->text()) ?></li>
    <?php if ($amount && $amount !== $total) : ?>
        <li><?= sprintf('Переведено средств: %s', $amount) ?></li>
    <?php endif; ?>
</ul>

<?php if ($leadUrl) : ?>
    <p>
        <strong>Подробнее в AmoCRM:</strong>
        <br />
        <a href="<?= $leadUrl ?>"><?= $leadUrl ?></a>
    </p>
<?php endif; ?>
<?php if ($cabinetLink) : ?>
    <p>
        <strong>Подробнее в СберБанке:</strong>
        <br />
        <a href="<?= $cabinetLink ?>" target="_blank"><?= $cabinetLink ?></a>
    </p>
<?php endif;
