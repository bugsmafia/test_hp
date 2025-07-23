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
use HYPERPC\MoySklad\Entity\MetaEntity;

/**
 * Class AttributeValueDeserializeHandler
 *
 * @package HYPERPC\MoySklad\Util\Serializer
 *
 * @since   2.0
 */
class AttributeValueDeserializeHandler
{
    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @param $visitor
     * @param mixed $value
     * @param array $type
     * @return array|mixed
     */
    public function __invoke($visitor, $value, array $type)
    {
        $this->serializer = SerializerInstance::getInstance();

        $serializeType = gettype($value);
        if (is_array($value)) {
            $serializeType = MetaEntity::class;
        }

        return $this->serializer->deserialize(json_encode($value), $serializeType, 'json');
    }
}
