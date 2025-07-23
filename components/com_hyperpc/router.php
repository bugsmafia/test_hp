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

use Joomla\CMS\Factory;
use HYPERPC\Joomla\Router\Router;

/**
 * Class HyperPcRouter
 *
 * @since 2.0
 */
class HyperPcRouter extends Router
{
}

/**
 * Build component route.
 *
 * @param   array $query    Array query details.
 * @return  array
 *
 * @throws  Exception
 *
 * @since   2.0
 */
function  HyperPcBuildRoute(&$query)
{
    $app    = Factory::getApplication();
    $router = new HyperPcRouter($app, $app->getMenu());

    return $router->build($query);
}

/**
 * Parse component route.
 *
 * @param   array $segments     Array segments details.
 * @return  array
 *
 * @throws  Exception
 *
 * @since   2.0
 */
function  HyperPcParseRoute($segments)
{
    $app    = Factory::getApplication();
    $router = new HyperPcRouter($app, $app->getMenu());

    return $router->parse($segments);
}
