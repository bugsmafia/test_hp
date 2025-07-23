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
 *
 * @todo add table initialize
 */

namespace HYPERPC\Joomla\View\Html\Data;

use HYPERPC\Container;

defined('_JEXEC') or die('Restricted access');

/**
 * Class HtmlData
 *
 * @package HYPERPC\Joomla\View\Html\Data
 *
 * @since   2.0
 */
abstract class HtmlData extends Container
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Render layout.
     *
     * @param   string  $layout
     * @param   array   $args
     *
     * @return  mixed
     *
     * @since   2.0
     */
    public function render($layout, $args = [])
    {
        $args['htmlData'] = $this;
        return $this->hyper['helper']['render']->render($layout, $args);
    }

    /**
     * Setup query conditions.
     *
     * @param   \JDatabaseQueryMysqli   $query
     * @param   array                   $conditions
     *
     * @return  \JDatabaseQueryMysqli
     *
     * @since   2.0
     */
    protected function _setConditions(\JDatabaseQueryMysqli $query, array $conditions = [])
    {
        if (count($conditions)) {
            foreach ($conditions as $condition) {
                $query->where($condition);
            }
        }

        return $query;
    }
}
