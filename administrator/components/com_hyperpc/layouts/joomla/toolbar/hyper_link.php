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

use HYPERPC\Data\JSON;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die('Restricted access');

$data = new JSON($displayData);
?>
<joomla-toolbar-button>
    <a class="btn btn-small" href="<?= $data->get('url') ?>" target="<?= $data->get('target') ?>">
        <span class="icon-<?= $data->get('icon') ?>"></span>
        <?= Text::_($data->get('text')) ?>
    </a>
</joomla-toolbar-button>

