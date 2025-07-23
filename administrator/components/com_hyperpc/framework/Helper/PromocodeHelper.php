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

use JBZoo\Data\Data;
use HYPERPC\ORM\Table\Table;
use HYPERPC\Helper\Context\EntityContext;
use HYPERPC\Joomla\Model\Entity\PromoCode;

/**
 * Class PromocodeHelper
 *
 * @package HYPERPC\Helper
 * 
 * @since   2.0
 */
class PromocodeHelper extends EntityContext
{

    const SESSION_NAMESPACE = 'hp-promo-codes';
    const SESSION_ITEMS_KEY = 'items';

    /**
     * Session helper object.
     *
     * @var     SessionHelper
     *
     * @since   2.0
     */
    protected $_session;

    /**
     * Initialize helper.
     *
     * @return  void
     * 
     * @since   2.0
     */
    public function initialize()
    {
        $table = Table::getInstance('Promo_Codes');
        $this->setTable($table);

        $this->_session = clone $this->hyper['helper']['session'];
        $this->_session->setNamespace(self::SESSION_NAMESPACE);

        parent::initialize();
    }

    /**
     * Get session data.
     *
     * @return  Data
     *
     * @since   2.0
     */
    public function getSessionData()
    {
        static $sessionData;
        if (!$sessionData) {
            $session = $this->_session->get();
            $data = new Data((array) $session->get(self::SESSION_ITEMS_KEY));

            $promoCode = $this->hyper['helper']['promocode']->findByCode($data->get('code'));
            if ($promoCode->id) {
                $data->set('type', $promoCode->type);
                $data->set('rate', ($promoCode->type == PromoCode::TYPE_GIFT) ? 100 : (int) $promoCode->rate);
            }

            $sessionData = $data;
        }

        return $sessionData;
    }

    /**
     * Clear session data.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function clearSessionData()
    {
        $this->setSessionData([]);
    }

    /**
     * Set session data.
     *
     * @param   array $data
     * @return  void
     *
     * @since   2.0
     */
    public function setSessionData(array $data)
    {
        $this->_session->set(self::SESSION_ITEMS_KEY, $data);
    }
}

