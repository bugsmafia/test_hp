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

namespace HYPERPC\ORM\Entity\Traits;

use HYPERPC\Data\JSON;

defined('_JEXEC') or die('Restricted access');

/**
 * Trait AmoCrmLeadTrait
 *
 * @package     HYPERPC\ORM\Entity\Traits
 *
 * @property    JSON    $params
 *
 * @since       2.0
 */
trait AmoCrmLeadTrait
{

    /**
     * Set param key for get lead id value.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $_paramsKeyLeadId = 'amo_lead_id';

    /**
     * Get AmoCRM lead id.
     *
     * @return  int
     *
     * @since   2.0
     */
    public function getAmoLeadId()
    {
        if ($this->params instanceof JSON) {
            return $this->params->get($this->_paramsKeyLeadId, 0, 'int');
        }

        return 0;
    }

    /**
     * Get AmoCRM lead url.
     *
     * @return  string|null
     *
     * @since   2.0
     */
    public function getAmoLeadUrl()
    {
        $leadId = $this->getAmoLeadId();
        if ($leadId) {
            return $this->hyper['helper']['crm']->getLeadUrl($leadId);
        }

        return null;
    }
}
