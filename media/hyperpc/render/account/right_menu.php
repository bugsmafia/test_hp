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
 * @var         RenderHelper $this
 */

use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use HYPERPC\Helper\RenderHelper;

defined('_JEXEC') or die('Restricted access');

$view = $this->hyper['input']->get('view');
?>
<ul class="uk-list">
    <li>
        <?php if ($view != 'profile') : ?>
            <a href="<?= Route::_('index.php?option=com_users&view=profile'); ?>" class="uk-link-muted">
                <?= Text::_('COM_HYPERPC_PROFILE') ?>
            </a>
        <?php else : ?>
            <span class="uk-text-emphasis">
                <?= Text::_('COM_HYPERPC_PROFILE') ?>
            </span>
        <?php endif; ?>
    </li>
    <li>
        <hr>
    </li>
    <li>
        <?php if ($view != 'profile_orders') : ?>
            <a href="<?= $this->hyper['route']->build(['view' => 'profile_orders']); ?>" class="uk-link-muted">
                <?= Text::_('COM_HYPERPC_MY_ORDERS') ?>
            </a>
        <?php else : ?>
            <span class="uk-text-emphasis">
                <?= Text::_('COM_HYPERPC_MY_ORDERS') ?>
            </span>
        <?php endif; ?>
    </li>
    <li>
        <?php if ($view != 'profile_configurations') : ?>
            <a href="<?= $this->hyper['route']->build(['view' => 'profile_configurations']); ?>" class="uk-link-muted">
                <?= Text::_('COM_HYPERPC_MY_CONFIGURATIONS') ?>
            </a>
        <?php else : ?>
            <span class="uk-text-emphasis">
                <?= Text::_('COM_HYPERPC_MY_CONFIGURATIONS') ?>
            </span>
        <?php endif; ?>
    </li>
    <li>
        <?php if ($view != 'profile_reviews') : ?>
            <a href="<?= $this->hyper['route']->build(['view' => 'profile_reviews']); ?>" class="uk-link-muted">
                <?= Text::_('COM_HYPERPC_MY_REVIEWS') ?>
            </a>
        <?php else : ?>
            <span class="uk-text-emphasis">
                <?= Text::_('COM_HYPERPC_MY_REVIEWS') ?>
            </span>
        <?php endif; ?>
    </li>
</ul>
