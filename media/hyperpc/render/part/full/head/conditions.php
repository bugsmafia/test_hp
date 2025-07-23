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

use Joomla\CMS\Language\Text;
use HYPERPC\Helper\RenderHelper;
use HYPERPC\Joomla\Model\Entity\Interfaces\Stockable;
use HYPERPC\Joomla\Model\Entity\Interfaces\PartMarker;
use HYPERPC\Joomla\Model\Entity\Interfaces\OptionMarker;

/**
 * @var string            $price
 * @var bool              $onlyForUpgrade
 * @var RenderHelper      $this
 * @var PartMarker        $part
 * @var OptionMarker|null $option
 * @var OptionMarker[]    $options
 */

$entity = $part;
if (isset($option) && $option instanceof OptionMarker && $option->id) {
    $entity = $option;
}

if (!isset($onlyForUpgrade)) {
    $onlyForUpgrade = false;
}

$availability = $entity->getAvailability();

$icon = '';
$textClass = '';
switch ($availability) {
    case Stockable::AVAILABILITY_INSTOCK:
        $icon = 'check';
        $textClass = ' uk-text-success';
        break;
    case Stockable::AVAILABILITY_PREORDER:
    case Stockable::AVAILABILITY_OUTOFSTOCK:
        $icon = 'clock';
        $textClass = ' uk-text-warning';
        break;
    case Stockable::AVAILABILITY_DISCONTINUED:
        $icon = 'ban';
        $textClass = ' uk-text-danger';
        break;
}

$jsSupport = !empty($options);
?>

<div class="hp-part-head__conditions uk-margin-bottom">
    <div class="uk-grid" uk-grid>
        <?php if ($part->isForRetailSale() && $this->hyper['input']->get('tmpl') !== 'component') : ?>
            <div>
                <span class="hp-conditions-item uk-flex uk-flex-middle<?= $jsSupport ? ' jsAvailabilityConditionItem' : '' ?>">
                    <span class="hp-conditions-item__icon">
                        <span uk-icon="<?= $icon ?>" class="uk-icon"></span>
                    </span>
                    <span>
                        <span class="hp-conditions-item__text<?= $textClass ?>">
                            <?= Text::_('COM_HYPERPC_AVAILABILITY_LABEL_' . \strtoupper($availability)) ?>
                        </span>
                        <span class="hp-conditions-item__sub">
                            <?php if ($onlyForUpgrade) : ?>
                                <?= Text::_('COM_HYPERPC_ONLY_FOR_UPGRADE') ?>
                            <?php else : ?>
                                <?php if (!$jsSupport) : ?>
                                    <?php if ($availability === Stockable::AVAILABILITY_INSTOCK) : ?>
                                        <a href="#delivery-options" class="uk-link-muted tm-link-dashed" uk-toggle>
                                            <?= Text::_('COM_HYPERPC_WAYS_TO_RECEIVE') ?>
                                        </a>
                                    <?php else : ?>
                                        <?= Text::_('COM_HYPERPC_PART_CONDITIONS_' . \strtoupper($availability) . '_SUB') ?>
                                    <?php endif; ?>
                                <?php else : // js support
                                    $availabilities = \array_unique(
                                        \array_map(
                                            fn($_option) => $_option->getAvailability(),
                                            $options
                                        )
                                    ); 
                                    ?>
                                    <?php foreach ($availabilities as $_availability) :
                                        $isHidden = $_availability !== $availability;
                                        ?>
                                        <?php if ($_availability === Stockable::AVAILABILITY_INSTOCK) : ?>
                                            <a href="#delivery-options" class="jsConditionsDeliveryLink uk-link-muted tm-link-dashed" uk-toggle>
                                                <?= Text::_('COM_HYPERPC_WAYS_TO_RECEIVE') ?>
                                            </a>
                                        <?php else : ?>
                                            <span class="jsConditions<?= $_availability ?>Sub"<?= $isHidden ? ' hidden' : '' ?>>
                                                <?= Text::_('COM_HYPERPC_PART_CONDITIONS_' . \strtoupper($_availability) . '_SUB') ?>
                                            </span>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            <?php endif; ?>
                        </span>
                    </span>
                </span>
            </div>
        <?php endif; ?>
        <div>
            <?= $this->hyper['helper']['render']->render('common/full/question/button', [
                'itemName' => $entity->getPageTitle(),
                'price'    => $price,
                'type'     => 'part'
            ]); ?>
        </div>
    </div>
</div>

<?php if ($part->isForRetailSale() && !$part->isDiscontinued()) : ?>
    <?= $this->hyper['helper']['render']->render('common/full/shipping-modal', [
        'entity'     => $entity,
        'parcelData' => $part->getDimensions()
    ]); ?>
<?php endif;
