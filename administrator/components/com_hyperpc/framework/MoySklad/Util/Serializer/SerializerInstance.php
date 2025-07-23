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

use MoySklad\Entity\Barcode;
use JMS\Serializer\Serializer;
use MoySklad\Entity\MetaEntity;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\Handler\HandlerRegistry;
use MoySklad\Util\Serializer\BarcodeDeserializeHandler;
use JMS\Serializer\Naming\IdenticalPropertyNamingStrategy;
use JMS\Serializer\Naming\SerializedNameAnnotationStrategy;
use MoySklad\Util\Serializer\SerializerInstance as BaseSerializerInstance;

/**
 * Class SerializerInstance
 *
 * @package HYPERPC\MoySklad\Util\Serializer
 *
 * @since   2.0
 */
class SerializerInstance extends BaseSerializerInstance
{
    private const DIRECTION = [
        'serialization' => 1,
        'deserialization' => 2,
    ];

    /**
     * @var Serializer
     */
    private static $instance = null;

    public static function getInstance(): Serializer
    {
        if (is_null(self::$instance)) {
            self::$instance = SerializerBuilder::create()
                ->setPropertyNamingStrategy(
                    new SerializedNameAnnotationStrategy(
                        new IdenticalPropertyNamingStrategy()
                    )
                )
                ->configureHandlers(
                    function (HandlerRegistry $registry) {
                        $registry->registerHandler(
                            self::DIRECTION['deserialization'],
                            MetaEntity::class,
                            'json',
                            new MetaEntityDeserializeHandler()
                        );
                        $registry->registerHandler(
                            self::DIRECTION['deserialization'],
                            Barcode::class,
                            'json',
                            new BarcodeDeserializeHandler()
                        );
                        $registry->registerHandler(
                            self::DIRECTION['deserialization'],
                            'attributeValue',
                            'json',
                            new AttributeValueDeserializeHandler()
                        );
                        $registry->registerHandler(
                            self::DIRECTION['serialization'],
                            'attributeValue',
                            'json',
                            new AttributeValueSerializeHandler()
                        );
                    }
                )
                ->addDefaultHandlers()
                ->build();
        }

        return self::$instance;
    }
}
