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
use HYPERPC\Joomla\Model\Entity\PromoCode;

defined('_JEXEC') or die('Restricted access');

/**
 * Class HyperPcModelPromo_Code
 *
 * @since   2.0
 */
class HyperPcModelPromo_Code extends ModelAdmin
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
        return ['code', 'published', 'type', 'description', 'rate', 'limit', 'used', 'publish_up', 'publish_down', 'context', 'parts', 'products', 'positions'];
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
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  PromoCode
     *
     * @throws  \Exception
     * @throws  \RuntimeException
     *
     * @since   2.0
     */
    public function loadFormData()
    {
        /** @var PromoCode $item */
        $item = clone $this->getItem();

        if (property_exists($item, 'published')) {
            if (!$item->id) {
                $item->set('published', true);
            }

            $item->set('published', (int) $item->published);
        }

        return $item->getArray();
    }

    /**
     * Get table object.
     *
     * @param   string $type
     * @param   string $prefix
     * @param   array $config
     *
     * @return  \JTable
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getTable($type = 'Promo_Codes', $prefix = HP_TABLE_CLASS_PREFIX, $config = [])
    {
        return parent::getTable($type, $prefix, $config);
    }
}
