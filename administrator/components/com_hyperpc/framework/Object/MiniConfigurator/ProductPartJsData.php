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

namespace HYPERPC\Object\MiniConfigurator;

use Spatie\DataTransferObject\DataTransferObject;

class ProductPartJsData extends DataTransferObject
{
    /**
     * Part option id
     */
    public int $option_id = 0;

    /**
     * Part id
     */
    public int $part_id;

    /**
     * Part product folder or group id
     */
    public int $folder_id;

    /**
     * Part name
     */
    public string $name;

    /**
     * Part description
     */
    public string $desc;

    /**
     * Part change url
     */
    public string $url_change;

    /**
     * Part view url
     */
    public string $url_view;

    /**
     * Part image
     */
    public string $image;

    /**
     * Part advantages
     */
    public array $advantages;
}
