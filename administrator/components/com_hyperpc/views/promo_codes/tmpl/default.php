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
 * @author      Sergey Kalistratov <kalistratov.s.m@gmail.com>
 *
 * @var         HyperPcViewPromo_Codes $this
 * @var         \HYPERPC\Joomla\Model\Entity\PromoCode $item
 */

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

defined('_JEXEC') or die('Restricted access');

$formAction = $this->hyper['route']->build([
    'view' => 'promo_codes',
]);
?>
<form action="<?= $formAction ?>" method="post" name="adminForm" id="adminForm" class="main-card">
    <div class="row main-card-columns">
        <div id="j-main-container" class="col-12">
            <table class="table table-striped table-hover align-middle" id="partList">
                <thead>
                <tr>
                    <th width="1%" class="center">
                        <?= HTMLHelper::_('grid.checkall') ?>
                    </th>
                    <th class="nowrap">
                        <?= Text::_('COM_HYPERPC_PROMO_CODE_TITLE') ?>
                    </th>
                    <th width="1%" class="nowrap center">
                        <?= Text::_('COM_HYPERPC_PROMO_CODE_RATE') ?>
                    </th>
                    <th width="1%" class="nowrap center">
                        <?= Text::_('JSTATUS') ?>
                    </th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($this->items as $i => $item) :
                    $editUrl = $this->hyper['route']->build([
                        'layout' => 'edit',
                        'id'     => $item->id,
                        'view'   => 'promo_code'
                    ]);

                    $codeItems = $item->getItems();

                    $linkAttrs = [
                        'href'  => $editUrl,
                        'title' => $item->code,
                        'class' => 'hasPopover'
                    ];

                    if (count($codeItems)) {
                        $renderItems = [];
                        /** @var \HYPERPC\Joomla\Model\Entity\Entity $item */
                        foreach ($codeItems as $_item) {
                            $renderItems[] = $_item->name;
                        }

                        $linkAttrs['data']['content'] = implode("\n", $renderItems);
                    }
                    ?>
                    <tr>
                        <td>
                            <?= HTMLHelper::_('grid.id', $i, $item->id) ?>
                        </td>
                        <td>
                            <a <?= $this->hyper['helper']['html']->buildAttrs($linkAttrs) ?>>
                                <?= $item->code ?>
                            </a>
                        </td>
                        <td class="center">
                            <?= $item->type === 2 ? $item->rate . 'руб' : $item->rate . '%' ?>
                        </td>
                        <td class="center">
                            <?= HTMLHelper::_('jgrid.published', $item->published, $i, 'promo_codes.') ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot>
                <tr>
                    <td colspan="6">
                        <?= $this->pagination->getListFooter() ?>
                    </td>
                </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <input type="hidden" name="task" />
    <input type="hidden" name="boxchecked" />
    <?= HTMLHelper::_('form.token'); ?>
</form>

