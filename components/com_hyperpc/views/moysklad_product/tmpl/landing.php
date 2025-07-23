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

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

/**
 * @var HyperPcViewMoysklad_Product $this
 */

$app = Factory::getApplication();

$activeMenuItem = $app->getMenu()->getActive();

if ($activeMenuItem) {
    $description = $this->hyper['helper']['string']->filterLanguage($activeMenuItem->getParams()->get('description', []));
}

echo HTMLHelper::_('content.prepare', $description ?? '');
