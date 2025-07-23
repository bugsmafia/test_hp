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

use HYPERPC\Data\JSON;
use HYPERPC\ORM\Table\Table;
use Joomla\Database\DatabaseDriver;

/**
 * Class HyperPcTableProduct_Folders_Translations
 */
class HyperPcTableProduct_Folders_Translations extends Table
{
    /**
     * HyperPcTableProduct_Folders_Translations constructor.
     *
     * @param   DatabaseDriver $db
     *
     * @throws  \Exception
     */
    public function __construct(DatabaseDriver $db)
    {
        parent::__construct(HP_TABLE_PRODUCT_FOLDERS_TRANSLATIONS, HP_TABLE_PRIMARY_KEY, $db);
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

        if (key_exists('translatable_params', $array)) {
            $params = new JSON($array['translatable_params']);
            $array['translatable_params'] = $params->write();
        } else {
            $array['translatable_params'] = '{}';
        }

        return parent::bind($array, $ignore);
    }
}
