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

use Joomla\CMS\Table\Table;
use HYPERPC\Joomla\Model\ModelAdmin;

/**
 * Class HyperPcModelMoysklad_Store
 *
 * @since   2.0
 */
class HyperPcModelMoysklad_Store extends HyperPcModelProduct_Folder
{
    /**
     * Get table object.
     *
     * @param   string $type
     * @param   string $prefix
     * @param   array $config
     *
     * @return  Table
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public function getTable($type = 'Moysklad_Stores', $prefix = HP_TABLE_CLASS_PREFIX, $config = [])
    {
        return parent::getTable($type, $prefix, $config);
    }

    /**
     * Method to save the form data.
     *
     * @param   array $data
     *
     * @return  bool
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public function save($data)
    {
        return ModelAdmin::save($data);
    }

    /**
     * Get global fields for form render.
     *
     * @return  array
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public function getGlobalFields()
    {
        return [
            'parent_id',
            'published',
            'uuid',
            'geoid',
        ];
    }
}
