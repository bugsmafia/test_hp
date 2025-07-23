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

namespace HYPERPC\Object\Order;

use HYPERPC\App;
use HYPERPC\ORM\Entity\MoyskladProductVariant;
use HYPERPC\Joomla\Model\Entity\MoyskladVariant;
use Spatie\DataTransferObject\DataTransferObject;
use HYPERPC\MoySklad\Entity\Document\Position\CustomerOrderDocumentPosition;

class PositionData extends DataTransferObject
{
    /**
     * Position id
     */
    public int $id;

    /**
     * Variant id
     */
    public ?int $option_id;

    /**
     * Position name
     */
    public string $name;

    /**
     * Position price
     */
    public float $price;

    /**
     * Discount in percent
     */
    public float $discount = 0.0;

    /**
     * Effective vat
     */
    public int $vat;

    /**
     * Position quantity
     */
    public int $quantity = 1;

    /**
     * Assortment type (product|service|variant|productvariant)
     */
    public string $type;

    /**
     * Class constructor
     */
    public function __construct(array $parameters = [])
    {
        $parameters['discount'] = (float) ($parameters['discount'] ?? 0.0);
        $parameters['price'] = (float) ($parameters['price'] ?? 0.0);
        parent::__construct($parameters);
    }

    /**
     * Data from Moysklad CustomerOrder position
     */
    public static function fromMoyskladOrderPosition(CustomerOrderDocumentPosition $orderPosition): self
    {
        $option_id = null;
        $meta = $orderPosition->assortment->getMeta();
        $type = $meta->type;
        if (in_array($type, ['variant', 'product'])) {
            $hp = App::getInstance();

            $assortmentUuid = $meta->getId();
            // try to find moysklad product variant by uuid
            /** @var MoyskladProductVariant $productVariant */
            $productVariant = $hp['helper']['moyskladProductVariant']->findBy('uuid', $assortmentUuid);
            $id = (int) $productVariant->product_id;
            if (!empty($id)) {
                $option_id = $productVariant->id;
                $type = 'productvariant';
            } elseif ($type === 'variant') { // productVariant not found by uuid and type is variant
                // try to find moysklad variant by uuid
                /** @var MoyskladVariant $option */
                $option = $hp['helper']['moyskladVariant']->findBy('uuid', $assortmentUuid);
                $id = (int) $option->part_id;
                $option_id = $option->id;
            }
        }

        if (empty($id)) {
            $id = (int) $orderPosition->assortment->externalCode;
        }

        return new self([
            'id' => $id,
            'option_id' => $option_id,
            'name' => $orderPosition->assortment->name,
            'price' => (float) $orderPosition->price / 100,
            'discount' => (float) $orderPosition->discount,
            'vat' => $orderPosition->vat,
            'quantity' => $orderPosition->quantity,
            'type' => $type
        ]);
    }
}
