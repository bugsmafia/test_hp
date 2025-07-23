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

namespace HYPERPC\Object\Compatibility;

use HYPERPC\ORM\Entity\Compatibility;
use Spatie\DataTransferObject\DataTransferObject;

class CompatibilityData extends DataTransferObject
{
    /**
     * Compatibility type
     */
    public string $type;

    /**
     * Master group id
     */
    public int $leftGroup;

    /**
     * Master group field id
     */
    public int $leftField;

    /**
     * Slave group id
     */
    public int $rightGroup;

    /**
     * Slave group field id
     */
    public int $rightField;

    /**
     * Create from entity
     */
    public static function fromEntity(Compatibility $entity): self
    {
        return new self([
            'type'       => $entity->type,
            'leftGroup'  => $entity->getLeftGroupId(),
            'leftField'  => $entity->getLeftFieldId(),
            'rightGroup' => $entity->getRightGroupId(),
            'rightField' => $entity->getRightFieldId()
        ]);
    }
}
