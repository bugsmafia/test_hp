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

use Joomla\CMS\Factory;
use HYPERPC\ORM\Table\Table;
use Joomla\CMS\Language\Text;

/**
 * Class HyperPcTableCompatibilities
 *
 * @property    string $id
 * @property    string $type
 * @property    string $name
 * @property    string $alias
 * @property    string $description
 * @property    string $published
 * @property    string $params
 * @property    string $modified_time
 * @property    string $modified_user_id
 * @property    string $created_time
 * @property    string $created_user_id
 *
 * @since       2.0
 */
class HyperPcTableCompatibilities extends Table
{

    /**
     * HyperPcTableCompatibilities constructor.
     *
     * @param   \JDatabaseDriver $db
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function __construct(\JDatabaseDriver $db)
    {
        parent::__construct(HP_TABLE_COMPATIBILITIES, HP_TABLE_PRIMARY_KEY, $db);
    }

    /**
     * Overloaded bind function.
     *
     * @param   array         $array   Named array
     * @param   array|string  $ignore  An optional array or space separated list of properties to ignore while binding.
     *
     * @return  boolean  True on success.
     *
     * @throws  \InvalidArgumentException
     */
    public function bind($array, $ignore = '')
    {
        if (!key_exists('description', $array)) {
            $array['description'] = '';
        }

        return parent::bind($array, $ignore);
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

        if (trim(str_replace('-', '', $this->alias)) === '') {
            $this->alias = Factory::getDate()->format('Y-m-d-H-i-s');
        }

        return true;
    }
}
