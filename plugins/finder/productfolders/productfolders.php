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
 * @author      Roman Evsyukov
 */

use HYPERPC\Joomla\Model\Entity\Entity;
use HYPERPC\Joomla\FinderIndexer\Adapter as HyperPCFinderIndexerAdapter;

//  no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Smart Search adapter for HYPERPC product Folders.
 *
 * @since 2.0
 */
class PlgFinderProductFolders extends HyperPCFinderIndexerAdapter
{

    /**
     * Name of indexer node.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $_name = 'productfolder';

    /**
     * The title filed.
     *
     * @var    string
     *
     * @since  2.0
     */
    protected $title_field = 'title';

    /**
     * Build entity summary.
     *
     * @param   Entity $entity
     * @return  string
     *
     * @since   2.0
     */
    protected function _getEntitySummary(Entity $entity)
    {
        return $entity->description;
    }
}
