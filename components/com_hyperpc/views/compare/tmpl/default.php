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

/**
 * @var HyperPcViewCompare $this
 */

$countItems = count($this->items->getArrayCopy());
?>
<div class="hp-compare">
    <div class="uk-container uk-container-large">
        <h1 class="uk-h2 uk-text-center uk-margin-top"><?= Text::_('COM_HYPERPC_COMPARE_PAGE_TITLE') ?></h1>
        <div class="hp-compare-wrapper">
            <?php if ($countItems > 1) : ?>
                <div class="uk-margin">
                    <a class="jsClearCompare uk-button uk-button-link uk-link-muted">
                        <span uk-icon="icon: trash;"></span>
                        <?= Text::_('COM_HYPERPC_COMPARE_CLEAR_ALL') ?>
                    </a>
                </div>
            <?php endif; ?>
            <?php if ($this->hyper['helper']['compare']->countItems()) : ?>
                <?php
                if ($countItems) {
                    echo $this->renderLayout('default_items', [
                        'items' => $this->items
                    ], false);
                }
                ?>
            <?php else : ?>
                <div class="uk-container uk-container-small">
                    <div class="uk-alert uk-alert-warning"><?= Text::_('COM_HYPERPC_COMPARE_EMPTY') ?></div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
