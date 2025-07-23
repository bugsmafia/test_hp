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
 * Interface Entity
 *
 * @package HYPERPC\Joomla\Model\Entity\Interfaces
 *
 * @since   2.0
 */
interface Entity extends CMSObject
{
    /**
     * Bind entity data.
     *
     * @param   array $rowData
     *
     * @return  void
     *
     * @since   2.0
     */
    public function bindData($rowData);

    /**
     * Get array properties.
     *
     * @param   bool $public
     *
     * @return  mixed
     *
     * @since   2.0
     */
    public function getArray($public = true);

    /**
     * Get render object.
     *
     * @return  Render|null
     *
     * @since   2.0
     */
    public function getRender();

    /**
     * Get site view category url.
     *
     * @param   array $query
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getViewUrl(array $query = []);

    /**
     * Initialize entity.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function initialize();

    /**
     * Setup entity renderer.
     *
     * @param   ?string $name
     * @param   ?Entity $entity
     *
     * @return  $this
     *
     * @since   2.0
     */
    public function setRender($name = null, $entity = null);
}
