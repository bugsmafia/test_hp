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

namespace HYPERPC\Render;

use JBZoo\Utils\Str;

/**
 * Class Worker
 *
 * @package     HYPERPC\Render
 * @property    \HYPERPC\Joomla\Model\Entity\Worker $_entity
 *
 * @since       2.0
 */
class Worker extends Render
{

    /**
     * Render worker cart.
     *
     * @param   string $tpl
     * @return  string
     *
     * @since   2.0
     */
    public function card($tpl = 'default')
    {
        return $this->renderLayout('card/' . Str::low($tpl), [
            'worker'  => $this->_entity
        ]);
    }

    /**
     * Render module form.
     *
     * @return  null|string
     *
     * @since   2.0
     */
    public function form()
    {
        return $this->hyper['helper']['module']->renderById($this->_entity->params->get('form'));
    }
}
