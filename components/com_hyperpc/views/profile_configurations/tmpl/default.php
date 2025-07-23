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
use Joomla\CMS\Layout\LayoutHelper;
use HyperPcViewProfile_Configurations as View;
use HYPERPC\Joomla\Model\Entity\SaveConfiguration;

/**
 * @var         View                $this
 * @var         SaveConfiguration   $item
 */

HTMLHelper::_('behavior.core');
?>
<div class="uk-container">
    <div class="uk-text-center">
        <h1 class="uk-margin-medium-bottom">
            <?= Text::_('COM_HYPERPC_MY_CONFIGURATIONS') ?>
        </h1>
    </div>
    <div class="uk-grid uk-grid-divider uk-margin-bottom" uk-grid>
        <div class="uk-width-auto uk-visible@m">
            <?= $this->hyper['helper']['render']->render('account/right_menu') ?>
        </div>
        <div class="uk-width-expand">
            <?php if (empty($this->filterForm->getData()->get('filter.search')) && !count($this->items)) : ?>
                <div class="tm-text-italic">
                    <?= Text::_('COM_HYPERPC_YOU_DO_NOT_HAVE_SAVED_CONFIGURATIONS_YET') ?>
                </div>
            <?php else : ?>
                <div class="uk-clearfix">
                    <form action="<?= $this->hyper['route']->build(['view' => 'profile_configurations']) ?>"
                        method="post" name="adminForm" id="adminForm">
                        <?= LayoutHelper::render('joomla.searchtools.default', ['view' => $this]); ?>
                        <?= HTMLHelper::_('form.token') ?>
                    </form>
                </div>

                <?php if (empty($this->items)) : ?>
                    <div class="tm-text-italic uk-margin">
                        <?= Text::_('COM_HYPERPC_NOTHING_FOUND') ?>
                    </div>
                <?php else : ?>
                    <?= $this->hyper['helper']['render']->render('user/profile/configurations_list', [
                        'configurations' => $this->items
                    ]); ?>
                <?php endif; ?>
            <?php endif; ?>

        </div>
    </div>
</div>
