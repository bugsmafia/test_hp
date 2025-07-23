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
 *
 * @todo        use media\hyperpc\render\group\filters-ajax.php
 */

use Joomla\CMS\Language\Text;

defined('_JEXEC') or die('Restricted access');

$isMobile = $this->hyper['detect']->isMobile();

if (!isset($filterCount)) {
    $filterCount = 0;
}

$hasActive = ($filterCount > 0);

if (!isset($elements)) {
    //  TODO no render elements.
}
?>
<?php if ($isMobile) : ?>
    <div class="hp-group__filters uk-modal uk-modal-full" uk-modal="bg-close: false">
        <div class="uk-modal-dialog uk-modal-body tm-background-gray-5" uk-height-viewport>
            <button class="uk-modal-close-full uk-close-large" type="button" uk-close></button>
            <a class="jsClearAllFilters uk-buton uk-button-link uk-position-top-left uk-padding uk-padding-remove-vertical uk-margin-top tm-text-medium uk-text-bold"<?= !$hasActive ? ' hidden' : '' ?>>
                <?= Text::_('COM_HYPERPC_CLEAR_ALL') ?>
            </a>
            <div class="uk-margin-medium-top uk-margin-large-bottom">
                <?= $this->hyper['helper']['moyskladFilter']->renderFieldList($elements) ?>
            </div>
        </div>
        <div class="uk-position-fixed uk-position-bottom uk-position-small uk-background-default">
            <button class="jsCloseFiltersModal uk-button uk-button-primary uk-width-1-1" type="button">
                <?= Text::_('COM_HYPERPC_SHOW') ?>
                <span class="jsFiltersResultCount"></span>
            </button>
        </div>
    </div>
<?php else : ?>
    <div class="hp-group__filters uk-margin-medium uk-width-1-6@xl uk-width-1-5@l uk-width-1-4@m uk-first-column">
        <div class="jsGroupFiltersSticky">
            <?= $this->hyper['helper']['moyskladFilter']->renderFieldList($elements) ?>
        </div>
    </div>
<?php endif;
