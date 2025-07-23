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
 * @var         RenderHelper    $this
 * @var         Element         $element
 * @var         array           $elements
 */

use HYPERPC\Helper\RenderHelper;
use HYPERPC\Html\Types\Filter\Element;

defined('_JEXEC') or die('Restricted access');
?>
<ul class="uk-accordion uk-list-divider uk-margin-remove-bottom" uk-accordion="multiple: true">
    <?php foreach ($elements as $element) :
        $liAttrs = [
            'class' => 'hp-group-filter',
            'data'  => [
                'filter' => $element->name
            ]
        ];

        if ($element->hasActive) {
            $liAttrs['class'] .= ' uk-open';
        }
        ?>
        <li <?= $this->hyper['helper']['html']->buildAttrs($liAttrs) ?>>
            <a class="uk-accordion-title" href="#">
                <?= $element->title ?>
                &nbsp;
                <span class="jsFilterMark uk-text-primary">
                    <?= ($element->hasFilters === true) ? '&bull;' : null ?>
                </span>
            </a>
            <div class="hp-filter-options uk-accordion-content"<?= !$element->hasActive ? ' hidden' : '' ?>>
                <?php foreach ($element->html as $html) : ?>
                    <div class="uk-margin-small">
                        <?= $html ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </li>
    <?php endforeach; ?>
</ul>
