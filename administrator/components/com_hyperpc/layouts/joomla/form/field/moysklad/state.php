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

use HYPERPC\App;
use JBZoo\Data\Data;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die('Restricted access');

$app      = App::getInstance();
$data     = new Data($displayData);
$state    = $data->get('state') !== null ? $data->get('state') : $data->get('published');
?>
<select name="<?= $data->get('name') ?>" class="form-control form-select chzn-color-state">
    <?php if ($state === HP_STATUS_ARCHIVED) : ?>
        <option selected="selected" value="2"><?= Text::_('JARCHIVED'); ?></option>
    <?php elseif ($state === HP_STATUS_TRASHED) : ?>
        <option selected="selected" value="-2"><?= Text::_('JTRASHED'); ?></option>
    <?php else : ?>
        <option <?= $state === 1 ? 'selected="selected"' : '' ?> value="1"><?= Text::_('JPUBLISHED'); ?></option>
        <option <?= $state === 0 ? 'selected="selected"' : '' ?> value="0"><?= Text::_('JUNPUBLISHED'); ?></option>
    <?php endif; ?>
</select>
