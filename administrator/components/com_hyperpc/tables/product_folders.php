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

use HYPERPC\ORM\Table\Nested;

/**
 * Class HyperPcTableProduct_Folders
 *
 * @since   2.0
 */
class HyperPcTableProduct_Folders extends Nested
{

    /**
     * Initialize hook table method.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function initialize()
    {
        $this->_tbl      = HP_TABLE_PRODUCT_FOLDERS;
        $this->_tbl_keys = HP_TABLE_PRIMARY_KEY;

        parent::initialize();

        $this->setName('Product_Folders');
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
        $this->alias = trim($this->alias);
        if ($this->alias === '') {
            $this->alias = $this->uuid;
        }

        return true;
    }
}
