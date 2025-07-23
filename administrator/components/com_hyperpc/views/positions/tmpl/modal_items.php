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
 * @author      Roman Evsyukov
 * @author      Artem Vyshnevskiy
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\Input\Input;
use Joomla\CMS\HTML\HTMLHelper;
use HYPERPC\Joomla\Model\Entity\MoyskladPart;

/**
 * @var HyperPcViewPositions $this
 */

/** @var Input $input */
$input = $this->hyper['input'];

/** @todo check onclick, maybe it should be done using a "function" property */
$element    = $input->getCmd('element');
$onclick    = $this->escape($element);

$showOptions = $input->getBool('show_options', true);
?>
<?php if (count($this->items) > 0 && (bool) $this->items !== false) : ?>
    <?php foreach ($this->items as $i => $item) :
        $attrs = [
            'href'  => '#',
            'title' => $item->name,
            'class' => 'jsChooseItem',
            'data'  => [
                'id'      => $item->getItemKey(),
                'uri'     => $item->getViewUrl(),
                'element' => $this->escape($onclick),
                'title'   => $this->escape(addslashes($item->name)),
                'name'    => $this->escape(addslashes($item->name))
            ]
        ];
        ?>
        <tr>
            <td class="text-nowrap">
                <?= HTMLHelper::_('grid.id', $i, $item->id) ?>
            </td>
            <td>
                <a <?= $this->hyper['helper']['html']->buildAttrs($attrs) ?>>
                    <?= $item->name ?>
                </a>
            </td>
            <td class="small d-none d-md-table-cell">
                <?= $item->getTypeName(); ?>
            </td>
            <td class="text-nowrap">
                <?= $item->getListPrice(); ?>
            </td>
            <td class="text-nowrap">
                <?= $item->getSalePrice(); ?>
            </td>
            <td>
                <div class="btn-group">
                    <?php
                    if (in_array($item->state, [HP_STATUS_PUBLISHED, HP_STATUS_UNPUBLISHED])) {
                        echo HTMLHelper::_('jgrid.published', (int) $item->state, $i, 'positions.');
                    } else {
                        echo $this->hyper['helper']['html']->published($item->state);
                    }
                    ?>
                </div>
            </td>
            <td class="d-none d-md-table-cell">
                <span class="badge bg-info"><?= $item->id ?></span>
            </td>
        </tr>

        <?php if ($showOptions && $item instanceof MoyskladPart) : ?>
            <?php
            $options = $item->getOptions();
            foreach ($options as $i => $option) :
                $attrs = [
                    'href'  => '#',
                    'title' => $option->name,
                    'class' => 'jsChooseItem',
                    'data'  => [
                        'id'      => $option->getItemKey(),
                        'uri'     => $option->getViewUrl(),
                        'element' => $this->escape($onclick),
                        'title'   => $this->escape(addslashes($item->name).' '.addslashes($option->name)),
                        'name'    => $this->escape(addslashes($item->name).' '.addslashes($option->name))
                    ]
                ];
                ?>
                <tr>
                    <td class="center">
                        <?= HTMLHelper::_('grid.id', $i, $option->id) ?>
                    </td>
                    <td>
                        <span class="muted">┊&nbsp;&nbsp;&nbsp;</span>
                        –&nbsp;
                        <a <?= $this->hyper['helper']['html']->buildAttrs($attrs) ?>>
                            <?= $option->name ?>
                        </a>
                    </td>
                    <td>
                        <?= $option->price->html() ?>
                    </td>
                    <td class="center">
                        <div class="btn-group">
                        </div>
                    </td>
                    <td class="center">
                        <span class="badge bg-info"><?= $option->id ?></span>
                    </td>
                </tr>
            <?php endforeach;?>
        <?php endif; ?>

    <?php endforeach; ?>
<?php endif;
