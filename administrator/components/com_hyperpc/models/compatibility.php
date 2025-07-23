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
 * @author      Sergey Kalistratov <kalistratov.s.m@gmail.com>
 */

use HYPERPC\Joomla\Model\ModelAdmin;

defined('_JEXEC') or die('Restricted access');

/**
 * Class HyperPcModelCompatibility
 *
 * @since   2.0
 */
class HyperPcModelCompatibility extends ModelAdmin
{

    /**
     * Initialize model hook method.
     *
     * @param   array $config
     *
     * @since   2.0
     */
    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->_autoAlias = true;
    }

    /**
     * Get global fields for form render.
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getGlobalFields()
    {
        return ['published', 'type'];
    }
}
