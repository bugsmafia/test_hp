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
    'class'    => 'uk-position-absolute uk-cover',
    'uk-cover' => ' ',
    'width' => 500,
    'height' => 500
];

//$viewFormUrl = $this->hyper['route']->build(['task' => 'workers.render-form', 'id' => $worker->id]);
// TODO: разобраться со ссылкой, непонятные редиректы, скорее всего CDN
$viewFormUrl = '/index.php?option=com_hyperpc&task=workers.render-form&id=' . $worker->id;
?>

<div>
    <div class="hp-employee-card uk-card uk-card-default uk-card-small tm-card-bordered uk-flex uk-flex-wrap">
        <div class="uk-card-media-left uk-width-1-3@s uk-width-2-5@xl uk-cover-container">
            <?= $this->hyper['helper']['html']->image($imagePath, $imgAttrs) ?>
            <canvas width="500" height="500"></canvas>
        </div>
        <div class="uk-card-body uk-width-expand@s uk-text-center uk-text-left@s">
            <div>
                <div class="hp-employee-card__name uk-text-emphasis uk-margin-small-bottom">
                    <?= $worker->name ?>
                </div>
                <hr class="uk-margin-remove">
                <?php if ($worker->params->get('position')) : ?>
                    <div class="hp-employee-card__function uk-flex uk-flex-middle uk-flex-center uk-flex-left@s uk-text-left">
                        <span class="uk-icon uk-flex-none uk-margin-small-right" uk-icon="user"></span>
                        <dl class="uk-description-list uk-description-list-divider uk-margin-small">
                            <dt class="uk-visible@s"><?= Text::_('COM_HYPERPC_WORKER_FUNCTION') ?></dt>
                            <dd><?= $worker->params->get('position') ?></dd>
                        </dl>
                    </div>
                    <hr class="uk-margin-remove">
                <?php endif; ?>
                <div class="uk-margin-top">
                    <a class="uk-button uk-button-default uk-button-small uk-button-normal@s uk-width-1-1 uk-width-auto@s jsLoadIframe" href="<?= $viewFormUrl ?>">
                        <?= Text::_('COM_HYPERPC_WRITE_A_MESSAGE') ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
