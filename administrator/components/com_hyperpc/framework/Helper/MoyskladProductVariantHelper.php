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

namespace HYPERPC\Helper;

use HYPERPC\ORM\Table\Table;
use HYPERPC\Helper\Context\EntityContext;

/**
 * Class MoyskladProductVariantHelper
 *
 * @package     HYPERPC\Helper
 *
 * @property    \HyperPcTableMoysklad_Product_Variants $_table
 *
 * @since       2.0
 */
class MoyskladProductVariantHelper extends EntityContext
{

    /**
     * Initialize helper.
     *
     * @return  void
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function initialize()
    {
        $table = Table::getInstance('Moysklad_Product_Variants');
        $this->setTable($table);

        parent::initialize();
    }
}
