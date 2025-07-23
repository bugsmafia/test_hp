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
use HYPERPC\Elements\Manager;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use HYPERPC\Helper\RenderHelper;
use HYPERPC\Elements\ElementConfiguratorActions;
use HYPERPC\Joomla\Model\Entity\SaveConfiguration;

/**
 * @var RenderHelper        $this
 * @var SaveConfiguration[] $configurations
 */

$allowedElements = [];

/** @var ElementConfiguratorActions[] $elementList */
$elementList = Manager::getInstance()->getByPosition(Manager::ELEMENT_TYPE_CONFIGURATION_ACTIONS);

foreach ($elementList as $element) {
    if ($element->canDo() && $element->isEnabled()) {
        $groupKey = ($element->isSingle()) ? 'single' : 'more';
        $allowedElements[$groupKey][$element->getType()] = $element;
    }
}

$allowedElements = new JSON($allowedElements);
?>

<table class="uk-table uk-table-responsive uk-table-divider">
    <thead>
        <tr>
            <th width="1%" hidden>
                <input class="uk-checkbox" type="checkbox" name="checkall-toggle" value="" onclick="Joomla.checkAll(this)"/>
            </th>
            <th class="uk-padding-remove-horizontal" style="width: 120px;"></th>
            <th class="uk-padding-remove-left">
                <?= Text::_('COM_HYPERPC_CONFIGURATION_NUMBER') ?>
            </th>
            <th class="uk-width-small">
                <?= Text::_('COM_HYPERPC_PRICE') ?>
            </th>
            <th class="uk-width-small">
                <?= Text::_('COM_HYPERPC_UPDATED') ?>
            </th>
            <th class="uk-table-shrink"></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($configurations as $item) :
            $product = $item->getProduct();

            if (!$product->id || !$product->isPublished()) {
                continue;
            }

            $imgSrc = $this->hyper['helper']['cart']->getItemImage($product);
            ?>
            <tr id="hp-configuration-<?= $item->id ?>" class="jsConfigurationsTableItem hp-configurations-table-item" data-configuration-id="<?= $item->id ?>">
                <td class="uk-text-center" hidden>
                    <input type="checkbox" class="uk-checkbox" id="cb<?= $item->id ?>" name="cid[]"
                        value="<?= $item->id ?>" onclick="Joomla.isChecked(this.checked);">
                </td>
                <td class="hp-configurations-table-item__image-cell uk-text-center uk-padding-remove-horizontal" style="width:120px !important">
                    <img src="<?= $imgSrc ?>" alt="" />
                </td>

                <td class="hp-configurations-table-item__info-cell uk-padding-remove-left">
                    <div class="uk-text-primary ">
                        <a href="<?= $item->getViewUrl() ?>" class="uk-link-reset jsGoToConfigurator">
                            #<?= $item->getName() ?>
                        </a>
                    </div>
                    <div class="uk-text-emphasis">
                        <?= $product->name ?>
                        <?php if (count((array) $allowedElements->get('single'))) : ?>
                            <div>
                                <?php
                                /** @var ElementConfiguratorActions $sElement */
                                foreach ((array) $allowedElements->get('single') as $sElement) {
                                    $sElement->setConfig(['configuration' => $item]);
                                    echo $sElement->renderActionButton();
                                }
                                ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </td>

                <td class="hp-configurations-table-item__price-cell uk-text-nowrap">
                    <?= $item->getDiscountedPrice()->text() ?>
                </td>

                <td class="hp-configurations-table-item__date-cell uk-text-muted uk-text-nowrap">
                    <?= HTMLHelper::date($item->getLastModifiedDate(), Text::_('DATE_FORMAT_LC5')); ?>
                </td>

                <td class="hp-configurations-table-item__more-cell">
                    <?php if (count((array) $allowedElements->get('more'))) : ?>
                        <button type="button" class="uk-icon uk-button uk-button-small uk-preserve-width" uk-icon="icon:more; ratio: 1.25"></button>
                        <div class="uk-dropdown tm-background-gray-15 uk-padding-small" uk-dropdown="mode: click; offset: 5; pos: bottom-right">
                            <ul class="uk-nav uk-dropdown-nav tm-dropdown-nav-iconnav">
                                <?php
                                /** @var ElementConfiguratorActions $moreElement */
                                foreach ((array) $allowedElements->get('more') as $moreElement) :
                                    $moreElement->setConfig(['configuration' => $item]);
                                    ?>
                                    <li>
                                        <?= $moreElement->renderActionButton() ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
    <?php if (isset($pagination)) : ?>
        <tfoot>
            <tr>
                <td colspan="6">
                    <?= $pagination->getListFooter() ?>
                </td>
            </tr>
        </tfoot>
    <?php endif; ?>
</table>
