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

namespace HYPERPC\Helper;

use HYPERPC\ORM\Table\Table;
use HYPERPC\Helper\Context\EntityContext;

/**
 * Class LeadHelper
 *
 * @package HYPERPC\Helper
 * 
 * @since   2.0
 */
class LeadHelper extends EntityContext
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
        $table = Table::getInstance('Leads');
        $this->setTable($table);

        parent::initialize();
    }
}
