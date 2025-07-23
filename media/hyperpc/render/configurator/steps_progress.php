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

use Joomla\CMS\Language\Text;

/**
 * @var int     $currentStep
 * @var string  $prevStep
 * @var bool    $excludePlatform
 */

$excludePlatform = $excludePlatform ?? false;

$stepTitles = [
    Text::_('COM_HYPERPC_STEP_CONFIGURATOR_STEP_SERIE'),
    Text::_('COM_HYPERPC_STEP_CONFIGURATOR_STEP_MODEL'),
    Text::_('COM_HYPERPC_STEP_CONFIGURATOR_STEP_PLATFORM'),
    Text::_('COM_HYPERPC_STEP_CONFIGURATOR_STEP_COMPLECTATION'),
    Text::_('COM_HYPERPC_STEP_CONFIGURATOR_STEP_CONFIGURATION'),
    //Text::_('COM_HYPERPC_STEP_CONFIGURATOR_STEP_RESULT')
];

$stepNumber = 0;
?>

<div class="uk-container uk-container-large uk-margin-medium-top">
    <hr class="uk-margin-remove" />
    <div class="uk-flex uk-child-width-1-<?= count($stepTitles) ?> uk-child-width-expand@m hp-step-configurator__progress jsStepConfiguratorProgress">
        <?php foreach ($stepTitles as $i => $title) :
            $class = 'hp-step-configurator__step';
            if (($i + 1) < $currentStep) {
                $class .= ' hp-step-configurator__step--past';
            } elseif (($i + 1) === $currentStep) {
                $class .= ' hp-step-configurator__step--active';
            }

            $href = '#';
            switch ($i) {
                case 0:
                    $href = '/configurator';
                    break;
                case 1:
                    $href = !empty($prevStep) ? '/' . ltrim($prevStep, '/') : '/configurator';
                    if ($currentStep > 2 && empty($prevStep)) {
                        $class .= ' uk-hidden';
                    }
                    break;
                case 2:
                    if ($excludePlatform) {
                        $class .= ' uk-hidden';
                    }
                    break;
            }

            if (strpos($class, 'uk-hidden') === false) {
                $stepNumber++;
            }
            ?>
            <a class="<?= $class ?>" href="<?= $href ?>">
                <small class="uk-text-muted uk-margin-small-right"><?= str_pad($stepNumber, 2, '0', STR_PAD_LEFT) ?>.</small>
                <?= $stepTitles[$i] ?>
            </a>
        <?php endforeach; ?>
    </div>
</div>
<hr class="uk-margin-remove-bottom" />
