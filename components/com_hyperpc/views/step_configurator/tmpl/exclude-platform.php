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
 *
 * @todo        move common code into their own templates
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use HYPERPC\Helper\ImageHelper;
use HYPERPC\Helper\RenderHelper;

/**
 * @var HyperPcViewStep_Configurator $this
 */

/** @var RenderHelper */
$renderHelper = $this->hyper['helper']['render'];
?>
<?php if ($this->params->get('show_page_heading')) :
    $pageHeading = $this->menuItem->title;
    if (!empty(trim($this->params->get('page_heading', '')))) {
        $pageHeading = $this->params->get('page_heading');
    }
    ?>
    <div class="uk-container uk-container-large">
        <h1 class="uk-h2"><?= $pageHeading ?></h1>
    </div>
<?php endif; ?>
<div class="hp-step-configurator">

    <?= $renderHelper->render('configurator/steps_progress', [
        'currentStep'     => $this->currentStep,
        'prevStep'        => $this->prevStep,
        'excludePlatform' => true
    ]) ?>

    <div class="uk-overflow-hidden">
        <div class="uk-container uk-container-large">
            <div class="uk-grid" uk-height-viewport="expand: true">
                <div class="hp-step-configurator__aside">
                    <div class="hp-step-configurator__aside-header">
                        <div class="hp-step-configurator__price uk-grid uk-flex-between uk-flex-middle">
                            <div>
                                <?= $this->category->title ?>
                            </div>
                            <div>
                                <?php echo
                                    Text::sprintf(
                                        'COM_HYPERPC_STARTS_FROM',
                                        '<span class="jsStartPrice">' . $this->minPrice->noStyle() . '</span> ' .
                                        $this->hyper['helper']['money']->getCurrencySymbol($this->minPrice)
                                    );
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="hp-step-configurator__aside-content">
                        <div class="uk-text-center uk-margin">
                            <div class="uk-h3 uk-text-normal"><?= Text::_('COM_HYPERPC_CONFIGURATOR') . ' ' . $this->category->title ?></div>
                            <?php
                                $imgSrc = $this->category->params->get('image', '', 'hpimagepath') ?: ImageHelper::TRANSPARENT_PIXEL;
                            ?>
                            <img src="<?= $imgSrc ?>" alt="<?= $this->category->title ?>" width="250"/>
                        </div>
                        <hr />
                        <ul class="uk-list uk-list-bullet">
                            <li>
                                <div class="tm-text-medium uk-text-emphasis">
                                    <?= Text::_('COM_HYPERPC_STEP_CONFIGURATOR_STEP_PLATFORM') ?>
                                </div>
                                <ul class="jsPlatformParamsSummary uk-list uk-list-divider tm-list-small">
                                    <?php foreach ($this->platform as $key => $data) : ?>
                                        <li data-prop="<?= $key ?>">
                                            <?= $data['title'] ?>
                                            <span class="jsPlatformParamsSummaryValue">
                                                <?= $data['value'] ?>
                                            </span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </li>
                            <li class="jsComplectationSummary">
                                <div class="tm-text-medium uk-text-emphasis">
                                    <?= Text::_('COM_HYPERPC_STEP_CONFIGURATOR_STEP_COMPLECTATION') ?>
                                </div>
                                <div class="jsComplectationSummaryValue uk-margin-small">
                                    <?= $this->_getActiveComplectationName() ?>
                                </div>
                            </li>
                        </ul>
                        <hr class="uk-margin-small-bottom" />
                        <div class="hp-step-configurator__price uk-grid uk-flex-between">
                            <div><?= Text::_('COM_HYPERPC_COST') ?></div>
                            <div>
                                <?php echo
                                    Text::sprintf(
                                        'COM_HYPERPC_STARTS_FROM',
                                        '<span class="jsStartPrice">' . $this->minPrice->noStyle() . '</span> ' .
                                        $this->hyper['helper']['money']->getCurrencySymbol($this->minPrice)
                                    );
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="hp-step-configurator__aside-toggler">
                        <button class="uk-icon" uk-toggle="target: .hp-step-configurator__aside; cls: hp-step-configurator__aside--expanded">
                            <svg width="60" height="9" viewBox="0 0 20 3" xmlns="http://www.w3.org/2000/svg"><rect height="1" width="18" y="1" x="1"></rect></svg>
                        </button>
                    </div>
                </div>
                <div class="hp-step-configurator__main uk-width-expand">
                    <ul class="jsStepConfiguratorSwitcher uk-switcher">
                        <li></li>
                        <li></li>
                        <li></li>
                        <li class="uk-active">
                            <ul class="uk-grid uk-grid-small uk-child-width-1-2@s uk-child-width-1-3@xl uk-grid-match" uk-grid uk-height-match=".uk-card-body">
                                <?php foreach ($this->complectations as $id => $complectationData) : ?>
                                    <li class="hp-configurator-complectation" data-complectation="<?= $id ?>"<?= !in_array($id, $this->availableComplectations) ? ' hidden' : '' ?>>
                                        <div class="uk-card uk-card-small uk-card-default tm-card-bordered">
                                            <div class="hp-configurator-complectation__image uk-card-media-top uk-background-default uk-text-center">
                                                <div class="uk-display-inline-block uk-background-cover" style="background-image: url('<?= $complectationData['image'] ?>');">
                                                    <canvas width="250" height="250"></canvas>
                                                </div>
                                                <input id="complectation-<?= $id ?>"
                                                       name="complectation"
                                                       type="radio"
                                                       value="<?= $complectationData['href'] ?>"
                                                       class="hp-configurator-complectation__checkbox uk-checkbox uk-position-top-left uk-position-small"
                                                        <?= $id === $this->activeComplectation ? 'checked' : '' ?>
                                                />
                                                <label class="uk-position-cover" for="complectation-<?= $id ?>"></label>
                                            </div>
                                            <div class="uk-card-body">
                                                <div class="hp-configurator-complectation__name uk-h5 uk-margin-remove-bottom">
                                                    <label for="complectation-<?= $id ?>">
                                                        <?= $complectationData['name'] ?>
                                                    </label>
                                                </div>
                                                <div>
                                                    <?php echo
                                                        Text::_('COM_HYPERPC_PRICE') . ' ' .
                                                        Text::sprintf(
                                                            'COM_HYPERPC_STARTS_FROM',
                                                            $this->_getComplectationPrice($id)->text()
                                                        );
                                                    ?>
                                                </div>
                                                <hr class="uk-margin-small uk-margin-remove-bottom" />
                                                <div class="uk-flex uk-flex-column uk-flex-between">
                                                    <div>
                                                        <div class="uk-grid uk-grid-small uk-child-width-1-2">
                                                            <?php foreach ($complectationData['parts'] as $groupName => $parts) : ?>
                                                                <div class="uk-margin-small-top uk-text-small">
                                                                    <div class="uk-text-muted"><?= $groupName ?></div>
                                                                    <div>
                                                                        <?php foreach ($parts as $part) : ?>
                                                                            <div><?= $part ?></div>
                                                                        <?php endforeach; ?>
                                                                    </div>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="uk-card-footer">
                                                <button class="jsSpecificationButton uk-button uk-button-link uk-text-small uk-link-muted tm-link-dashed"
                                                        type="button"
                                                        data-itemkey="<?= $complectationData['itemKey'] ?>"
                                                        data-title="<?= $complectationData['name'] ?>">
                                                    <?= Text::_('COM_HYPERPC_CONFIGURATOR_FULL_SPECIFICATION') ?>
                                                </button>
                                            </div>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </li>
                        <li></li>
                        <li></li>
                    </ul>
                </div>
            </div>
        </div>

        <?= $renderHelper->render('configurator/steps_nav') ?>
    </div>
</div>
