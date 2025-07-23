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
 *
 * @var         string          $modalKey
 * @var         string          $form
 * @var         RenderHelper    $this
 *
 */

use Joomla\CMS\Language\Text;
use HYPERPC\Helper\RenderHelper;

defined('_JEXEC') or die('Restricted access');

?>

<div id="<?= $modalKey ?>" class="uk-modal jsBuyNowModal jsProductTeaserFormModal" uk-modal="bg-close: false">
    <div class="uk-modal-dialog uk-modal-body uk-margin-auto-vertical">
        <button class="uk-modal-close-default uk-close-large uk-icon uk-close" type="button" uk-close></button>
        <div>
            <div class="uk-h3 uk-text-center">
                <?= Text::_('COM_HYPERPC_BUY_NOW_MODAL_HEADING') ?>
            </div>
            <?= $form ?>
        </div>
    </div>
</div>
