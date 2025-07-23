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

use HYPERPC\Joomla\Model\Entity\Position;
use HYPERPC\Joomla\Model\Entity\MoyskladProduct;

/**
 * @var         string $itemKey
 * @var         Position $item
 */

if (!($item instanceof MoyskladProduct)) {
    return false;
}

$isMobile = $this->hyper['detect']->isMobile();

$render = $item->getRender();
$render->setEntity($item);
$configurationHtml = $render->configuration(true);
?>

<?php if ($isMobile) : ?>
    <div id="<?= $itemKey ?>" hidden>
        <?= $configurationHtml ?>
    </div>
<?php else : ?>
    <?= $this->hyper['helper']['uikit']->modal($itemKey, implode(PHP_EOL, [
        '<div class="uk-container-small uk-margin-auto">',
            '<div class="uk-h2">' . $item->getName() . '</div>',
            $configurationHtml,
        '</div>'
    ])); ?>
<?php endif;
