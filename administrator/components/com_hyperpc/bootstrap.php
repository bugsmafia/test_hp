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

defined('_JEXEC') or die('Restricted access');

use HYPERPC\App;

$autoload = JPATH_LIBRARIES . '/hyperpc/vendor/autoload.php';
if (file_exists($autoload)) {
    /** @noinspection PhpIncludeInspection */
    require_once $autoload;
} else {
    throw new Exception('Please execute "composer update" !');
}

require_once __DIR__ . '/defines.php';

$app = App::getInstance();
$app->initialize();

$app['event']
    ->on('initialize', [
        'HYPERPC\Event\SystemEventHandler',
        'initialize'
    ])
    ->trigger('initialize');
