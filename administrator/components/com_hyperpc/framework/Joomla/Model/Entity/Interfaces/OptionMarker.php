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

use HYPERPC\Data\JSON;

/**
 * Interface OptionMarker
 *
 * @package HYPERPC\Joomla\Model\Entity\Interfaces
 *
 * @since   2.0
 */
interface OptionMarker extends Stockable, Priceable, Entity
{
    /**
     * Get option name for configurator.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getConfigurationName();

    /**
     * Get image assembled
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getImageAssembled();

    /**
     * Get item key
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getItemKey();

    /**
     * Get option page title with part.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getPageTitle();

    /**
     * Get option part object.
     *
     * @return  PartMarker
     *
     * @since   2.0
     */
    public function getPart();

    /**
     * Get sorting review array.
     *
     * @param   string $sorting
     * @param   string $order
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getReview($order = 'asc', $sorting = '{n}.sorting');

    /**
     * Get merged params
     *
     * @return  JSON
     *
     * @since   2.0
     */
    public function getParams();

    /**
     * Get short description
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getShortDescription();
}
