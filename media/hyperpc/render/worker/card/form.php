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
 * @var         \HYPERPC\Joomla\Model\Entity\Worker $worker
 * @var         \HYPERPC\Helper\RenderHelper $this
 */

use Joomla\CMS\Language\Text;

$imagePath = $this->hyper['helper']['worker']->getPhotoPath($worker);

$imgAttrs = [
    'width' => 500,
    'height' => 500
];
?>
<div>
    <div class="uk-grid uk-grid-divider uk-grid-small" uk-grid>
        <div class="uk-width-1-4@m">
            <div>
                <div class="uk-grid">
                    <div class="uk-width-1-3@s uk-width-1-1@m uk-text-center">
                        <?= $this->hyper['helper']['html']->image($imagePath, $imgAttrs) ?>
                    </div>
                    <div class="uk-width-2-3@s uk-width-1-1@m uk-margin-top">
                        <div class="uk-text-emphasis uk-text-large uk-margin-small uk-text-center uk-text-left@s">
                            <?= $worker->name ?>
                        </div>
                        <?php if ($worker->params->get('position')) : ?>
                            <hr class="uk-margin-remove">
                            <div class="uk-flex uk-flex-middle uk-flex-center uk-flex-left@s uk-text-left">
                                <span class="uk-icon uk-flex-none uk-margin-small-right" uk-icon="user"></span>
                                <dl class="uk-description-list uk-description-list-divider uk-margin-small">
                                    <dt class="uk-visible@s"><?= Text::_('COM_HYPERPC_WORKER_FUNCTION') ?></dt>
                                    <dd><?= $worker->params->get('position') ?></dd>
                                </dl>
                            </div>
                        <?php endif; ?>
                        <hr class="uk-visible@m uk-margin-remove">
                    </div>
                </div>
            </div>
        </div>
        <div class="uk-width-3-4@m">
            <div>
                <?= $worker->getRender()->form() ?>
            </div>
        </div>
    </div>
</div>
