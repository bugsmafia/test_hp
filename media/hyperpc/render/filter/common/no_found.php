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
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
?>
<div class="uk-width-1-1">
    <div class="uk-text-center">
        <div class="uk-text-large">
            <?= Text::_('COM_HYPERPC_FILTERS_RESULT_NOT_FOUND') ?>
        </div>
        <div class="uk-margin">
            <button class="jsClearAllFilters uk-button uk-button-default" type="button">
                <?= Text::_('COM_HYPERPC_CLEAR_ALL_FILTERS') ?>
            </button>
        </div>
    </div>
</div>
