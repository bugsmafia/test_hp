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
use HYPERPC\Joomla\Model\Entity\Game;

defined('_JEXEC') or die('Restricted access');

/**
 * Class HyperPcModelGame
 *
 * @since   2.0
 */
class HyperPcModelGame extends ModelAdmin
{

    /**
     * Get global fields for form render.
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getGlobalFields()
    {
        return ['published', 'default_game'];
    }

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
     * Method to get the data that should be injected in the form.
     *
     * @return  Game
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function loadFormData()
    {
        /** @var Game $item */
        $item = clone $this->getItem();

        if (property_exists($item, 'published')) {
            $item->set('published', (int) $item->published);
        }

        return $item;
    }
}
