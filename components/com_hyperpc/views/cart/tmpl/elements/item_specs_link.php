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
use HYPERPC\Helper\CartHelper;
use HYPERPC\Joomla\Model\Entity\Entity;
use HYPERPC\Joomla\Model\Entity\MoyskladProduct;

/**
 * @var         string $type
 * @var         string $itemKey
 * @var         Entity $item
 */

$isMobile        = $this->hyper['detect']->isMobile();
$itemName        = '';
$toggledText     = '';
$isConfiguration = false;
$showLink        = false;

switch ($type) {
    case CartHelper::TYPE_POSITION:
        if ($item instanceof MoyskladProduct) {
            $showLink        = true;
            $configuration   = $item->getConfiguration();
            $isConfiguration = $configuration->id > 0 && !$item->isFromStock();
            $itemName        = $isConfiguration ? Text::sprintf('COM_HYPERPC_CART_CONFIGURATION_NUMBER', $configuration->getName()) : Text::_('COM_HYPERPC_CART_CONFIGURATION');
            $toggledText     = Text::_('COM_HYPERPC_CART_HIDE_CONFIGURATION');
        }

        break;
}
?>

<?php if ($showLink) : ?>
    <?php if ($isMobile) : ?>
        <span class="uk-text-primary jsDetailToggle jsShowMore"
            toggled-text="<?= $toggledText ?>"
            toggled-icon="icon:chevron-down" uk-toggle="target: #<?= $itemKey ?>; animation: uk-animation-fade;">
            <span uk-icon="icon: chevron-right" style="vertical-align: bottom; margin: 0 -5px"></span>
            <?= $itemName ?>
        </span>
    <?php else : ?>
        <a href="#<?= $itemKey ?>" uk-toggle>
            <?= $itemName ?>
        </a>
    <?php endif; ?>

    <?php if ($isConfiguration) : ?>
        |
        <a href="<?= $item->getConfigUrl($configuration->id) ?>">
            <?= Text::_('COM_HYPERPC_EDIT_CONFIGURATION') ?>
        </a>
    <?php endif; ?>

<?php endif;
