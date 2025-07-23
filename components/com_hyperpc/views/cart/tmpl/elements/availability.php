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

use HYPERPC\Joomla\Model\Entity\Entity;
use HYPERPC\Joomla\Model\Entity\Interfaces\PartMarker;
use HYPERPC\Joomla\Model\Entity\Interfaces\Stockable;
use Joomla\CMS\Language\Text;

/**
 * @var string  $availability
 * @var bool    $onlyUpgrade
 * @var Entity  $item
 */

$labelClass = match ($availability) {
    Stockable::AVAILABILITY_DISCONTINUED => 'uk-text-danger',
    Stockable::AVAILABILITY_PREORDER => 'uk-text-warning',
    default => ''
};

$text = '';
if ($onlyUpgrade) {
    $text = Text::_('COM_HYPERPC_ONLY_FOR_UPGRADE');
} elseif ($availability === Stockable::AVAILABILITY_PREORDER) {
    if ($item instanceof PartMarker) {
        $text = Text::_('COM_HYPERPC_PART_CONDITIONS_PREORDER_SUB');
    }
}
?>
<span class="<?= $labelClass ?>">
    <?= Text::_('COM_HYPERPC_AVAILABILITY_LABEL_' . strtoupper($availability)) ?>
</span>
<?php if (!empty($text)) : ?>
    <div class="uk-text-muted tm-text-italic">
        <?= $text ?>
    </div>
<?php endif;
