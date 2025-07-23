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

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use HYPERPC\Joomla\Model\Entity\MoyskladPart;
use HYPERPC\Joomla\Model\Entity\MoyskladService;

/**
 * @var HyperPcViewProduct_Folder $this
 * @var MoyskladPart              $part
 * @var MoyskladService           $service
 */

$isArchiveView = $this->hyper['input']->get('view') === 'group_archive';

$compareItems = [];
if (count($this->parts) || count($this->services)) {
    $compareItems = $this->hyper['helper']['compare']->getItems('position');
}
?>

<?php if ($this->productFolder->getParams()->get('show_title', true, 'bool')) :
    $pageHeading = $this->productFolder->title;
    if (!empty(trim($this->productFolder->getParams()->get('title')))) {
        $pageHeading = $this->productFolder->getParams()->get('title');
    }
    ?>
    <div class="uk-container uk-container-large">
        <h1 class="uk-margin-bottom"><?= $pageHeading ?></h1>
    </div>
<?php endif; ?>

<?php if ($this->productFolder->description != '' && !$this->isArchive) : ?>
    <div class="uk-margin-bottom">
        <?= HTMLHelper::_('content.prepare', $this->productFolder->description); ?>
    </div>
<?php endif; ?>

<?php if ($this->showSubGroups) :
    $children = $this->productFolder->getSubfolders();
    ?>
    <?php if (count($children) > 0) : ?>
        <div class="hp-group-parents uk-margin-large-bottom">
            <div class="uk-container uk-container-large">
                <?= $this->renderLayout('default_subcategories', [
                    'productFolders' => $children,
                    'productFolder'  => $this->productFolder
                ], false) ?>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php if ($this->showPositions) : ?>
    <?php if (count($this->products)) : // Products
        $teasersType = $this->productFolder->getParams()->get('teasers_type', 'default');
        ?>
        <div id="buy" class="uk-section">
            <?php if ($teasersType === 'table') :
                /** @todo display table */
                ?>
            <?php else :
                $cols = $this->productFolder->getParams()->get('products_cols', HP_DEFAULT_ROW_COLS);
                $gridClass = $this->hyper['helper']['uikit']->getProductsResponsiveClassByCols($cols);
                $containerClass = $this->hyper['helper']['uikit']->getProductsContainerClassByCols($cols);
                ?>
                <div class="<?= $containerClass ?>">
                    <div
                        class="<?= $gridClass ?> uk-grid-small uk-grid-match<?= count($this->products) < $cols ? ' uk-flex-center' : '' ?>"
                        uk-margin="margin: uk-margin-large-top"
                        uk-height-match="target: .hp-product-teaser__header"
                    >
                        <?= $this->hyper['helper']['render']->render('product/teaser/default', [
                            'products'   => $this->products,
                            'showFps'    => $this->showFps,
                            'options'    => $this->options,
                            'groups'     => $this->productFolders,
                            'teaserType' => $teasersType,
                        ], 'renderer', false); ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php
    $hasPartsFilter = $this->partsFilter->hasFilters();
    if (count($this->parts) || $hasPartsFilter) : // Parts
        $hasPartsFilter = $this->partsFilter->hasFilters();
        $hasActivePartsFilter = !empty($this->partsFilter->getState()['current']);

        $groupAttr = [
            'class' => 'uk-margin-large-bottom' . ($hasPartsFilter ? ' jsGroupFilter' : '')
        ];

        if ($hasPartsFilter && $hasActivePartsFilter) {
            $groupAttr['data-filters'] = json_encode($this->partsFilter->getState()['current']);
        }

        $cols = $this->hyper['params']->get('parts_cols', HP_DEFAULT_ROW_COLS);
        if ($hasPartsFilter) {
            $cols--;
        }
        $responsiveClass = $this->hyper['helper']['uikit']->getResponsiveClassByCols($cols);
        ?>
        <div <?= $this->hyper['helper']['html']->buildAttrs($groupAttr) ?>>
            <?php if ($hasPartsFilter) : ?>
                <?= $this->hyper['helper']['render']->render('filter/2024/nav', [
                    'filter' => $this->partsFilter
                ], 'renderer', false); ?>
            <?php endif; ?>
            <div class="uk-container uk-container-large">
                <div class="uk-grid uk-grid-small">
                    <?php if ($hasPartsFilter) :
                        $isMobile = $this->hyper['detect']->isMobile();
                        ?>
                        <?php if ($isMobile) : ?>
                            <div class="hp-group__filters uk-modal uk-modal-full" data-uk-modal="bg-close: false">
                                <div class="uk-modal-dialog uk-modal-body tm-background-gray-5" data-uk-height-viewport>
                                    <button class="uk-modal-close-full uk-close-large" type="button" data-uk-close></button>
                                    <a href="#" class="jsClearAllFilters"<?= !$hasActivePartsFilter ? ' hidden' : '' ?>>
                                        <?= Text::_('COM_HYPERPC_CLEAR_ALL') ?>
                                    </a>
                                    <div class="uk-margin-top uk-margin-xlarge-bottom">
                                        <?= $this->hyper['helper']['render']->render('filter/2024/list', [
                                            'filter' => $this->partsFilter
                                        ], 'renderer', false); ?>
                                    </div>
                                </div>
                                <div class="uk-position-fixed uk-position-bottom uk-position-small uk-background-default">
                                    <button class="jsCloseFiltersModal uk-button uk-button-primary uk-width-1-1" type="button">
                                        <?= Text::_('COM_HYPERPC_SHOW') ?>
                                        <span class="jsFiltersResultCount">
                                            (<?= count($this->parts) ?>)
                                        </span>
                                    </button>
                                </div>
                            </div>
                        <?php else : ?>
                            <div class="hp-group__filters uk-margin-medium uk-width-1-6@xl uk-width-1-5@l uk-width-1-4@m">
                                <div class="jsGroupFiltersSticky">
                                    <?= $this->hyper['helper']['render']->render('filter/2024/list', [
                                        'filter' => $this->partsFilter
                                    ], 'renderer', false); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                    <div class="uk-width-expand<?= $hasPartsFilter ? ' jsGroupItemsWrapper' : '' ?>">
                        <div class="hp-group-items uk-grid uk-grid-small uk-grid-match <?= $responsiveClass ?><?= $hasPartsFilter ? ' jsGroupItems' : '' ?>">
                            <?php
                            $html = [];
                            foreach ($this->parts as $part) {
                                $html[] = $this->hyper['helper']['render']->render('part/teaser/part', [
                                    'part'         => $part,
                                    'compareItems' => $compareItems,
                                    'group'        => $this->productFolder
                                ]);
                            }

                            if (!empty($html)) {
                                echo implode(PHP_EOL, $html);
                            } else {
                                echo $this->hyper['helper']['render']->render('filter/common/no_found');
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (count($this->services)) : // Services
        $cols = $this->hyper['params']->get('parts_cols', HP_DEFAULT_ROW_COLS);
        $responsiveClass = $this->hyper['helper']['uikit']->getResponsiveClassByCols($cols);

        $html = [];

        foreach ($this->services as $service) {
            $html[] = $this->hyper['helper']['render']->render('part/teaser/service', [
                'service'       => $service,
                'compareItems'  => $compareItems,
                'group'         => $this->productFolder
            ]);
        }
        ?>
        <?php if (!empty($html)) : ?>
            <div class="uk-margin-medium-top uk-margin-large-bottom">
                <div class="uk-container uk-container-large">
                    <div class="hp-group-items uk-grid uk-grid-small uk-grid-match <?= $responsiveClass ?>">
                        <?= implode(PHP_EOL, $html) ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
<?php endif; ?>

<?php
echo HTMLHelper::_('content.prepare', $this->productFolder->getParams()->get('content_before_promo', ''));
/** @todo display promo module */
echo HTMLHelper::_('content.prepare', $this->productFolder->getParams()->get('content_after_items', ''));
