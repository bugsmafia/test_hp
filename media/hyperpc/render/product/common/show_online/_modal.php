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
 * @author      Roman Evsyukov
 *
 * @var         string       $modalKey
 * @var         string       $form
 * @var         RenderHelper $this
 */

use Joomla\CMS\Language\Text;
use HYPERPC\Helper\RenderHelper;

defined('_JEXEC') or die('Restricted access');

?>

<div id="<?= $modalKey ?>" class="uk-modal jsShowOnlineModal jsProductTeaserFormModal" uk-modal="bg-close: false">
    <div class="uk-modal-dialog uk-modal-body uk-margin-auto-vertical">
        <button class="uk-modal-close-default uk-close-large uk-icon uk-close" type="button" uk-close=""></button>
        <div>
            <div class="uk-text-center uk-margin-bottom">
                <div class="uk-h2 uk-margin-small-bottom">
                    <?= Text::_('COM_HYPERPC_SHOW_ONLINE_MODAL_HEADING') ?>
                </div>
                <div>
                    <?= Text::_('COM_HYPERPC_SHOW_ONLINE_MODAL_TEXT') ?>
                </div>
            </div>
            <?= $form ?>
        </div>
    </div>
</div>
