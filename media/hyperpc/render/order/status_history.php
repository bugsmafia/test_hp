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
use HYPERPC\Helper\DateHelper;
use HYPERPC\Helper\RenderHelper;
use HYPERPC\Joomla\Model\Entity\Order;

/**
 * @var RenderHelper    $this
 * @var Order           $order
 */

$statuses      = $order->getAllowedStatusList();
$countStatuses = count($statuses);

/** @var DateHelper $dateHelper */
$dateHelper = $this->hyper['helper']['date'];
$userTimeZone = $dateHelper->getUserTimeZone();

$i = 0;
?>
<?php if ($countStatuses) : ?>
    <div class="uk-h4 uk-margin-small-bottom">
        <?= Text::_('COM_HYPERPC_ORDER_STATUS_LIST_TITLE') ?>
    </div>
    <div class="uk-margin-bottom" uk-margin>
        <?php
        foreach ($statuses as $status) :
            $i++;
            $status = new Data($status);
            $statusItem = $order->getStatus($status->get('statusId'));

            if (!$statusItem->id) {
                continue;
            }

            $isLast    = false;
            $statusLbl = 'uk-label';
            if ($countStatuses === $i) {
                $isLast = true;
                $statusLbl .= ' uk-label-success';
            }

            $date = Date::getInstance()
                ->setTimestamp($status->get('timestamp', 0, 'int'))
                ->setTimezone($userTimeZone)
                ->format(Text::_('DATE_FORMAT_FILTER_DATETIME'), true);
            ?>
            <div class="uk-inline">
                <span class="<?= $statusLbl ?>" style="cursor: pointer;">
                    <?= $statusItem->name ?>
                </span>
                <div data-uk-drop="pos: bottom; animation: uk-animation-slide-top-small; duration: 200" class="uk-drop uk-drop-bottom-center">
                    <div class="uk-card uk-card-body uk-card-small uk-card-default uk-text-small">
                        <?php if ($statusItem->params->get('description')) : ?>
                            <div><?= $statusItem->params->get('description') ?></div>
                            <hr class="uk-margin-small">
                        <?php endif; ?>
                        <b><?= Text::_('COM_HYPERPC_ORDER_STATUS_STEP_SET_TITLE') ?></b>&nbsp;<?= $date ?>
                    </div>
                </div>
            </div>
            <?php if (!$isLast) : ?>
                <span class="uk-text-middle uk-display-inline-block">
                    <span class="uk-icon" uk-icon="arrow-right"></span>
                </span>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
<?php endif;
