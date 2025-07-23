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
 * @author      Artem Vyshnevskiy
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

/**
 * @var HyperPcViewMoysklad $this
 */
?>

<div class="main-card">
    <div class="row main-card-columns">
        <div id="j-main-container" class="col-12">
            <div class="main-card p-3">
                <h2><?= Text::_('COM_HYPERPC_SIDEBAR_MOYSKLAD') ?></h2>
                <p>Webhooks</p>

                <?php foreach ($this->webhooks as $entity => $actions) : ?>
                    <?= LayoutHelper::render('joomla.form.field.moysklad.webhook_group', [
                        'entityType' => $entity,
                        'actions'    => $actions
                    ]) ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
