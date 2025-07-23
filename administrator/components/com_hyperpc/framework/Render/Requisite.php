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
 * Class Part
 *
 * @package     HYPERPC\Render
 * @property    \HYPERPC\Joomla\Model\Entity\Requisite $_entity
 *
 * @since       2.0
 */
class Requisite extends Render
{

    /**
     * Render requisites footer block info.
     *
     * @param string $tpl
     * @return string
     *
     * @since 2.0
     */
    public function footer($tpl = 'footer')
    {
        return $this->renderLayout(Str::low($tpl), [
            'requisite' => $this->_entity
        ]);
    }
}
