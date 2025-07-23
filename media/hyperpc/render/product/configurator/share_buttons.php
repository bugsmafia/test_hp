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
 * @var         \HYPERPC\Helper\RenderHelper $this
 */

defined('_JEXEC') or die('Restricted access');

?>
<a class="sharer uk-icon-link" style="color: #4a76a8;" title="Поделиться Вконтакте" uk-icon="icon: hp-vk; ratio: 1.2" data-sharer="vk"></a>
<a class="sharer uk-icon-link uk-margin-small-left" style="color: #3b5998;" title="Поделиться Facebook" uk-icon="icon: facebook; ratio: 1.2" data-sharer="facebook"></a>
<a class="sharer uk-icon-link uk-margin-small-left" style="color: #1da1f2;" title="Поделиться Twitter" uk-icon="icon: twitter; ratio: 1.2" data-sharer="twitter"></a>
<?php if ($this->hyper['detect']->isMobile()) : ?>
    <a class="sharer uk-icon-link uk-margin-small-left" style="color: #25d366;" title="Поделиться WhatsApp" uk-icon="icon: whatsapp; ratio: 1.2" data-sharer="whatsapp"></a>
<?php endif; ?>
