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

use JBZoo\Image\Image;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use HYPERPC\Helper\ImageHelper;
use HYPERPC\Helper\MoneyHelper;
use HYPERPC\Helper\CreditHelper;
use HYPERPC\Helper\ModuleHelper;
use HYPERPC\Helper\RenderHelper;
use HYPERPC\Helper\ProductFolderHelper;

/**
 * @var HyperPcViewConfigurator_Step_Model $this
 */

/** @var ProductFolderHelper */
$categoryHelper = $this->categoryHelper;
/** @var MoneyHelper */
$moneyHelper = $this->hyper['helper']['money'];
/** @var CreditHelper */
$creditHelper = $this->hyper['helper']['credit'];
/** @var ModuleHelper */
$moduleHelper = $this->hyper['helper']['module'];
/** @var RenderHelper */
$renderHelper = $this->hyper['helper']['render'];
/** @var ImageHelper */
$imageHelper = $this->hyper['helper']['image'];

$creditMaxPrice = $creditHelper->getMaxPrice();
$creditEnabled = $this->hyper['params']->get('credit_enable', false, 'bool');

$initialCategory = $this->categories[$this->activeCategoryId];
$initialCategoryImg = ImageHelper::TRANSPARENT_PIXEL;

$activeIsFirst = false;
$activeIsLast = false;
$activeIndex = 0;
?>

<?php /** @todo move to file */ ?>
<style type="text/css">
    [data-series] {
        padding-left: 25px;
        padding-top: 40px;
    }

    [data-series]::before {
        content: '';
        position: absolute;
        top: 0;
        bottom: 0;
        left: 15px;
        width: 1px;
        background: #555;
    }

    [data-series]::after {
        content: attr(data-series);
        position: absolute;
        top: 0;
        left: 35px;
        white-space: nowrap;
        color: #fff;
    }

    .p-slidenav-previous {
        transform: translateX(-100%);
        border-right: 1px solid #555;
    }

    .p-slidenav-next {
        transform: translateX(100%);
        border-left: 1px solid #555;
    }

    @media (max-width: 649px) {
        .hp-step-configurator__model-slider {
            margin-left: -15px;
            margin-right: -15px;
        }
    }

    .hp-step-configurator__model-slider-items>*>* {
        position: relative;
        padding: 0px 20px 10px;
    }

    .hp-step-configurator__model-slider-items label {
        border: 1px solid transparent;
        cursor: pointer;
        position: absolute;
        margin: 0 10px;
        top: 0;
        bottom: 0;
        left: 0;
        right: 0;
    }

    .hp-step-configurator__model-slider-items input:checked+label {
        border-color: #555;
        cursor: default;
    }
</style>

<?php if (!empty($this->pageContentModuleId)) : ?>
    <?= $moduleHelper->renderById($this->pageContentModuleId) ?>
