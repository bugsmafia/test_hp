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
 * @author      Artem Vyshnevskiy
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use HYPERPC\Elements\Manager;
use HYPERPC\Joomla\Model\Entity\Order;

JLoader::register('ElementCreditFinboxCredit', JPATH_ROOT . '/media/hyperpc/elements/credit/finboxcredit/finboxcredit.php');

/**
 * Class ElementCreditFinboxInstallment
 */
class ElementCreditFinboxInstallment extends \ElementCreditFinboxCredit
{
    const PARAM_KEY = 'finboxinstallment';

    /**
     * Load element language.
     *
     * @return  void
     *
     * @since   2.0
     */
    protected function _loadLang()
    {
        parent::_loadLang();
        Factory::getApplication()->getLanguage()->load(
            'el_' . $this->getGroup() . '_finboxcredit',
            $this->hyper['path']->get('elements:' . $this->_group . '/finboxcredit')
        );
    }

    /**
     * Get data array for order create request
     *
     * @param   Order $order
     *
     * @return  array
     *
     * @throws  \Exception
     */
    protected function _getOrderData(Order $order)
    {
        $data = parent::_getOrderData($order);

        $data['creditTypes'] = ['installment'];
        $data['maxDiscount'] = (float) $this->getConfig('max_discount', 1.0);

        return $data;
    }

    /**
     * Get parent manifest params
     *
     * @return  array
     */
    protected function getParentManifestParams()
    {
        $parentManifestPath = $this->hyper['path']->get('elements:' . $this->_group . '/finboxcredit') . '/' . Manager::ELEMENT_MANIFEST_FILE;
        $parentManifest = include $parentManifestPath;
        $params = $parentManifest['params'] ?? [];
        return $params;
    }
}
