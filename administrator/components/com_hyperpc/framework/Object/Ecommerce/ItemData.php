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

namespace HYPERPC\Object\Ecommerce;

use Spatie\DataTransferObject\DataTransferObject;

class ItemData extends DataTransferObject
{
    /**
     * Item name.
     */
    public string $name;

    /**
     * Item id.
     */
    public string $id;

    /**
     * Item price.
     */
    public float $price;

    /**
     * Currency iso code.
     */
    public string $currency;

    /**
     * Vendor or brand.
     */
    public ?string $brand;

    /**
     * List of item categories.
     *
     * @var string[]
     */
    public array $categories;

    /**
     * Item type.
     */
    public string $type;

    /**
     * List index.
     */
    public int $index = 1;

    /**
     * Item quantity.
     */
    public int $quantity = 1;

    /**
     * Item list name.
     * If associated with a list selection
     */
    public ?string $list_name;

    /**
     * Item list id.
     * If associated with a list selection
     */
    public ?string $list_id;

    /**
     * Return an array in Universal Analytics (UA) format
     */
    public function toArrayUA(): array
    {
        $result = [
            'name'      => $this->name,
            'id'        => $this->id,
            'price'     => $this->price,
            'category'  => empty($this->categories) ? null : $this->categories[array_key_first($this->categories)],
            'currency'  => $this->currency,
            'quantity'  => $this->quantity,
            'position'  => $this->index,
            'type'      => $this->type,
            'list'      => $this->list_name
        ];

        $result = array_filter($result, function ($propValue) {
            return $propValue !== null;
        });

        return $result;
    }

    /**
     * Return an array in Google Analytics 4 (GA4) format
     */
    public function toArrayGA4(): array
    {
        $result = [
            'item_name'         => $this->name,
            'item_id'           => $this->id,
            'brand'             => $this->brand,
            'price'             => $this->price,
            'item_list_name'    => $this->list_name,
            'item_list_id'      => $this->list_id,
            'index'             => $this->index,
            'quantity'          => $this->quantity
        ];

        $i = 1;
        foreach (array_reverse($this->categories) as $category) {
            $propKey = 'item_category' . ($i > 1 ? $i : '');
            $result[$propKey] = $category;
            $i++;
        }

        $result = array_filter($result, function ($propValue) {
            return $propValue !== null;
        });

        return $result;
    }
}
