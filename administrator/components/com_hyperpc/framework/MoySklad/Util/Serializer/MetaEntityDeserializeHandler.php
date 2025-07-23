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

namespace HYPERPC\MoySklad\Util\Serializer;

use JMS\Serializer\Serializer;
use HYPERPC\MoySklad\Entity\Meta;

/**
 * Class MetaEntityDeserializeHandler
 *
 * @package HYPERPC\MoySklad\Util\Serializer
 *
 * @since   2.0
 */
class MetaEntityDeserializeHandler
{
    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @param $visitor
     * @param array $metaEntity
     * @param array $type
     * @return array|mixed
     */
    public function __invoke($visitor, array $metaEntity, array $type)
    {
        $this->serializer = SerializerInstance::getInstance();

        try {
            $className = Meta::getClassNameByType($metaEntity['meta']['type']);
        } catch (\InvalidArgumentException $exception) {
            //@todo log it
            return $metaEntity;
        }

        return $this->serializer->deserialize(json_encode($metaEntity), $className, 'json');
    }
}
