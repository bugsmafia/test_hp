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

use HYPERPC\Helper\RenderHelper;
use HYPERPC\Joomla\Model\Entity\Interfaces\Stockable;

/**
 * @var         RenderHelper    $this
 * @var         Stockable       $entity
 * @var         array           $stores
 * @var         array           $pickingDates
 */

$stores = $this->hyper['helper']['store']->findAll();
?>

<?php if (count($stores)) :
    if (!isset($pickingDates)) {
        $pickingDates = $entity->getPickingDates();
    }
    ?>
    <ul class="jsConditionsDeliveryStores uk-list uk-list-divider uk-margin-small">
        <?php foreach ($stores as $id => $store) :
            $storeName = trim($store->getParam('city', $store->name));
            $storeName = empty($storeName) ? $store->name : $storeName;

            $storeAddress  = $store->getParam('address', $store->name);
            $storeSchedule = $store->getParam('schedule_string', '');
            $storeAddress .= !empty($storeSchedule) ? ', ' : '';

            $storeMap = $store->params->get('map_link', '');

            $isInStore   = $pickingDates->find($id . '.availableNow', false);
            $pickingDate = $pickingDates->find($id . '.pickup.value', '');
            ?>
            <li data-storeid="<?= $id ?>">
                <div class="uk-grid uk-grid-small uk-flex-nowrap">
                    <div class="uk-flex-none" style="margin-top: 7px">
                        <a href="<?= $storeMap ?>" target="_blank" rel="nofollow noopener" class="jsLoadIframe">
                            <img src="/media/hyperpc/img/other/map-teaser-50x57.png" class="uk-border-rounded" alt="">
                        </a>
                    </div>
                    <div>
                        <div class="tm-text-medium uk-text-emphasis">
                            <?= $storeName ?>
                        </div>
                        <div class="uk-text-muted" style="line-height: 1.2">
                            <?= $storeAddress ?>
                            <?php if (!empty($storeSchedule)) : ?>
                                <span class="uk-text-nowrap">
                                    <?= $storeSchedule ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="jsConditionsDeliveryStoreAvailability">
                            <?= $pickingDate ?>
                        </div>
                    </div>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
<?php else : ?>
    <?= $this->hyper['helper']['render']->render('common/order_pickup_address') ?>
<?php endif;
