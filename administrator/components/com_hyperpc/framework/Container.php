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

namespace HYPERPC;

use Pimple\Container as PimpleContainer;

/**
 * Class Container
 *
 * @package HYPERPC
 *
 * @since   2.0
 */
class Container extends PimpleContainer
{

    /**
     * Instance of HyperPC application.
     *
     * @var         App
     *
     * @deprecated  Use new property hyper
     *
     * @since       2.0
     */
    public $app;

    /**
     * Instance of HyperPC application.
     *
     * @var     App
     *
     * @since   2.0
     */
    public $hyper;

    /**
     * Container constructor.
     *
     * @param   array $values
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function __construct(array $values = [])
    {
        parent::__construct($values);
        $this->app   = App::getInstance();
        $this->hyper = App::getInstance();
    }
}