<?php endif; ?>
<div class="hp-step-configurator">

    <?= $renderHelper->render('configurator/steps_progress', ['currentStep' => $this->currentStep]) ?>

    <div class="uk-overflow-hidden">
        <div class="uk-container uk-container-large">
            <div>
                <select class="jsStepConfiguratorModelSelect uk-select uk-margin-top uk-hidden@s">
                    <?php
                    $i = 0;
                    foreach ($this->categoriesTree as $categoryGroup) : ?>
                        <?php foreach ($categoryGroup->child as $categoryId) :
                            $isActive = $categoryId === $this->activeCategoryId;

                            if ($isActive) {
                                $activeIndex = $i;

                                if ($activeIndex === 0) {
                                    $activeIsFirst = true;
                                }

                                if ($activeIndex + 1 === count($this->categories)) {
                                    $activeIsLast = true;
                                }
                            }

                            $i++;
                            ?>
                            <option <?= $isActive ? 'selected' : '' ?>>
                                <?= $this->categories[$categoryId]->title ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </select>

                <ul class="jsStepConfiguratorModelSliderSeriesNav hp-step-configurator__slider-series-nav uk-subnav uk-subnav-divider uk-visible@s uk-flex-center">
                    <?php
                    $i = 0;
                    foreach ($this->categoriesTree as $categoryGroup) :
                        $sliderItem = $i;
                        $i += count($categoryGroup->child);

                        $isActive = $activeIndex >= $sliderItem && $activeIndex < $i;
                        ?>
                        <li data-slider-item="<?= $sliderItem ?>" class="<?= $isActive ? 'uk-active' : '' ?>"><a href="#"><?= $categoryGroup->rootShort ?></a></li>
                    <?php endforeach; ?>
                </ul>

                <div class="hp-step-configurator__model-slider uk-slider uk-position-relative uk-visible-toggle uk-light" tabindex="-1" uk-slider="finite: true;">
                    <div class="uk-slider-container uk-margin-top">
                        <div class="jsStepConfiguratorModelSliderItems hp-step-configurator__model-slider-items uk-slider-items uk-text-center uk-grid uk-grid-small uk-flex-bottom">
                            <?php foreach ($this->categoriesTree as $categoryGroup) :
                                $i = 0;
                                ?>
                                <?php foreach ($categoryGroup->child as $categoryId) :
                                    $category = $this->categories[$categoryId];

                                    $categoryStartPrice = $categoryHelper->getMinCategoryPrice($categoryId);
                                    $categoryStartCredit = null;
                                    if ($categoryStartPrice && $creditEnabled) {
                                        $categoryStartCredit = $categoryStartPrice->compare($creditMaxPrice, '<=') ? $creditHelper->getMonthlyPayment($categoryStartPrice->val()) : null;
                                    }

                                    $nextStepUrl = Route::_('index.php?option=com_hyperpc&view=step_configurator&category_id=' . $categoryId);
                                    if (strpos($nextStepUrl, 'view=step_configurator') !== false) {
                                        $nextStepUrl = $category->getViewUrl() . '#buy';
                                    }

                                    $categoryImg = ImageHelper::TRANSPARENT_PIXEL;
                                    $categoryImgThumb = ImageHelper::TRANSPARENT_PIXEL;

                                    $categoryImgPath = '/' . ltrim($category->params->get('image', '', 'hpimagepath'), '/');

                                    if ($categoryImgPath !== '/') {
                                        $thumb = $imageHelper->getThumb($categoryImgPath, $this->categoryThumbSize, $this->categoryThumbSize);
                                        if (isset($thumb['thumb']) && $thumb['thumb'] instanceof Image) {
                                            $categoryImgThumb = $thumb['thumb']->getUrl();
                                        }

                                        $img = $imageHelper->getThumb($categoryImgPath, $this->categoryImgSize, $this->categoryImgSize);
                                        if (isset($img['thumb']) && $img['thumb'] instanceof Image) {
                                            $categoryImg = $img['thumb']->getUrl();

                                            if ($categoryId === $this->activeCategoryId) {
                                                $initialCategoryImg = $categoryImg;
                                            }
                                        }
                                    }
                                ?>
                                <div <?= $i++ === 0 && count((array) $this->categoriesTree) > 1 ? 'data-series="' . $categoryGroup->root . '"' : '' ?>>
                                    <div>
                                        <input
                                                id="<?= $category->alias ?>"
                                                hidden <?= $categoryId === $this->activeCategoryId ? 'checked' : '' ?>
                                                name="step-configurator-model"
                                                type="radio"
                                                value="<?= $nextStepUrl ?>"
                                                data-title="<?= $category->title ?>" />
                                        <label class="uk-position-cover" for="<?= $category->alias ?>"></label>
                                        <div>
                                            <img src="<?= $categoryImgThumb ?>" width="<?= $this->categoryThumbSize ?>" height="<?= $this->categoryThumbSize ?>" alt="<?= $category->title ?>"
                                                 data-fullsize="<?= $categoryImg ?>" loading="lazy" />
                                        </div>
                                        <div class="uk-margin-small-top">
                                            <h3 class="uk-h5 uk-margin-remove"><?= $category->title ?></h3>
                                            <?php if (!empty($categoryStartPrice)) : ?>
                                                <div class="uk-text-small">
                                                    <?= Text::sprintf('COM_HYPERPC_STARTS_FROM', $categoryStartPrice->text()) ?>
                                                    <?php if (!empty($categoryStartCredit)) : ?>
                                                        <span class="uk-text-muted">| <?= $categoryStartCredit->text() ?>/<?= Text::_('COM_HYPERPC_MONTH_SHORT') ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <a class="uk-position-left uk-flex uk-flex-middle p-slidenav-previous uk-visible@s" href="#" uk-slidenav-previous uk-slider-item="previous"></a> <a class="uk-position-right uk-flex uk-flex-middle p-slidenav-next uk-visible@s" href="#" uk-slidenav-next uk-slider-item="next"></a>
                </div>

                <div class="uk-container-small uk-margin-auto uk-margin uk-text-center uk-position-relative">
                    <img class="jsStepConfiguratorModelImg" src="<?= $initialCategoryImg ?>" alt="<?= $initialCategory->title ?>" width="<?= $this->categoryImgSize ?>" height="<?= $this->categoryImgSize ?>" />
                    <div class="jsStepConfiguratorModelName uk-text-large uk-text-emphasis">
                        <?= $initialCategory->title ?>
                    </div>
                    <a class="jsStepConfiguratorPrevModel uk-slidenav-large uk-position-center-left uk-icon uk-slidenav-previous uk-slidenav"<?= $activeIsFirst ? ' hidden' : '' ?> href="#" uk-slidenav-previous></a>
                    <a class="jsStepConfiguratorNextModel uk-slidenav-large uk-position-center-right uk-icon uk-slidenav-previous uk-slidenav"<?= $activeIsLast ? ' hidden' : '' ?> href="#" uk-slidenav-next></a>
                </div>
            </div>
        </div>
    </div>

    <?= $renderHelper->render('configurator/steps_nav') ?>
</div>
