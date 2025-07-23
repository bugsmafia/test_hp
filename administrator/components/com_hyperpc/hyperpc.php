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
 * @link        https://github.com/HYPER-PC/HYPERPC".
 *
 * @author      Sergey Kalistratov © <kalistratov.s.m@gmail.com>
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Access\Exception\NotAllowed;
use HYPERPC\Joomla\Controller\ControllerLegacy;

defined('_JEXEC') or die('Restricted access');


if (!Factory::getUser()->authorise('core.manage', HP_OPTION)) {
    throw new NotAllowed(Text::_('JERROR_ALERTNOAUTHOR'), 403);
}

$input = JFactory::getApplication()->input;
$task = $input->getCmd('task', '');
if ($task) {
    $taskArray = explode('.', $task);
    $task = end($taskArray);
}

/** @var HyperPcController $controller */
$controller = ControllerLegacy::getInstance('hyperpc');
$controller->execute($task);
$controller->redirect();
