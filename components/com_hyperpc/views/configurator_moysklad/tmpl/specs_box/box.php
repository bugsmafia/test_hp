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

use JBZoo\Utils\Str;
use HYPERPC\Money\Type\Money;
use Joomla\CMS\Language\Text;
use HYPERPC\Helper\RenderHelper;
use \HYPERPC\Helper\ConfiguratorHelper;
use HYPERPC\Joomla\Model\Entity\Interfaces\PartMarker;
use HYPERPC\Joomla\Model\Entity\Interfaces\ProductMarker;
use HYPERPC\Joomla\Model\Entity\Interfaces\CategoryMarker;

/**
 * @var         RenderHelper    $this
 * @var         PartMarker      $part
 * @var         array           $parts
 * @var         CategoryMarker  $group
 * @var         array           $groups
 * @var         ProductMarker   $product
 * @var         array           $groupList
 * @var         boolean         $saveEnabled
 * @var         array           $productParts
 * @var         Money           $productPrice
 * @var         integer         $configurationId
 * @var         boolean         $hasUnavailableParts
 */

$isMobile = $this->hyper['detect']->isMobile();

/** @var ConfiguratorHelper $configuratorHelper */
$configuratorHelper = $this->hyper['helper']['configurator'];

$imageMaxWidth = 305;
$imageMaxHeight = 171;

$imageGroupIds = $configuratorHelper->getImageGroupIds($product, $groupList, $productParts);
$imageSrc = $product->getConfigurationImagePath($imageMaxWidth, $imageMaxHeight);
?>
<?php if ($isMobile) : ?>
    <div class="uk-width-1-3@m uk-width-1-4@l uk-width-1-5@xl uk-flex-last">

        <div id="full-specs" class="uk-modal-container uk-modal" uk-modal>
            <div class="uk-modal-dialog">
                <button class="uk-modal-close-default uk-close-large" type="button" uk-close></button>
                <div class="uk-modal-body" uk-overflow-auto>
                    <?php if (!empty($imageGroupIds)) : ?>
                        <div class="jsBoxCaseImg uk-text-center uk-margin" data-group='<?= json_encode($imageGroupIds) ?>'>
                            <img src="<?= $imageSrc ?>" alt="">
                        </div>
                    <?php endif; ?>
                    <div class="jsFullSpecs uk-margin-auto uk-container-small"></div>
                </div>
            </div>
        </div>

        <div class="jsConfigBox uk-visible@m">
            <div class="uk-card uk-card-small uk-card-default uk-position-z-index uk-background-default hp-config-box">
                <div class="uk-card-header">
                    <div class="uk-card-title hp-configurator-title">
                        <?= Text::_('COM_HYPERPC_CONFIGURATION') ?>
                    </div>
                </div>
                <div class="hp-configurator-box-wrapper uk-card-body uk-text-small">
                    <?php
                    echo $this->hyper['helper']['render']->render('product/configurator/specification', [
                        'parts'     => $parts,
                        'groups'    => $groups,
                        'product'   => $product,
                        'groupList' => $groupList
                    ]);
                    ?>
                </div>
            </div>
        </div>
    </div>
