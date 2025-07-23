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

namespace HYPERPC\Event;

use Joomla\CMS\Cache\Cache;
use HYPERPC\ORM\Table\Table;
use Joomla\CMS\Filesystem\Path;
use HYPERPC\Helper\MoyskladFilterHelper;
use HyperPcTablePositions as PositionsTable;
use HYPERPC\Joomla\Model\Entity\MoyskladProduct;
use HyperPcTableMoysklad_Products as ProductsTable;

/**
 * Class MoyskladProductEventHandler
 *
 * @package HYPERPC\Event
 *
 * @since   2.0
 */
class MoyskladProductEventHandler extends Event
{

    /**
     * Global product on after save.
     *
     * @param   PositionsTable|ProductsTable $table
     * @param   bool $isNew
     *
     * @return  bool
     *
     * @throws  \Exception
     *
     * @todo    emit events in model with own context for position table
     *
     * @since   2.0
     */
    public static function onAfterSave($table, $isNew)
    {
        $app = self::getApp();
        if ($app['cms']->isClient('administrator') === true) {
            /** @var MoyskladProduct $product */
            $product = $app['helper']['moyskladProduct']->findById($table->id);

            /** @var MoyskladFilterHelper $filterHelper */
            $filterHelper = $app['helper']['moyskladFilter'];
            if (!empty($app['params']->get(MoyskladFilterHelper::PRODUCT_INDEX_FIELD)) && $product->isPublished()) {
                try {
                    Table::getInstance('Moysklad_Products_Index');
                } catch (\Exception $e) {
                    $filterHelper
                        ->dropTable()
                        ->createTable($filterHelper->getTableProps());
                }

                $filterHelper->updateProductIndex($product);
            }

            $cache = Cache::getInstance(null, [
                'defaultgroup' => $product->getCacheGroup(),
                'cachebase'    => Path::clean(JPATH_ROOT . '/cache', '/')
            ]);

            $cache->clean();

            return true;
        }

        return true;
    }

    /**
     * On before delete product.
     *
     * @param   ProductsTable  $table
     *
     * @return  void
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public static function onBeforeDelete(ProductsTable $table)
    {
        $app = self::getApp();
        if ($app['cms']->isClient('administrator') === true) {
            /** @var \HyperPcTableMoysklad_Products_Index $indexesTable */
            $indexesTable = Table::getInstance('Moysklad_Products_Index');
            $indexesTable->deleteByProductId($table->id);

            /** @var \HyperPcTableProducts_Config_Values $valuesTable */
            $valuesTable = Table::getInstance('Products_Config_Values');
            $valuesTable->deleteAllProductData($table->id);
        }
    }
}
