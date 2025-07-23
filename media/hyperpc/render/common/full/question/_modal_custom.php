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
 * @var string $modalKey
 * @var string $form
 * @var RenderHelper $this
 */

?>

<div id="<?= $modalKey ?>" class="uk-modal jsProductQuestionModal" uk-modal="bg-close: false">
    <?= $form ?>
</div>
