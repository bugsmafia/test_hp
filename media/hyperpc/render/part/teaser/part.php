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

use HYPERPC\Helper\RenderHelper;
use HYPERPC\Joomla\Model\Entity\Interfaces\Stockable;
use HYPERPC\Joomla\Model\Entity\MoyskladPart;
use HYPERPC\Joomla\Model\Entity\MoyskladVariant;
use HYPERPC\Joomla\Model\Entity\ProductFolder;
use JBZoo\Utils\Str;

/**
 * @var array $compareItems
 * @var array $groupFilters
 * @var RenderHelper $this
 * @var MoyskladPart $part
 * @var ProductFolder $group
 */

$partWrapperAttrs = ['class' => 'tm-part-teaser'];

if (!$group) {
    $group = $part->getGroup();
}

$optionTakenFromPart = false;
if ($part->option instanceof MoyskladVariant && $part->option->id) {
    $optionTakenFromPart = true;
}

$option = $optionTakenFromPart ? $part->option : $part->getDefaultOption(true);
if ($option->id) {
    $part->setListPrice($option->getListPrice());
    $part->set('option', $option);
}

$availability = $optionTakenFromPart ? $option->getAvailability() : $part->getAvailability();

$filters = [];
$groupFilters = isset($groupFilters) ? $groupFilters : [];

if (!empty($groupFilters)) {
    if (is_array($part->fields) && !$part->isDiscontinued()) {
        foreach ($part->fields as $field) {
            if (array_key_exists($field->name, $groupFilters)) {
                $filters[$field->name] = Str::slug($field->value);
            }
        }

        if (!empty($filters)) {
            $partWrapperAttrs['data-field-value'] = json_encode($filters);
        }
    }
}

$hasProperties = false;
if ($group !== null) {
    $hasProperties = $group->partHasProperties($part->id);
}
?>

<div <?= $this->hyper['helper']['html']->buildAttrs($partWrapperAttrs) ?>>
    <?php if (!$this->hyper['helper']['request']->isAjax()) : ?>
        <?= $this->hyper['helper']['microdata']->getEntityMicrodata($part, $option); ?>
    <?php endif; ?>
    <div class="uk-card uk-card-small uk-card-default uk-transition-toggle tm-margin-16-top uk-flex uk-flex-column">

        <div class="uk-card-media-top uk-text-center tm-margin-32-top">
            <?= $this->render('part/teaser/common/image', [
                'part' => $part,
                'optionTakenFromPart' => $optionTakenFromPart
            ]); ?>

            <?php if ($part->isForRetailSale()) : ?>
                <div class="tm-padding-24-left tm-padding-16-top uk-position-top-left tm-text-size-14">
                    <?= $this->render('part/teaser/common/availability', ['availability' => $availability]); ?>
                </div>
            <?php endif; ?>

            <?php if ($hasProperties) : ?>
                <div class="tm-part-teaser__icons tm-padding-24-right tm-padding-16-top uk-position-top-right">
                    <?= $this->render('part/teaser/common/compare', [
                            'part'         => $part,
                            'option'       => $option,
                            'compareItems' => $compareItems
                        ]);
                    ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="uk-card-body uk-width-expand uk-flex uk-flex-column uk-flex-between tm-padding-16-top">

            <h3 class="tm-part-teaser__heading uk-text-default uk-link-reset uk-margin-remove">
                <?= $this->render('part/teaser/common/heading', [
                    'part' => $part,
                    'optionTakenFromPart' => $optionTakenFromPart
                ]); ?>
            </h3>

            <?php if ($part->isForRetailSale()) : ?>
                <div class="tm-margin-16-top">
                    <?php if ($availability !== Stockable::AVAILABILITY_DISCONTINUED) : ?>
                        <div class="uk-flex uk-flex-between uk-flex-bottom uk-text-nowrap">
                             <?= $this->render('part/teaser/common/price', [
                                'price'  => $part->getListPrice(),
                                'entity' => $part
                            ]); ?>

                            <?= $part->getRender()->getCartBtn() ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>
