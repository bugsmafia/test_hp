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

use JBZoo\Data\Data;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Language\Text;
use HYPERPC\Helper\RenderHelper;
use HYPERPC\Joomla\Model\Entity\Order;

/**
 * @var RenderHelper    $this
 * @var Order           $order
 */

/** @var DateHelper $dateHelper */
$dateHelper = $this->hyper['helper']['date'];
$userTimeZone = $dateHelper->getUserTimeZone();

$history = $order->status_history->getArrayCopy();
?>

<?php if (count($history)) : ?>
    <div class="hp-order-status-history btn-toolbar">
        <div class="d-flex flex-wrap">
            <span class="badge text-bg-primary fw-normal d-flex align-items-center my-1">
                <?= Text::_('Статусы заказа') ?>
            </span>
            <?php
            $i = 0;
            $newRow = false;
            $countHistory = count($history);
            foreach ($history as $data) :
                $i++;

                $data = new Data($data);

                $isLast    = false;
                $statusBtn = 'badge';
                if ($countHistory === $i) {
                    $isLast = true;
                    $statusBtn .= ' text-bg-success';
                } else {
                    $statusBtn .= ' text-bg-light';
                }

                if ($i > 15 && !$newRow) {
                    $newRow = true;
                    echo '<br /><span style="display: inline-block; width: 84px;"></span>';
                }

                $date = Date::getInstance()
                    ->setTimestamp($data->get('timestamp', 0, 'int'))
                    ->setTimezone($userTimeZone)
                    ->format(Text::_('DATE_FORMAT_FILTER_DATETIME'), true);

                $statusTitle = Text::sprintf('COM_HYPERPC_ORDER_STATUS_HISTORY_VALUE', $date);
                ?>
                <span class="badge text-dark active p-1 d-flex align-items-center justify-content-center border-0 my-1" style="padding: 0;">
                    <span class="icon-arrow-right-3 m-0"></span>
                </span>
                <span class="<?= $statusBtn ?> hasTooltip d-flex align-items-center text-nowrap fw-normal my-1" title="<?= $statusTitle ?>">
                    <?= $order->getStatus($data->get('statusId'))->name ?>
                </span>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif;
