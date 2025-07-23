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
 * @author      Artem Vyshnevskiy
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use HYPERPC\Elements\Element;

/**
 * Class ElementOrderMethods
 *
 * @since   2.0
 */
class ElementOrderMethods extends Element
{

    /**
     * Load assets.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function loadAssets()
    {
        $this->hyper['helper']['assets']
            ->js('elements:' . $this->_group . '/' . $this->_type . '/assets/js/methods.js')
            ->widget('#field-' . $this->getIdentifier(), 'HyperPC.ElementOrderMethods');
    }

    /**
     * Get method title.
     *
     * @param   ?int $value
     *
     * @return  null|string
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function getMethodTitle(int $value = null)
    {
        $value   = $value !== null ? $value : (int) $this->getValue();
        $methods = $this->getMethods();

        if (key_exists($value, $methods)) {
            return Text::_('HYPER_ELEMENT_ORDER_METHOD_' . strtoupper($methods[$value]));
        }

        return null;
    }

    /**
     * Get CRM value.
     *
     * @return  mixed|null|string
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function getCrmValue()
    {
        return $this->getMethodTitle();
    }

    /**
     * Allowed methods.
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getMethods()
    {
        return (array) $this->_metaData->get('methods');
    }
}
