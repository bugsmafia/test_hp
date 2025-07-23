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
 * @author      Roman Evsyukov
 * @author      Artem Vyshnevskiy
 */

namespace HYPERPC\Render;

/**
 * Class Option
 *
 * @package     HYPERPC\Render
 * @property    \HYPERPC\Joomla\Model\Entity\MoyskladVariant $_entity
 *
 * @since       2.0
 */
class MoyskladVariant extends MoyskladPart
{
    /**
     * Get entity image path.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getEntityImage()
    {
        return $this->_getImagePath('image') ?: $this->_entity->getPart()->getRender()->getEntityImage();
    }
}
