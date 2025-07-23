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

$popupInfo = $this->hyper['helper']['credit']->renderInfoPopup();
?>

<?php if ($this->hyper['helper']['credit']->hasPopupInfo()) : ?>
    <a href="#credit-info" class="uk-link-muted uk-icon" uk-toggle uk-icon="icon: question; ratio: 0.7"></a>
<?php endif; ?>

<?php if (!empty($popupInfo)) : ?>
    <div id="credit-info" class="uk-modal" uk-modal>
        <?= $popupInfo ?>
    </div>
<?php endif;
