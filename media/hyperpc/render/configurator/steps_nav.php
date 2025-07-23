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
?>

<div class="tm-background-gray-5 hp-step-configurator__sticky">
    <hr class="uk-margin-remove uk-visible@s" />
    <div class="uk-container uk-container-large uk-text-right">
        <button class="jsStepBack uk-button uk-button-link uk-text-emphasis uk-margin-right">
            <span uk-icon="chevron-left"></span><?= Text::_('COM_HYPERPC_STEP_CONFIGURATOR_BACK') ?>
        </button>
        <button class="jsStepForward uk-button uk-button-primary uk-button-large">
            <?= Text::_('COM_HYPERPC_STEP_CONFIGURATOR_FORWARD') ?>
        </button>
    </div>
    <hr class="uk-margin-remove uk-visible@s" />
</div>
<div class="hp-step-configurator__sticky-placeholder"></div>
