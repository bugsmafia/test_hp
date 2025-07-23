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
 * @var         Review              $item
 * @var         HyperPcViewReviews  $this
 */

defined('_JEXEC') or die('Restricted access');

$formAction = $this->hyper['route']->build([
    'view' => '%view',
]);

use Joomla\CMS\Language\Text;
use HYPERPC\ORM\Entity\Review;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;
use HYPERPC\Joomla\Model\Entity\Interfaces\ProductMarker;
?>

<form action="<?= $formAction ?>" method="post" name="adminForm" id="adminForm" class="main-card">
    <div class="row main-card-columns">
        <div id="j-main-container" class="col-12">
            <?= LayoutHelper::render('joomla.searchtools.default', ['view' => $this]); ?>
            <table class="table table-striped table-hover align-middle" id="partList">
                <thead>
                <tr>
                    <th width="1%" class="center">
                        <?= HTMLHelper::_('grid.checkall') ?>
                    </th>
                    <th width="10%">
                        <?= Text::_('COM_HYPERPC_REVIEW_CONTEXT_TITLE') ?>
                    </th>
                    <th class="nowrap">
                        <?= Text::_('COM_HYPERPC_REVIEW_ITEM_ID_TITLE') ?>
                    </th>
                    <th class="nowrap">
                        <?= Text::_('COM_HYPERPC_LINK') ?>
                    </th>
                    <th class="nowrap">
                        <?= Text::_('COM_HYPERPC_REVIEW_RATING_TITLE') ?>
                    </th>
                    <th width="1%" class="nowrap center">
                        <?= Text::_('JSTATUS') ?>
                    </th>
                    <th width="1%" class="nowrap center">
                        <?= Text::_('ID') ?>
                    </th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($this->items as $i => $item) :
                    $editUrl = $this->hyper['route']->build([
                        'layout' => 'edit',
                        'id'     => $item->id,
                        'view'   => 'review'
                    ]);

                    /** @var ProductMarker $entity */
                    $entity = $item->getItem();
                    $reviewUrl = trim($item->params->get('user_review_url', ''));
                    ?>
                    <tr>
                        <td>
                            <?= HTMLHelper::_('grid.id', $i, $item->id) ?>
                        </td>
                        <td>
                            <a href="<?= $editUrl ?>">
                                <?= $item->getContextTitle() ?>
                            </a>
                        </td>
                        <td>
                            <a href="<?= $editUrl ?>">
                                <?= $entity->getName() ?>
                            </a>
                        </td>
                        <td>
                            <?php if (!empty($reviewUrl)) : ?>
                                <a href="<?= $reviewUrl ?>">
                                    <?= $reviewUrl ?>
                                </a>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="jsRating" data-score="<?= $item->rating ?>"></div>
                        </td>
                        <td class="center">
                            <?= HTMLHelper::_('jgrid.published', $item->published, $i, $this->getName() . '.') ?>
                        </td>
                        <td class="center">
                            <?= $item->id ?>
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
