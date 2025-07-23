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
 * @author      Artem Vyshnevskiy
 *
 * @info        Module assets load in system event. See administrator\components\com_hyperpc\framework\Event\SystemEventHandler.php
 *              Method _onSiteAssets()
 */

use HYPERPC\App;
use HYPERPC\Money\Type\Money;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die('Restricted access');

$hp = App::getInstance();
$price = $hp['helper']['money']->get();

$formModuleId = $params->get('form_module');

$introText = trim($params->get('intro_text', ''));
$disclaimer = trim($params->get('disclaimer_text', ''));
$formText = trim($params->get('form_text', ''));

$partsLimitToFilter = 12;
?>

<?php if (!empty($introText)) : ?>
    <div class="uk-margin">
        <?= $introText ?>
    </div>
<?php endif; ?>

<div class="jsTradeinCalculator hp-tradein-calculator">
    <ul class="uk-accordion hp-tradein-calculator-accordion" uk-accordion="multiple: true">
        <?php foreach ($params->get('groups', []) as $groupKey => $group) :
            $needFilter = count((array) $group->parts) > $partsLimitToFilter;
            ?>
            <li class="jsTradeinGroup hp-tradein-calculator-accordion__item uk-open" hidden data-key="<?= $groupKey ?>">
                <a class="uk-accordion-title" href="#" data-group-name="<?= $group->group_name ?>">
                    <?= $group->group_name ?>
                </a>
                <div class="uk-accordion-content">
                    <?php if ($needFilter) : ?>
                        <div class="jsTradeinCalculatorFilter uk-margin">
                            <div class="uk-inline">
                                <a class="jsTradeinCalculatorFilterClear uk-form-icon uk-form-icon-flip"
                                uk-icon="icon: close"
                                title="<?= Text::_('MOD_HP_TRADEIN_CALCULATOR_CLEAR_FILTER') ?>"></a>
                                <input type="text" class="uk-input uk-form-large uk-width-xlarge"
                                    placeholder="<?= Text::_('MOD_HP_TRADEIN_CALCULATOR_FIND') ?>" />
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="uk-grid uk-child-width-1-4@m uk-child-width-1-2@s" uk-margin>
                        <?php foreach ($group->parts as $partKey => $part) : ?>
                            <div class="jsTradeinPart" data-price="<?= $part->part_price ?>"<?= $needFilter ? ' data-name="' . strtolower($part->part_name) . '"' : '' ?>>
                                <label class="uk-flex">
                                    <span class="uk-flex-none"><input class="uk-radio uk-margin-small-right" type="radio" name="<?= $groupKey ?>" value="<?= $part->part_name ?>"></span>
                                    <span><?= $part->part_name ?></span>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if ($needFilter) : ?>
                        <div class="jsTradeinCalculatorFilterFooter uk-text-muted tm-text-italic uk-text-center uk-margin" hidden>
                            <?= Text::_('MOD_HP_TRADEIN_CALCULATOR_NO_MATCHES_FOUND') ?>
                            <a class="jsTradeinCalculatorFilterClear uk-link-muted">
                                <u>
                                    <?= Text::_('MOD_HP_TRADEIN_CALCULATOR_CLEAR_FILTER') ?>
                                </u>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>

    <button class="jsTradeinCalculate uk-button uk-button-primary" type="button" hidden>
        <?= Text::_('MOD_HP_TRADEIN_CALCULATOR_BUTTON_CALCULATE') ?>
    </button>

    <div class="jsTradeinOffer" hidden>
        <hr class="uk-margin-medium">
        <div class="uk-grid uk-grid-divider" uk-grid>
            <div class="uk-width-1-3@m">
                <div class="hp-tradein-calculator__offer">
                    <div class="uk-card uk-card-small uk-card-default uk-card-body uk-box-shadow-small">
                        <div class="uk-h3">
                            <?= Text::_('MOD_HP_TRADEIN_CALCULATOR_EVALUATION_RESULT') ?>
                        </div>

                        <div class="uk-text-large uk-text-emphasis">~
                            <span class="jsTradeinOfferPrice">
                                <?= $price->html() ?>
                            </span>
                        </div>
                    </div>
                    <?php if (!empty($disclaimer)) : ?>
                        <div class="uk-margin-top uk-text-small uk-text-muted tm-text-italic">
                            <?= $disclaimer ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="uk-width-2-3@m">
                <?php if (!empty($formText)) : ?>
                    <div class="uk-margin">
                        <?= $formText ?>
                    </div>
                <?php endif; ?>
                <?= $hp['helper']['module']->renderById($formModuleId) ?>
            </div>
        </div>
    </div>
</div>
