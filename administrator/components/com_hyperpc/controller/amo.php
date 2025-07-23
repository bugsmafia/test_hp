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

defined('_JEXEC') or die('Restricted access');

use HYPERPC\Data\JSON;
use Joomla\Filesystem\File;
use HYPERPC\Helper\CrmHelper;
use Joomla\CMS\Language\Text;
use HYPERPC\Joomla\Controller\ControllerLegacy;

/**
 * Class HyperPcControllerAmo
 *
 * @property    CrmHelper $_helper
 *
 * @since       2.0
 */
class HyperPcControllerAmo extends ControllerLegacy
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

        $this->_helper = $this->hyper['helper']['crm'];

        $this->registerTask('update_pipelines_list', 'updatePipelinesList');
    }

    /**
     * Update AmoCRM pipelines data.
     *
     * @return  bool
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function updatePipelinesList()
    {
        $redirectUrl = $this->hyper['route']->build(['view' => 'manager']);

        $body = $this->_helper->getLeadsPipelines();
        if ($body->find('response.error')) {
            $this->hyper['cms']->enqueueMessage($body->find('response.error'), 'error');
            $this->hyper['cms']->redirect($redirectUrl);
            return false;
        }

        $pipelines = (array) $body->find('_embedded.items');
        if (!count($pipelines)) {
            $this->hyper['cms']->enqueueMessage(Text::_('COM_HYPERPC_AMO_ERROR_NOT_FIND_PIPELINES'), 'error');
            $this->hyper['cms']->redirect($redirectUrl);
            return false;
        }

        $buffer = (new JSON($pipelines))->write();

        File::write($this->_helper->getTmpPipelineFilePath(), $buffer);

        $this->hyper['cms']->enqueueMessage(Text::_('COM_HYPERPC_AMO_SUCCESS_UPDATE_PIPELINES'));
        $this->hyper['cms']->redirect($redirectUrl);

        return true;
    }
}
