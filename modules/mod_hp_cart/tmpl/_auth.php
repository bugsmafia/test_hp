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

use HYPERPC\Helper\RenderHelper;

/**
 * @var RenderHelper    $this
 */

?>

<div id="login-form-modal" class="uk-flex-top uk-modal" data-uk-modal="bg-close: false">
    <div class="uk-modal-dialog uk-margin-auto-vertical uk-margin-remove-bottom uk-margin-auto-bottom@s">
        <button class="uk-modal-close-default uk-close uk-icon" type="button" data-uk-close></button>

        <div>
            <div class="uk-modal-body">
                <?= $this->render('login/auth') ?>
            </div>
        </div>

    </div>
</div>
