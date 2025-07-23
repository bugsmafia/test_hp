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
use Joomla\CMS\HTML\HTMLHelper;
use HYPERPC\Object\SavedConfiguration\CheckData;
use HYPERPC\Joomla\Model\Entity\SaveConfiguration;

/**
 * @var     CheckData           $configurationCheckData
 * @var     SaveConfiguration   $configuration
 */

$unavailableParts = $configurationCheckData->unavalableParts;
$priceDifference = $configurationCheckData->priceDifference;
?>

<?php if ($configurationCheckData->hasWarnings) : ?>
    <div id="hp-warning-modal" class="uk-flex-top" uk-modal="bg-close: false">
        <div class="uk-modal-dialog uk-margin-auto-vertical tm-card-bordered">
            <div class="uk-modal-body uk-flex uk-flex-top">
                <button class="uk-modal-close-default" type="button" uk-close></button>
                <div class="uk-margin-auto-vertical">
                    <div class="tm-text-medium uk-margin-small-bottom">
                        <?= Text::sprintf('COM_HYPERPC_YOUR_CONFIGURATION_HAS_BEEN_SAVED_ON', HTMLHelper::date($configurationCheckData->lastModifiedDate, Text::_('DATE_FORMAT_LC5'))) ?>
                    </div>
                    <?php if (!empty($unavailableParts)) : ?>
                        <div class="uk-text-muted uk-margin-top">
                            <?= Text::_('COM_HYPERPC_THE_FOLLOWING_PARTS_ARE_CURRENTLY_UNAVAILABLE') ?>:
                        </div>
                        <ul class="uk-list tm-list-small uk-list-bullet uk-text-muted uk-margin-small">
                            <?php foreach ($unavailableParts as $partName) : ?>
                                <li>
                                    <?= $partName ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <div>
                            <?= Text::_('COM_HYPERPC_DO_NOT_FORGET_TO_PICK_A_REPLACEMENT') ?>!
                        </div>
                    <?php endif; ?>

                    <?php if (empty($configuration->order_id) && (int) $priceDifference->val() !== 0) : ?>
                        <?php if (!empty($unavailableParts)) : ?>
                            <div class="uk-text-muted uk-margin-top jsPriceDifferenceWarning">
                                <?= Text::sprintf(
                                    'COM_HYPERPC_THE_TOTAL_PRICE_W_OUT_UNAVAILABLE_PARTS_' . ($priceDifference->val() > 0 ? 'INCREASE' : 'DECREASE'),
                                    $priceDifference->abs()
                                );
                                ?>
                            </div>
                        <?php else : ?>
                            <div class="uk-text-muted">
                                <?= Text::sprintf(
                                    'COM_HYPERPC_AT_THE_MOMENT_THE_TOTAL_PRICE_' . ($priceDifference->val() > 0 ? 'INCREASE' : 'DECREASE'),
                                    $priceDifference->abs()
                                ); ?>
                            </div>
                        <?php endif; ?>

                    <?php endif; ?>
                </div>
            </div>
            <div class="uk-modal-footer uk-text-center">
                <button class="uk-button uk-button-primary uk-modal-close" type="button">
                    <?= Text::_('COM_HYPERPC_GOT_IT') ?>
                </button>
            </div>
        </div>
    </div>
<?php endif;
