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

namespace HYPERPC\MoySklad\Util\Param;

use MoySklad\Util\Param\Param;

class StockType extends Param
{
    protected const STOCK_TYPE_PARAM = 'stockType';

    /**
     * @var string
     */
    public $stockType;

    /**
     * @param string $stockType
     */
    private function __construct($stockType)
    {
        $this->stockType = $stockType;
        $this->type = self::STOCK_TYPE_PARAM;
    }

    /**
     * @param string $stockType
     * @return Offset
     */
    public static function eq($stockType): self
    {
        return new self($stockType);
    }

    /**
     * @return string
     */
    public function render(): string
    {
        return $this->stockType;
    }
}
