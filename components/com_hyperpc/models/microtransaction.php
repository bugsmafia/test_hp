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

use HYPERPC\Joomla\Model\ModelAdmin;
use HYPERPC\ORM\Entity\Microtransaction;

/**
 * Class HyperPcModelMicrotransaction
 *
 * @since   2.0
 */
class HyperPcModelMicrotransaction extends ModelAdmin
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
        $this->setHelper($this->hyper['helper']['microtransaction']);
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

        if (property_exists($item, 'total')) {
            $item->set('total', (float) $item->total->val());
        }

        unset($item->hyper);

        return $item;
    }
}
