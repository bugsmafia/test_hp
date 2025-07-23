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

namespace HYPERPC\Elements;

use JBZoo\Utils\Str;
use HYPERPC\Joomla\Model\Entity\SaveConfiguration;
use HYPERPC\Joomla\Model\Entity\Interfaces\ProductMarker;

/**
 * Class ElementConfigurationHook
 *
 * @since   2.0
 */
abstract class ElementConfigurationHook extends ElementHook
{

    /**
     * Get configuration object.
     *
     * @return  SaveConfiguration
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function getConfiguration()
    {
        return $this->_config->find('data.configuration');
    }

    /**
     * Get configuration event context.
     *
     * @return  mixed
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function getContext()
    {
        return Str::low($this->_config->find('data.context'));
    }

    /**
     * Get product object.
     *
     * @return  ProductMarker
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function getProduct()
    {
        return $this->_config->find('data.form.product');
    }

    /**
     * Get form user email.
     *
     * @return  mixed
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function getUserEmail()
    {
        return $this->_config->find('data.form.email');
    }

    /**
     * Get form username.
     *
     * @return  mixed
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function getUsername()
    {
        return $this->_config->find('data.form.username');
    }

    /**
     * Get form phone.
     *
     * @return  mixed
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function getUserPhone()
    {
        return $this->_config->find('data.form.phone');
    }

    /**
     * Check context.
     *
     * @param   string $name
     *
     * @return  bool
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function isContext($name)
    {
        return ($this->getContext() === Str::low($name));
    }

    /**
     * Get message subject.
     *
     * @return  mixed
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    protected function _getSubject()
    {
        $configuration = $this->_prepareOrderMacrosData();
        $this->hyper['helper']['macros']->setData($configuration);
        return $this->hyper['helper']['macros']->text($this->_config->get('subject'));
    }

    /**
     * Prepare configuration data for macros.
     *
     * @return  SaveConfiguration
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    protected function _prepareOrderMacrosData()
    {
        $configuration = $this->getConfiguration();
        $configuration->set('name', $this->hyper['helper']['order']->getName($configuration->id));

        return $configuration;
    }
}
