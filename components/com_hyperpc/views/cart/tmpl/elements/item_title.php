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

use HYPERPC\Helper\CartHelper;
use HYPERPC\Joomla\Model\Entity\Entity;
use HYPERPC\Joomla\Model\Entity\MoyskladProduct;

/**
 * @var string $type
 * @var Entity $item
 */
?>

<?php if ($type === CartHelper::TYPE_POSITION && $item instanceof MoyskladProduct) : ?>
    <div class="uk-h4 uk-margin-remove">
        <?= $item->name ?>
    </div>
<?php elseif ($type === CartHelper::TYPE_POSITION) : ?>
    <div class="uk-text-muted">
        <?= $item->getFolder()->title ?>
    </div>
    <div class="uk-text-emphasis">
        <a href="<?= $item->getViewUrl(['opt' => true]) ?>" class="uk-link-reset" target="_blank">
            <?= $item->getConfiguratorName() ?>
        </a>
    </div>
<?php endif;
