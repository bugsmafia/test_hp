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
 * @author      Roman Evsyukov
 * @author      Artem Vyshnevskiy
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Table\Table;
use HYPERPC\Helper\PositionHelper;
use HYPERPC\Joomla\Model\ModelAdmin;
use HYPERPC\Joomla\Model\Entity\Position;

/**
 * Class HyperPcModelPosition
 *
 * @property PositionHelper $_helper
 * @method   PositionHelper getHelper()
 *
 * @since    2.0
 */
class HyperPcModelPosition extends ModelAdmin
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
        $this->setHelper($this->hyper['helper']['position']);
    }

    /**
     * Get category object by request id.
     *
     * @param   int $id
     *
     * @return  Position
     *
     * @throws  RuntimeException
     * @throws  Exception
     *
     * @since   2.0
     */
    public function getItem($id = null)
    {
        if ($id === null) {
            $id = $this->hyper['input']->getInt('id', 1);
        }

        /** @var Position $position */
        $position = $this->_helper->findById($id);
        if (!$position->id) {
            return $position;
        }

        return $this->_helper->expandToSubtype($position);
    }

    /**
     * Get table object.
     *
     * @param   string $name
     * @param   string $prefix
     * @param   array $options
     * @return  Table|HyperPcTableParts
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public function getTable($name = 'Positions', $prefix = HP_TABLE_CLASS_PREFIX, $options = [])
    {
        return parent::getTable($name, $prefix, $options);
    }
}