<?php else : ?>
    <div class="hp-configurator-right-column uk-width-auto@m uk-flex-last">
        <div class="jsConfigBox">
            <div class="hp-configurator-right-box uk-padding-small">
                <h1 class="uk-h4 uk-text-center uk-text-normal uk-margin-remove-top">
                    <?= Text::_('COM_HYPERPC_CONFIGURATOR') ?><br>
                    <?= $product->name ?>
                </h1>
                <?php if (!empty($imageGroupIds)) : ?>
                    <div class="jsBoxCaseImg uk-text-center uk-margin" data-group='<?= json_encode($imageGroupIds) ?>'>
                        <img src="<?= $imageSrc ?>" alt="" style="max-height: <?= $imageMaxHeight ?>px">
                    </div>
                <?php endif; ?>
                <div class="uk-margin-small-bottom">
                    <div class="uk-text-center uk-margin-small-bottom">
                        <?= $this->hyper['helper']['render']->render('common/price/item-price', [
                            'price'      => $productPrice,
                            'entity'     => $product,
                            'htmlPrices' => true
                        ]); ?>
                    </div>

                    <?= $product->getRender()->cartBtnForConfigurator(); ?>
                </div>

                <div class="uk-flex hp-configurator-right-column__buttons uk-margin">

                    <button type="button" class="uk-button uk-button-secondary uk-button-small uk-width-1-1 uk-border-rounded jsSaveConfig<?= $saveEnabled ? '' : ' uk-disabled' ?>"
                        title="<?= Text::_('COM_HYPERPC_CONFIG_SAVE_BUTTON_HINT') ?>">
                        <span class="uk-icon" uk-icon="hp-diskette"></span><br>
                        <?= Text::_('JSAVE') ?>
                    </button>

                    <button type="button" class="uk-button uk-button-secondary uk-button-small uk-width-1-1 uk-border-rounded jsConfigReset uk-disabled" title="<?= Text::_('COM_HYPERPC_CONFIGURATOR_RESET_TITLE') ?>">
                        <span class="uk-icon" uk-icon="refresh"></span><br>
                        <?= Text::_('COM_HYPERPC_CONFIGURATOR_RESET') ?>
                    </button>

                    <button type="button" uk-toggle="target: #load-configuration-modal" class="uk-button uk-button-secondary uk-button-small uk-width-1-1 uk-border-rounded" title="<?= Text::_('COM_HYPERPC_CONFIG_LOAD_BUTTON_HINT') ?>">
                        <span class="uk-icon" uk-icon="upload"></span><br>
                        <?= Text::_('COM_HYPERPC_LOAD') ?>
                    </button>

                    <?php if ($this->hyper['params']->get('conf_save_email', 1)) : ?>
                        <button type="button" class="uk-button uk-button-secondary uk-button-small uk-width-1-1 uk-border-rounded" title="<?= Text::_('COM_HYPERPC_CONFIG_EMAIL_SEND_HINT') ?>" uk-toggle="target: #save_email">
                            <span class="uk-icon" uk-icon="mail"></span><br>
                            <?= Text::_('COM_HYPERPC_CONFIG_EMAIL_SEND') ?>
                        </button>
                    <?php endif; ?>

                </div>

                <hr class="uk-margin-small">

                <div class="uk-h4 uk-text-center uk-margin-remove">
                    <?= Text::_('COM_HYPERPC_CONFIGURATION') ?>
                    <?php if ($hasUnavailableParts) : ?>
                        <a href="#hp-warning-modal" class="uk-icon uk-text-warning" uk-icon="warning" uk-toggle></a>
                    <?php endif; ?>
                </div>
                <div class="hp-configurator-box-wrapper hp-configurator-box-short uk-text-small">
                    <?php
                        echo $this->hyper['helper']['render']->render('product/configurator/specification', [
                            'parts'     => $parts,
                            'groups'    => $groups,
                            'product'   => $product,
                            'groupList' => $groupList
                        ]);
                    ?>
                </div>
                <button class="jsShowFullSpecs uk-button uk-button-link uk-text-small uk-text-bold"><?= Text::_('COM_HYPERPC_CONFIGURATOR_FULL_SPECIFICATION') ?></button>

                <div id="full-specs" class="uk-modal-container uk-modal" uk-modal>
                    <div class="uk-modal-dialog uk-modal-body">
                        <button class="uk-modal-close-default" type="button" uk-close=""></button>
                        <div class="jsFullSpecs uk-margin-auto uk-container-small"></div>
                    </div>
                </div>

                <hr>

                <div class="jsConfigNumberWrapper uk-margin-top"<?= !$configurationId ? ' hidden' : '' ?>>
                    <div class="uk-text-emphasis uk-text-center uk-margin-small">
                        <?= Text::_('COM_HYPERPC_CONFIG_SAVE_NUMBER') ?>
                    </div>
                    <div class="uk-text-center">
                        <div class="hp-config-number-wrapper uk-padding-small">
                            <div class="jsConfigNumber hp-config-number uk-h1 uk-text-normal uk-margin-remove"><?= $configurationId ? Str::zeroPad($configurationId, 7) : '_______' ?></div>
                        </div>
                    </div>
                </div>

                <?php if (false) : ?>
                    <div class="uk-text-emphasis uk-text-center uk-margin-top">
                        <?= Text::_('COM_HYPERPC_CONFIG_SAVE_SHARE_WITH_FRIENDS') ?>
                    </div>
                    <div class="uk-text-center uk-margin-small">
                        <?= $this->hyper['helper']['render']->render('product/configurator/share_buttons', []) ?>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
<?php endif;
