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

use HYPERPC\Data\JSON;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Language\Text;
use HYPERPC\Helper\DateHelper;
use HYPERPC\Elements\ElementCredit;

/**
 * @var array           $history
 * @var ElementCredit   $element
 */

/** @var DateHelper $dateHelper */
$dateHelper = $this->hyper['helper']['date'];
$userTimeZone = $dateHelper->getUserTimeZone();
?>

<?php if (count($history)) : ?>
    <div class="hp-order-status-history btn-toolbar mb-3">
        <div class="d-flex flex-wrap">
            <span class="badge text-bg-primary fw-normal d-flex align-items-center my-1">
                <?= $element->getConfig('name') ?>
            </span>
            <?php
            $i = 0;
            $countHistory = count($history);
            foreach ($history as $data) :
                $i++;
                $data      = new JSON($data);
                $timestamp = $data->get('timestamp');

                /** @var JSON $statusVal */
                $statusVal = $element->getStatusByAlias($data->get('statusId'));

                $isLast    = false;
                $statusBtn = 'badge';

                if ($countHistory === $i) {
                    $isLast = true;
                    $statusBtn .= ' text-bg-success';
                } else {
                    $statusBtn .= ' text-bg-light';
                }

                $date = Date::getInstance()
                    ->setTimestamp($timestamp)
                    ->setTimezone($userTimeZone)
                    ->format(Text::_('DATE_FORMAT_FILTER_DATETIME'), true);

                $statusTitle = Text::sprintf('COM_HYPERPC_ORDER_STATUS_HISTORY_VALUE', $date);

                if ($timestamp === 0) {
                    $statusTitle = Text::_('COM_HYPERPC_ORDER_STATUS_HISTORY_NOT_FIND_TIMESTAMP');
                }

                if ($statusVal->get('label')) {
                    $statusTitle = $statusTitle . '&#013;' . $statusVal->get('label');
                }
                ?>
                <span class="badge text-dark active p-1 d-flex align-items-center justify-content-center border-0 my-1" style="padding: 0;">
                    <span class="icon-arrow-right-3 m-0"></span>
                </span>
                <span class="<?= $statusBtn ?> hasTooltip d-flex align-items-center text-nowrap fw-normal my-1" title="<?= $statusTitle ?>">
                    <?php
                    if ($statusVal->get('label')) {
                        echo explode('.', $statusVal->get('label'))[0];
                    } else {
                        echo $data->get('statusId');
                    }
                    ?>
                </span>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif;
