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
 * @author      Roman Evsyukov
 * @author      Artem Vyshnevskiy
 */

defined('_JEXEC') or die('Restricted access');

use HYPERPC\Helper\RenderHelper;
use HYPERPC\Joomla\Model\Entity\MoyskladService;
use HYPERPC\Joomla\Model\Entity\ProductFolder;
use JBZoo\Utils\Str;

/**
 * @var array           $compareItems
 * @var array           $groupFilters
 * @var RenderHelper    $this
 * @var MoyskladService $service
 * @var ProductFolder   $group
 */

$partWrapperAttrs = ['class' => 'tm-part-teaser'];

if (!$group) {
    $group = $service->getGroup();
}

$filters = [];
$groupFilters = isset($groupFilters) ? $groupFilters : [];
if (!empty($groupFilters)) {
    if (\is_array($service->fields)) {
        foreach ($service->fields as $field) {
            if (\array_key_exists($field->name, $groupFilters)) {
                $filters[$field->name] = Str::slug($field->value);
            }
        }

        if (!empty($filters)) {
            $partWrapperAttrs['data-field-value'] = \json_encode($filters);
        }
    }
}

$hasProperties = false;
//if ($group !== null) {
    // change to $group->partHasProperties($service->id);
    // $partFields    = $group->getPartFields($service->id, ['context' => 'moysklad_service']);
    // $hasProperties = $this->hyper['helper']['position']->hasProperties($partFields);
//}
?>

<div <?= $this->hyper['helper']['html']->buildAttrs($partWrapperAttrs) ?>>
    <div class="uk-card uk-card-small uk-card-default uk-transition-toggle tm-margin-16-top uk-flex uk-flex-column">

        <div class="uk-card-media-top uk-text-center tm-margin-32-top">
            <?= $this->render('part/teaser/common/image', [
                'part' => $service,
                'optionTakenFromPart' => false
            ]); ?>

            <?php /** @todo Repair compare for service teaser */ ?>
            <?php //if ($hasProperties) : ?>
                <?php //echo $this->render('part/teaser/common/compare', [
                    //'part'         => $service,
                    //'compareItems' => $compareItems
                //]);
                ?>
            <?php //endif; ?>
        </div>

        <div class="uk-card-body uk-width-expand uk-flex uk-flex-column uk-flex-between tm-padding-16-top">
            <h3 class="tm-part-teaser__heading uk-text-default uk-link-reset uk-margin-remove">
                <?= $this->render('part/teaser/common/heading', [
                    'part' => $service,
                    'optionTakenFromPart' => false
                ]); ?>
            </h3>

            <?php if ($service->isForRetailSale()) : ?>
                <div class="uk-flex uk-flex-between uk-flex-bottom uk-text-nowrap">
                        <?= $this->render('part/teaser/common/price', [
                        'price'  => $service->getListPrice(),
                        'entity' => $service
                    ]); ?>

                    <?= $service->getRender()->getCartBtn() ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
