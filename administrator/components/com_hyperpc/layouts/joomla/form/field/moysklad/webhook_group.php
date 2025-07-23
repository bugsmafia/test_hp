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

use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Utilities\ArrayHelper;

/**
 * @var array $displayData
 */

$entityType = $displayData['entityType'];
?>

<fieldset class="options-form">
    <legend>
        <?= $entityType ?>
    </legend>

    <?php foreach ($displayData['actions'] as $action => $key) : ?>
        <div class="control-group d-flex flex-column">
            <div class="control-label">
                <b><?= $action ?></b>:
            </div>
            <div class="controls">
                <div class="input-group">
                    <?php
                        $inputAttributes = [
                            'type' => 'text',
                            'value' => $key,
                            'class' => 'jsWebhookKey input-xxlarge form-control'
                        ];

                        $createBtnAttributes = [
                            'class' => 'jsCreateWebhook btn btn-success',
                            'data-entity' => $entityType,
                            'data-action' => $action,
                            'data-url' => Uri::root() . 'index.php?option=com_hyperpc&task=moysklad.webhook_' . $action
                        ];

                        $removeBtnAttributes = [
                            'class' => 'jsRemoveWebhook btn btn-danger',
                            'data-key' => $key
                        ];
                    ?>
                    <input <?= ArrayHelper::toString($inputAttributes); ?> readonly />
                    <?php if (empty($key)) : ?>
                        <?= HTMLHelper::_('link', '#', '<span class="icon-plus-circle"></span>', $createBtnAttributes); ?>
                    <?php else : ?>
                        <?= HTMLHelper::_('link', '#', '<span class="icon-trash"></span>', $removeBtnAttributes); ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

</fieldset>
