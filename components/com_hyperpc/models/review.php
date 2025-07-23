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

use HYPERPC\Helper\ReviewHelper;
use HYPERPC\Joomla\Model\ModelAdmin;
use HYPERPC\Joomla\Model\Entity\Entity;

/**
 * Class HyperPcModelReview
 *
 * @property ReviewHelper   $_helper
 * @method   ReviewHelper   getHelper()
 *
 * @since    2.0
 */
class HyperPcModelReview extends ModelAdmin
{

    /**
     * Initialize model hook method.
     *
     * @param   array $config
     *
     * @return  void
     *
     * @since   2.0
     */
    public function initialize(array $config)
    {
        $this->setHelper($this->hyper['helper']['review']);
    }

    /**
     * Get review object by request id.
     *
     * @param   int $id
     *
     * @return  Entity
     *
     * @throws  \RuntimeException
     *
     * @since   2.0
     */
    public function getItem($id = null)
    {
        if ($id === null) {
            $id = $this->hyper['input']->get('id', 1);
        }

        return $this->_helper->findById($id, ['a.*']);
    }

    /**
     * Get table object.
     *
     * @param   string  $name
     * @param   string  $prefix
     * @param   array   $options
     *
     * @return  \Joomla\CMS\Table\Table|HyperPcTableReviews|JTable
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getTable($name = 'Reviews', $prefix = HP_TABLE_CLASS_PREFIX, $options = [])
    {
        return parent::getTable($name, $prefix, $options);
    }
}
