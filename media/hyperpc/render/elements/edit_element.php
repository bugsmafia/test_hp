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
 */

use HYPERPC\Elements\Element;
use Joomla\CMS\Language\Text;
use HYPERPC\Helper\RenderHelper;

/**
 * @var         RenderHelper $this
 * @var         Element      $element
 * @var         string       $id
 */

$elementToggleId = uniqid($element->getType() . '-');
$elementDesc     = $element->getDescription();
$elLangKey       = implode('_', [
    'HYPER_ELEMENT',
    strtoupper($element->getGroup()),
    strtoupper($element->getType()),
    'DESCRIPTION'
]);
?>
<li class="accordion-group border rounded my-1" data-element="<?= $element->getType() ?>">
    <div class="accordion-heading d-flex align-items-center">
        <a href="#" class="hp-icon hp-icon-sort jsFieldSort mb-1"></a>
        <a class="accordion-toggle collapsed ms-3 me-1" data-bs-toggle="collapse" data-parent="#hp-group-<?= $element->getGroup() ?>"
           href="#<?= $elementToggleId ?>">
            <?= $element->getConfig('name') ?>
        </a>
        <span class="hp-element-type">
            (<?= $element->getType() ?>)
        </span>

        <div class="accordion-nav ms-auto">
            <?php if (\method_exists($element, 'isEnabled') && !$element->isEnabled()) : ?>
                <span class="badge bg-danger"><?= Text::_('JDISABLED') ?></span>
            <?php endif; ?>

            <?php if (\method_exists($element, 'isDebug') && $element->isDebug()) : ?>
                <span class="badge bg-warning">Debug mode</span>
            <?php endif; ?>

            <?php if ($element->isForManager()) : ?>
                <span class="badge bg-info">Для менеджеров</span>
            <?php endif; ?>
            <a class="accordion-toggle collapsed" data-bs-toggle="collapse" data-parent="#hp-group-<?= $element->getGroup() ?>"
               href="#<?= $elementToggleId ?>">
                <i class="hp-icon hp-icon-edit"></i>
            </a>
            <a href="#" class="jsRemoveField">
                <i class="hp-icon hp-icon-remove"></i>
            </a>
        </div>
    </div>
    <div id="<?= $elementToggleId ?>" class="accordion-collapse collapse border-top p-2" data-bs-parent="<?= isset($id) ? '#' . $id : '' ?>">
        <div class="accordion-inner">
            <div class="row">
                <div class="col-12">
                    <?php if ($elLangKey !== $elementDesc) : ?>
                        <div class="alert alert-info">
                            <?= $elementDesc ?>
                        </div>
                    <?php endif; ?>
                    <div>
                        <?= $element->getEditParams() ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</li>
