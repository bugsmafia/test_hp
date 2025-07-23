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

use HYPERPC\Helper\CartHelper;
use HYPERPC\Object\Delivery\MeasurementsData;
use HYPERPC\Joomla\Model\Entity\MoyskladProduct;
use HYPERPC\Joomla\Model\Entity\MoyskladService;

/**
 * @var RenderHelper    $this
 * @var string          $itemKey
 * @var MoyskladProduct $item
 */

/** @var CartHelper */
$cartHelper = $this->hyper['helper']['cart'];

$serviceFolders = $cartHelper->getProductServiceFolders();
?>

<?php if (count($serviceFolders)) :
    $isInStock    = $item->isInStock();
    $productRate  = $cartHelper->getPositionRate($item);
    $serviceItems = $cartHelper->getServiceItems();
    $partOrder    = $this->hyper['params']->get('product_teaser_parts_order', 'a.product_folder_id ASC');
    $parts        = $item->getConfigParts(true, $partOrder, true, false);
    $promoCode    = $this->hyper['helper']['promocode']->getSessionData();
    ?>
    <?php foreach ($serviceFolders as $folder) :
        if ($item->isInStock() && $folder->id === $this->hyper['params']->get('production_time_folder', 0, 'int')) {
            continue;
        }

        $serviceParts = $item->getAllConfigParts([
            'groupIds' => [$folder->id]
        ]);

        if (count($serviceParts) <= 1) {
            continue;
        }

        $popupUrl = [
            'd_pid'     => 0,
            'item-key'  => $itemKey,
            'id'        => $item->id,
            'group_id'  => $folder->id,
            'tmpl'      => 'component',
            'task'      => 'moysklad_product.service',
            'config_id' => $item->saved_configuration
        ];

        $isDefaultService     = true;
        $defaultServicePartId = null;
        $defaultServicePrice  = 0;
        $currentService       = new MoyskladService();
        if (isset($parts[$folder->id]) && is_array($parts[$folder->id])) {
            $currentService = array_shift($parts[$folder->id]);
            $defaultServicePartId = $currentService->id;
            $defaultServicePrice  = $currentService->getListPrice()->val();

            $sessionPrice = $serviceItems->find($itemKey . '.' . $folder->id . '.price');
            $sessionId    = $serviceItems->find($itemKey . '.' . $folder->id . '.id');

            if ($sessionId) {
                $positionHelper = $this->hyper['helper']['position'];
                $sessionService = $positionHelper->expandToSubtype($positionHelper->findById($sessionId));

                $price = $this->hyper['helper']['money']->get($sessionPrice);
                $sessionService->setListPrice($price);
                $sessionService->setSalePrice($price);
                $currentService = clone $sessionService;
            }

            $popupUrl['d_pid'] = $currentService->id;
        }

        if ($defaultServicePartId && $defaultServicePartId !== $currentService->id) {
            $isDefaultService = false;
        }

        $addLinkUrl = $popupUrl;
        $addLinkUrl['d_pid'] = $defaultServicePartId;
        $resetData = [
            'serviceId' => $addLinkUrl['d_pid'],
            'itemKey'   => $addLinkUrl['item-key'],
            'productId' => $addLinkUrl['id'],
            'groupId'   => $addLinkUrl['group_id'],
            'configId'  => $addLinkUrl['config_id'],
            'price'     => $defaultServicePrice
        ];

        $overrideParams = [];
        if (in_array($folder->id, (array) $this->hyper['params']->get('package_group_moysklad', [])) && !$isDefaultService) {
            /** @var MeasurementsData $defaultDimensions */
            $defaultDimensions = $item->getDefaultDimensions();

            if (in_array($currentService->id, $this->hyper['params']->get('hyperbox_parts_moysklad', []))) { // current service is hyperbox
                /** @var MeasurementsData $boxDimensions */
                $boxDimensions = $this->hyper['helper']['moyskladProduct']->getHyperboxDimensions($item);

                $overrideParams['dimensions'] = $boxDimensions->dimensions;
                $overrideParams['weight'] = $defaultDimensions->weight + $boxDimensions->weight;
            } else { // current service is not hyperbox
                $overrideParams['dimensions'] = $defaultDimensions->dimensions;
                $overrideParams['weight'] = $defaultDimensions->weight;
            }
        }
        ?>
        <?php if ($currentService->id) :
            $servicePrice = $currentService->getListPrice();
            $sale_price   = clone $servicePrice;

            $promoType = $promoCode->get('type');
            if ($promoType === 1) {
                $currentService->setSalePrice(
                    $this->hyper['helper']['money']->get($sale_price->add('-' . $productRate . '%'))
                );
            }

            $servicePricePromo = $currentService->getSalePrice();
            ?>
            <div id="hp-service-<?= $itemKey . '-' . $folder->id ?>" class="hp-cart-item-service uk-margin-small-top" data-default="<?= $defaultServicePartId ?>" data-override-params='<?= !empty($overrideParams) ? json_encode($overrideParams) : '{}' ?>'>
                <div class="uk-flex uk-flex-middle">
                    <div class="uk-flex hp-cart-item-service__label"<?= $isDefaultService ? ' hidden' : '' ?>>
                        <div>
                            <span class="jsServiceName"><?= $currentService->getConfiguratorName() ?></span>
                            <span class="jsServicePrice uk-text-nowrap" data-original-price="<?= $servicePrice->val() ?>">(<?= $servicePricePromo->html() ?>)</span>
                        </div>
                        <button type="button" class="jsServiceReset uk-icon-link uk-flex-none hp-cart-item-service__reset" data-reset='<?= json_encode($resetData) ?>' uk-icon="close"></button>
                    </div>
                    <a href="<?= $this->hyper['route']->build($popupUrl) ?>" class="jsServiceEdit jsLoadIframe uk-icon-link uk-flex-none hp-cart-item-service__edit" uk-icon="pencil"<?= $isDefaultService ? ' hidden' : '' ?>></a>
                </div>
                <a href="<?= $this->hyper['route']->build($addLinkUrl) ?>" class="jsLoadIframe hp-cart-item-service__link uk-link-reset"<?= !$isDefaultService ? ' hidden' : '' ?>>
                    + <?= !empty($folder->getParams()->get('add_service_text', '')) ? $folder->getParams()->get('add_service_text', '') : $folder->title ?>
                </a>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
<?php endif;
