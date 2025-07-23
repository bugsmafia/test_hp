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

namespace HYPERPC\MoySklad\Entity;

use JMS\Serializer\Annotation\Type;
use MoySklad\Entity\Meta as MetaBase;
use MoySklad\Util\Object\Annotation\Generator;

/**
 * Class Meta
 *
 * @package HYPERPC\MoySklad\Entity
 *
 * @since   2.0
 */
final class Meta
{
    private const TYPES = [
        'counterparty'   => Agent\Counterparty::class,

        'customerorder'  => Document\CustomerOrder::class,
        'loss'           => Document\Loss::class,
        'move'           => Document\Move::class,
        'processingplan' => Document\ProcessingPlan::class,

        'productfolder'  => \MoySklad\Entity\Product\ProductFolder::class,

        'bundle'         => Product\Bundle::class,
        'product'        => Product\Product::class,
        'service'        => Product\Service::class,
        'variant'        => \MoySklad\Entity\Variant::class,

        'store'          => \MoySklad\Entity\Store\Store::class,

        'webhook'        => WebHook::class,
    ];

    /**
     * @Type("string")
     * @Generator()
     */
    public $href;

    /**
     * @Type("string")
     * @Generator()
     */
    public $metadataHref;

    /**
     * @Type("string")
     * @Generator()
     */
    public $type;

    /**
     * @Type("string")
     * @Generator()
     */
    public $mediaType;

    /**
     * @Type("string")
     * @Generator()
     */
    public $uuidHref;

    /**
     * @Type("string")
     * @Generator()
     */
    public $downloadHref;

    /**
     * @Type("int")
     * @Generator()
     */
    public $size;

    /**
     * @Type("int")
     * @Generator()
     */
    public $limit;

    /**
     * @Type("int")
     * @Generator()
     */
    public $offset;

    /**
     * @param string $type
     * @return string
     */
    public static function getClassNameByType(string $type): string
    {
        if (!isset(self::TYPES[$type])) {
            throw new \InvalidArgumentException('Meta type unsupported');
        }

        return self::TYPES[$type];
    }

    /**
     * @return string
     */
    public function getClassName(): string
    {
        return static::getClassNameByType($this->type);
    }

    /**
     * id не всегда передаётся в объекты, зато всегда есть в ссылке на объект в мета-данных
     *
     * @return string
     */
    public function getId() : string
    {
        $href = explode('/', $this->href);
        $href = end($href);
        $href = explode('?', $href);
        return reset($href);
    }

    /**
     * @return  MetaBase
     */
    public function toBaseMeta(): MetaBase
    {
        $meta = new MetaBase();

        foreach (get_object_vars($this) as $prop => $value) {
            $meta->{$prop} = $value;
        }

        return $meta;
    }
}
