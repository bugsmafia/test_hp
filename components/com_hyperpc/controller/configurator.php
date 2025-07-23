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

use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use HYPERPC\Helper\ConfiguratorHelper;
use HYPERPC\Joomla\Controller\ControllerLegacy;
use HYPERPC\Joomla\Model\Entity\SaveConfiguration;

/**
 * Class HyperPcControllerConfigurator
 *
 * @since 2.0
 */
class HyperPcControllerConfigurator extends ControllerLegacy
{
    /**
     * Hold configurator helper object.
     *
     * @var     ConfiguratorHelper
     *
     * @since   2.0
     */
    protected $_helper;

    /**
     * Hook on initialize controller.
     *
     * @param   array $config
     * @return  void
     *
     * @since   2.0
     *
     * @SuppressWarnings("unused")
     */
    public function initialize(array $config)
    {
        $this->registerTask('find_configuration', 'findConfiguration');

        $this->_helper = $this->hyper['helper']['configurator'];
    }

    /**
     * Find configuration by id.
     *
     * @return  bool
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public function findConfiguration()
    {
        $id = $this->hyper['input']->get('configuration_id');

        /** @var SaveConfiguration $configuration */
        $configuration = $this->hyper['helper']['configuration']->getById($id);

        if (!$configuration->id) {
            $this->hyper['cms']->enqueueMessage(Text::_('COM_HYPERPC_CONFIGURATION_NOT_EXIST'), 'error');
            $this->hyper['cms']->redirect(Uri::base());
            return false;
        }

        $this->hyper['cms']->redirect($configuration->getViewUrl());
        return true;
    }
}
