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
 * @author      Artem vyshnevskiy
 */

namespace HYPERPC\Joomla\Model\Entity\Interfaces;

/**
 * Interface Categorizable
 *
 * @package HYPERPC\Joomla\Model\Entity\Interfaces
 *
 * @since   2.0
 */
interface CategoryMarker extends YandexMarketInterface, Entity
{
    /**
     * Get product google category id
     *
     * @return int
     *
     * @since  2.0
     */
    public function getGoogleId();

    /**
     * Get item types
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getItemsType();

    /**
     * Get merged params
     *
     * @return  JSON
     *
     * @since   2.0
     */
    public function getParams();

    /**
     * Get parent id
     *
     * @return  int
     *
     * @since   2.0
     */
    public function getParentId();

    /**
     * Get parent folder
     *
     * @return  CategoryMarker
     *
     * @since   2.0
     */
    public function getParent();
}
