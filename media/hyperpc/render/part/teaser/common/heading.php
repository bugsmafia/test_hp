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

use HYPERPC\Helper\RenderHelper;
use HYPERPC\Joomla\Model\Entity\Interfaces\PartMarker;

defined('_JEXEC') or die('Restricted access');

/**
 * @var bool         $optionTakenFromPart
 * @var RenderHelper $this
 * @var PartMarker   $part
 */

$viewUrl = $optionTakenFromPart ? $part->option->getViewUrl() : $part->getViewUrl();

$linkAttrs = [
    'href'     => $viewUrl,
    'class'    => 'uk-display-block',
    'title'    => strlen($part->name) > 23 ? $part->name : ''
];

$gtmOnclick = $this->hyper['helper']['render']->render('common/teaser/gtmProductClick', ['entity' => $part]);
?>

<a <?= $this->hyper['helper']['html']->buildAttrs($linkAttrs) ?><?= $gtmOnclick ?>>
    <?= $part->name ?>
</a>
