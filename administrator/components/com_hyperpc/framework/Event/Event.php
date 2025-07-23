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
 */

namespace HYPERPC\Event;

use HYPERPC\App;
use HYPERPC\Container;

/**
 * Class Event
 *
 * @package HYPERPC\Event
 *
 * @since 2.0
 */
abstract class Event extends Container
{

    /**
     * Get HYPERPC Application object.
     *
     * @return App
     * @throws \Exception
     *
     * @since 2.0
     */
    public static function getApp()
    {
        return App::getInstance();
    }
}
