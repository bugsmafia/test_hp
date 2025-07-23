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

namespace HYPERPC\Helper;

use HYPERPC\Container;

/**
 * Class AppHelper
 *
 * @package HYPERPC\Helper
 *
 * @since   2.0
 */
abstract class AppHelper extends Container
{

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
        $this->initialize();
    }

    /**
     * Initialize helper.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function initialize()
    {
    }
}
