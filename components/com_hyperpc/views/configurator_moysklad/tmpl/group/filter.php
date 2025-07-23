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
 * @author      Sergey Kalistratov <kalistratov.s.m@gmail.com>
 *
 * @var         RenderHelper    $this
 * @var         ProductFolder   $group
 * @var         Field           $field
 * @var         Data            $option
 * @var         ProductMarker   $product
 * @var         bool            $divideByAvailability
 */

use JBZoo\Data\Data;
use Joomla\CMS\Language\Text;
use HYPERPC\Helper\RenderHelper;
use HYPERPC\Joomla\Model\Entity\Field;
use HYPERPC\Joomla\Model\Entity\ProductFolder;
use HYPERPC\Joomla\Model\Entity\Interfaces\ProductMarker;

defined('_JEXEC') or die('Restricted access');

$groupPreorder = $group->params->get('preorder');
?>

<?php if (!$groupPreorder || !empty($group->field) || $divideByAvailability) : ?>
    <div class="hp-group-filters">

        <?php if (!empty($group->field)) :
            $actualFilters  = array_count_values((array) $group->field->get('actualFilters'));
            ?>
            <?php if (count($actualFilters) > 1) :
                $checkedFilters = array_count_values((array) $group->field->get('checkedFilters'));
                ?>
                <div class="hp-group-filter" data-filter="<?= $group->field->name ?>">
                    <div class="hp-group-filter-inner jsScrollableFilter">
                        <?php if (in_array($group->field->type, ['list'])) :
                            $options = $group->field->getFieldOption(false); ?>
                            <?php if (count($options)) : ?>
                                <span class="hp-filter-all" uk-filter-control>
                                    <?= Text::_('COM_HYPERPC_FILTERS_ALL') ?>
                                </span>
                                <?php $visibleFilters = array_count_values((array) $group->field->get('visibleFilters')); ?>
                                <?php foreach ($options as $option) :
                                    $value = $option->get('value');
                                    if (!array_key_exists($value, $actualFilters)) {
                                        continue;
                                    }

                                    $activeClass = '';
                                    if ((empty($checkedFilters) || count($checkedFilters) === 2) && $value === 'recommended') {
                                        $activeClass = ' uk-active';
                                    } elseif (count($checkedFilters) === 1 && isset($checkedFilters[$value])) {
                                        $activeClass = ' uk-active';
                                    } elseif (count($checkedFilters) > 2 && isset($checkedFilters['recommended'])) {
                                        if ((count($checkedFilters) - $checkedFilters['recommended']) === 1) {
                                            // all picked parts are recommended
                                            if ($value === 'recommended') {
                                                $activeClass = ' uk-active';
                                            }
                                        }
                                    }

                                    $hiddenAttr = '';
                                    if (!array_key_exists($value, $visibleFilters)) {
                                        $hiddenAttr = ' hidden';
                                    }
                                    ?>
                                    <span class="jsFilterButton<?= $activeClass ?>" uk-filter-control="[data-<?= $group->field->name ?>~='<?= $value ?>']"<?= $hiddenAttr ?>>
                                        <?= $option->get('name') ?>
                                    </span>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        <?php else : ?>
                            <span class="hp-filter-all" uk-filter-control>
                                <?= Text::_('COM_HYPERPC_FILTERS_ALL') ?>
                            </span>
                            <?php foreach ($actualFilters as $option => $count) :
                                $hash = hash('crc32', $option);
                                $activeClass = array_key_exists($option, $checkedFilters) ? ' uk-active' : '';
                                ?>
                                <span class="jsFilterButton<?= $activeClass ?>" uk-filter-control="[data-<?= $group->field->name ?>~='<?= $hash ?>']">
                                    <?= $option ?>
                                </span>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($divideByAvailability && $group->get('hasOutOfStockParts', false)) : ?>
            <?php $initState = $this->hyper['helper']['configurator']->inStockOnlyInitState($product); ?>
            <input type="checkbox" class="jsOnlyInstock" hidden<?= $initState ? ' checked' : ''?>>
            <?php if ($group->get('allPartsOutOfStock', true)) : ?>
                <div class="jsShowAllPreorderedWrapper uk-text-muted uk-text-italic uk-margin-medium-top"<?= $initState ? '' : ' hidden'?>>
                    <?= Text::_('COM_HYPERPC_THERE_ARE_NO_ITEMS_INSTOCK_NOW') ?>.<br class="uk-hidden@s">
                    <a class="jsShowAllPreordered tm-link-underlined">
                        <?= Text::_('COM_HYPERPC_SHOW_ALL') ?>
                    </a>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
<?php endif;
