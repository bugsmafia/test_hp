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
 * @var         string $itemKey
 * @var         \HYPERPC\Joomla\Model\Entity\Product $item
 *
 */

defined('_JEXEC') or die('Restricted access');

$isMobile = $this->hyper['detect']->isMobile();

$render = $item->render();
$render->setEntity($item);
$configurationHtml = $render->configuration();
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
