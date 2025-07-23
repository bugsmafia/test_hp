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

use HYPERPC\MoySklad\ApiClient;
use JMS\Serializer\Annotation\Type;
use HYPERPC\MoySklad\Http\RequestExecutor;
use MoySklad\Util\Exception\ApiClientException;

/**
 * Class MetaEntity
 *
 * @package HYPERPC\MoySklad\Entity
 *
 * @since   2.0
 */
class MetaEntity
{
    /**
     * @Type("string")
     */
    public $id;

    /**
     * @Type("string")
     */
    public $accountId;

    /**
     * @Type("string")
     */
    public $name;

    /**
     * @Type("HYPERPC\MoySklad\Entity\Meta")
     */
    protected $meta;

    /**
     * MetaEntity constructor
     *
     * @param Meta|null $meta
     */
    public function __construct(?Meta $meta = null)
    {
        if ($meta) {
            $this->meta = $meta;
        }
    }

    /**
     * @param ApiClient $api
     * @throws ApiClientException
     * @throws \Exception
     */
    public function fetch(ApiClient $api): void
    {
        if (empty($this->meta->href)) {
            throw new \Exception("The entity has not metadata.");
        }

        $fetched = RequestExecutor::url($api, $this->meta->href)->get(get_class($this));

        foreach ($this as $property => &$value) {
            $value = $fetched->$property;
        }
    }

    /**
     * @return Meta|null
     */
    public function getMeta(): ?Meta
    {
        return $this->meta;
    }
}
