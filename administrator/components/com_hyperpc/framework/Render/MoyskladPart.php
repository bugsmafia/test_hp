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

use Cake\Utility\Hash;
use HYPERPC\Helper\CartHelper;
use HYPERPC\Render\AbstractPart;
use HYPERPC\Joomla\Model\Entity\MoyskladVariant;

/**
 * Class Part
 *
 * @package     HYPERPC\Render
 * @property    \HYPERPC\Joomla\Model\Entity\MoyskladPart $_entity
 *
 * @since       2.0
 */
class MoyskladPart extends AbstractPart
{
    /**
     * Render part cart buttons.
     *
     * @param   string $tpl     Render layout.
     * @param   array $data
     * @return  null|string
     *
     * @todo check getItems method
     *
     * @since   2.0
     */
    public function getCartBtn($tpl = 'button', array $data = [])
    {
        $cart      = $this->hyper['helper']['cart'];
        $cartItems = $cart->getItems();

        $entityId = $this->_entity->id;
        $useDefaultOption = true;
        if (isset($data['option']) && $data['option'] instanceof MoyskladVariant) {
            $entityId .= '-' . $data['option']->id;
            $useDefaultOption = false;
        }

        $isInCart = (array_key_exists($cart->getItemKey($entityId, CartHelper::TYPE_POSITION), $cartItems));

        $data = Hash::merge([
            'isInCart'         => $isInCart,
            'items'            => $cartItems,
            'position'         => $this->_entity,
            'type'             => CartHelper::TYPE_POSITION,
            'useDefaultOption' => $useDefaultOption,
            'size'             => 'small'
        ], $data);

        return $this->hyper['helper']['render']->render('position/cart/' . $tpl, $data);
    }

    /**
     * Get entity image path.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getEntityImage()
    {
        return $this->_entity->images->get('image', '', 'hpimagepath');
    }

    /**
     * Get image path by param name
     *
     * @param   string $paramName
     *
     * @return  string
     *
     * @since   2.0
     */
    protected function _getImagePath(string $paramName)
    {
        return $this->_entity->images->get($paramName, '', 'hpimagepath');
    }
}
