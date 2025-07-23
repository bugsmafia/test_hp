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
use HYPERPC\Helper\RenderHelper;

/**
 * @var RenderHelper    $this
 */
?>
<div class="uk-text-small uk-text-muted">
    <?= Text::_('COM_HYPERPC_FPS_DISCLAIMER') ?>
    <?php if (!empty($this->hyper['params']->get('fps_info_article'))) : ?>
        <a href="<?= $this->hyper['params']->get('fps_info_article') ?>" target="_blank" class="jsLoadIframe">
            <?= Text::_('COM_HYPERPC_DETAILS') ?>
        </a>
    <?php endif; ?>
</div>
