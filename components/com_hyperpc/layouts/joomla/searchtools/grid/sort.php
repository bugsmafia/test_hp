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
 * @author      Sergey Kalistratov <kalistratov.s.m@gmail.com>
 *
 * @var         array $displayData
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

$data = $displayData;

$title = htmlspecialchars(Text::_($data->tip ?: $data->title));
HTMLHelper::_('bootstrap.popover');
?>
<a href="#" onclick="return false;" class="js-stools-column-order hasPopover"
   data-order="<?php echo $data->order; ?>" data-direction="<?php echo strtoupper($data->direction); ?>" data-name="<?php echo Text::_($data->title); ?>"
   title="<?php echo $title; ?>" data-content="<?php echo htmlspecialchars(Text::_('JGLOBAL_CLICK_TO_SORT_THIS_COLUMN')); ?>" data-placement="top">
    <?php if (!empty($data->icon)) : ?><span class="<?php echo $data->icon; ?>"></span><?php endif; ?>
    <?php if (!empty($data->title)) : ?><?php echo Text::_($data->title); ?><?php endif; ?>
    <?php if ($data->order == $data->selected) : ?><span class="<?php echo $data->orderIcon; ?>"></span><?php endif; ?>
</a>
