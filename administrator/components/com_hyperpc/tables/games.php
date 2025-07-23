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

use Joomla\CMS\Factory;
use HYPERPC\ORM\Table\Table;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Application\ApplicationHelper;

defined('_JEXEC') or die('Restricted access');

/**
 * Class HyperPcTableGames
 *
 * @property    string $id
 * @property    string $alias
 * @property    string $name
 * @property    string $params
 * @property    string $default_game
 *
 * @since       2.0
 */
class HyperPcTableGames extends Table
{

    /**
     * HyperPcTableOptions constructor.
     *
     * @param   JDatabaseDriver $db
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public function __construct(\JDatabaseDriver $db)
    {
        parent::__construct(HP_TABLE_GAMES, HP_TABLE_PRIMARY_KEY, $db);
    }

    /**
     * Override check function.
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function check()
    {
        if (trim($this->name) === '') {
            $this->setError(Text::_('JLIB_DATABASE_ERROR_MUSTCONTAIN_A_TITLE_CATEGORY'));
            return false;
        }

        $this->alias = trim($this->alias);
        if ($this->alias === '') {
            $this->alias = $this->name;
        }

        $this->alias = ApplicationHelper::stringURLSafe($this->alias);
        if (trim(str_replace('-', '', $this->alias)) === '') {
            $this->alias = Factory::getDate()->format('Y-m-d-H-i-s');
        }

        return true;
    }
}
