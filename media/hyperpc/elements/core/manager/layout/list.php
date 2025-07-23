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
 * @author      Artem Vyshnevskiy
 *
 * @var         ElementCoreManager $this
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;

$inputAttr1 = [
    'type'  => 'text',
    'class' => 'uk-input',
    'name'  => $this->getControlName('value')
];
?>
<div id="field-<?= $this->getIdentifier() ?>" class="uk-margin">
    <a class="uk-h4 uk-margin-small">
        <?= $this->getTitle() ?>
    </a>
    <div class="uk-form-controls">
        <div class="uk-margin">
            <?php
            echo HTMLHelper::_(
                'select.genericlist',
                $this->getListOptions(),
                $this->getControlName('value'),
                'class="uk-select"',
                'value',
                'text',
                $this->getCookieValue(),
                null
            );
            ?>
        </div>
    </div>
</div>
