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
 * @author      Artem Vyshnevskiy
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\Registry\Registry;
use Joomla\CMS\Helper\ModuleHelper;

/**
 * @var Registry $params
 * @var stdClass $module
 */

// Include the helper.
JLoader::register('ModMicrotransactionFormHelper', __DIR__ . '/helper.php');

ModMicrotransactionFormHelper::prepareData($params);
ModMicrotransactionFormHelper::setAssets($params);

/** @noinspection PhpIncludeInspection */
require ModuleHelper::getLayoutPath('mod_hp_microtransaction_form', $params->get('layout', 'default'));
