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

use Joomla\CMS\Language\Text;
use HYPERPC\Helper\MoySkladHelper;
use HYPERPC\Joomla\Controller\ControllerLegacy;

/**
 * Class HyperPcControllerMoysklad
 *
 * @property    MoySkladHelper $_helper
 *
 * @since       2.0
 */
class HyperPcControllerMoysklad extends ControllerLegacy
{

    /**
     * Hook on initialize controller.
     *
     * @param   array $config
     *
     * @return  void
     *
     * @since   2.0
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->_helper = $this->hyper['helper']['moysklad'];

        $this->registerTask('update_status_list', 'updateStatusList');
        $this->registerTask('update_characteristics', 'updateCharacteristics');
    }

    /**
     * Update Characteristic list from MoySklad.
     *
     * @return  void
     */
    public function updateCharacteristics()
    {
        $result = $this->_helper->updateCharacteristicList() && $this->_helper->updateAllCharacteristicValues();

        if ($result) {
            $this->hyper['cms']->enqueueMessage(Text::_('COM_HYPERPC_MOYSKLAD_CHARACTERISTICS_UPDATED_SUCCESSFULLY'));
        } else {
            $this->hyper['cms']->enqueueMessage(Text::_('COM_HYPERPC_MOYSKLAD_CHARACTERISTICS_UPDATE_FAILED'), 'error');
        }

        $redirectUrl = $this->hyper['route']->build(['view' => 'manager']);
        $this->hyper['cms']->redirect($redirectUrl);
    }

    /**
     * Update status list from MoySklad.
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function updateStatusList()
    {
        $redirectUrl = $this->hyper['route']->build(['view' => 'manager']);

        $result = $this->_helper->updateStatusList();

        if (!$result) {
            $this->hyper['cms']->enqueueMessage(Text::_('COM_HYPERPC_MOYSKLAD_STATUSES_UPDATE_FAILED'), 'error');
            $this->hyper['cms']->redirect($redirectUrl);
            return false;
        }

        $this->hyper['cms']->enqueueMessage(Text::_('COM_HYPERPC_MOYSKLAD_STATUSES_UPDATED_SUCCESSFULLY'));
        $this->hyper['cms']->redirect($redirectUrl);

        return true;
    }
}
