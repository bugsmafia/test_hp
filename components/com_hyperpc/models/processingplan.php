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
 * Class HyperPcModelProcessingplan
 *
 * @since   2.0
 *
 * @todo    create abstract nested list item model
 */
class HyperPcModelProcessingplan extends ModelAdmin
{
    /**
     * Initialize model hook method.
     *
     * @param   array $config
     * @return  void
     *
     * @since   2.0
     */
    public function initialize(array $config)
    {
        $this->setHelper($this->hyper['helper']['processingplan']);
    }

    /**
     * Get table object.
     *
     * @param   string $type
     * @param   string $prefix
     * @param   array $config
     *
     * @return  Table
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getTable($type = 'Processingplans', $prefix = HP_TABLE_CLASS_PREFIX, $config = [])
    {
        return parent::getTable($type, $prefix, $config);
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  bool|Microtransaction
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function loadFormData()
    {
        /** @var Microtransaction */
        $item = clone $this->getItem();

        unset($item->hyper);

        return $item;
    }
}
