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
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\Registry;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Application\SiteApplication;

/**
 * @var SiteApplication $app
 * @var Registry $params
 */

$configuratorMenuItemId = $params->get('configurator_menu_item', 0);
if (!empty($configuratorMenuItemId)) {
    $configuratorMenuItem = $app->getMenu()->getItem($configuratorMenuItemId);
    $configuratorQuery    = $configuratorMenuItem->link;
}

$configuratorRoute = !empty($configuratorQuery) ? Route::_($configuratorQuery, false) : '/configuration';

/** @noinspection PhpIncludeInspection */
require ModuleHelper::getLayoutPath('mod_hp_configuration', $params->get('layout', 'default'));
