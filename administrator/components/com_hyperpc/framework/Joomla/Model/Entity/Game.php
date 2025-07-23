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

namespace HYPERPC\Joomla\Model\Entity;

use JBZoo\Data\JSON;

/**
 * Class Game
 *
 * @package HYPERPC\Joomla\Model\Entity
 *
 * @since   2.0
 */
class Game extends Entity
{

    /**
     * Option id.
     *
     * @var     int
     *
     * @since   2.0
     */
    public $id;

    /**
     * Option name.
     *
     * @var     string
     *
     * @since   2.0
     */
    public $name;

    /**
     * Option name alias.
     *
     * @var     string
     *
     * @since   2.0
     */
    public $alias;

    /**
     * Show in table of fps status.
     *
     * @var     string
     *
     * @since   2.0
     */
    public $default_game;

    /**
     * Ordering.
     *
     * @var     int
     *
     * @since   2.0
     */
    public $ordering;

    /**
     * Option params.
     *
     * @var     JSON
     *
     * @since   2.0
     */
    public $params;

    /**
     * Published status.
     *
     * @var     bool
     *
     * @since   2.0
     */
    public $published;

    /**
     * Get site view game link.
     *
     * @param   array $query
     * @return  null
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getViewUrl(array $query = [])
    {
        return null;
    }
}
