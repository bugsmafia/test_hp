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
use HYPERPC\ORM\Entity\Compatibility;
use HYPERPC\Helper\Context\EntityContext;

/**
 * Class CompatibilityHelper
 *
 * @package HYPERPC\Helper
 *
 * @since   2.0
 */
class CompatibilityHelper extends EntityContext
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
        $table = Table::getInstance('Compatibilities');
        $this->setTable($table);

        parent::initialize();
    }

    /**
     * Get all published compatibilities
     *
     * @return  Compatibility[]
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getPublished()
    {
        $db = $this->getDbo();

        return $this->findAll([
            'conditions' => [
                $db->quoteName('a.published') . ' = ' . $db->quote(HP_STATUS_PUBLISHED)
            ]
        ]);
    }
}
