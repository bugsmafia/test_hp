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

namespace HYPERPC\Joomla\Model\Entity;

use JBZoo\Data\JSON;
use Cake\Utility\Hash;
use HYPERPC\Elements\Manager;
use HYPERPC\Elements\ElementProductService;
use HYPERPC\Render\MoyskladService as ServiceRender;

/**
 * Class MoyskladService
 *
 * @package     HYPERPC\Joomla\Model\Entity
 *
 * @property    ServiceRender $_render
 * @method      ServiceRender getRender()
 *
 * @since       2.0
 */
class MoyskladService extends Position
{
    /**
     * Virtual field for order.
     *
     * @var     int
     *
     * @since   2.0
     */
    public $quantity = 0;

    /**
     * Get advantages array
     *
     * @return array
     *
     * @since 2.0
     */
    public function getAdvantages()
    {
        $advantages = [];

        foreach ($this->getParams()->get('advantages', [], 'arr') as $item) {
            if (is_array($item) && key_exists('advantage', $item)) {
                $advantages[] = $item['advantage'];
            }
        }

        return $advantages;
    }

    /**
     * Get option name for configurator.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getConfigurationName()
    {
        $configuratorTitle = $this->getParams()->get('configurator_title');
        return (!empty($configuratorTitle)) ? $configuratorTitle : $this->name;
    }

    /**
     * Get part group.
     *
     * @return  ProductFolder
     *
     * @since   2.0
     */
    public function getGroup()
    {
        return $this->getFolder();
    }

    /**
     * Get parent product folder id
     *
     * @return  int
     *
     * @since   2.0
     */
    public function getGroupId(): int
    {
        return (int) $this->product_folder_id;
    }

    /**
     * Get sorting review array.
     *
     * @param   string $sorting
     * @param   string $order
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getReview($order = 'asc', $sorting = '{n}.sorting')
    {
        return Hash::sort($this->review->getArrayCopy(), $sorting, $order);
    }

    /**
     * Get part configurator name.
     *
     * @param   mixed $productId
     * @param   bool $considerOption not used. Only for compability with PartMarker method
     * @param   bool $considerQuantity
     * @return  mixed|string
     *
     * @since   2.0
     */
    public function getConfiguratorName($productId = null, $considerOption = false, $considerQuantity = false)
    {
        $reloadName       = $this->getParams()->get('reload_content_name');
        $productReloadIds = (array) $this->getParams()->get('reload_content_product_ids');

        if ($productId && in_array((string) $productId, $productReloadIds) && !empty($reloadName)) {
            if ($considerQuantity && $this->quantity > 1) {
                $reloadName = sprintf('%s x %s', $this->quantity, $reloadName);
            }
            return $reloadName;
        }

        $partName = $this->getConfigurationName();
        if ($considerQuantity && $this->quantity > 1) {
            $partName = sprintf('%s x %s', $this->quantity, $partName);
        }

        return $partName;
    }

    /**
     * Get service element.
     *
     * @return  ElementProductService|null
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getServiceElement()
    {
        static $elements = [];

        if ($this->params === null) {
            return null;
        }

        $serviceType = $this->params->get('service_type');

        if (!count($elements)) {
            $elements = Manager::getInstance()->getByPosition(Manager::ELEMENT_POS_PRODUCT_SERVICE);
        }

        /** @var ElementProductService $element */
        foreach ($elements as $element) {
            if ($element->getType() === $serviceType) {
                return $element;
            }
        }

        return null;
    }

    /**
     * Check is reload content form product by iu.
     *
     * @param   int $productId
     * @return  bool
     *
     * @since   2.0
     */
    public function isReloadContentForProduct($productId)
    {
        $productReloadIds = (array) $this->params->get('reload_content_product_ids');
        return in_array((string) $productId, $productReloadIds);
    }

    /**
     * Take out of the configuration?
     *
     * @return bool
     *
     * @since   2.0
     */
    public function isDetached()
    {
        $param = $this->params->get('remove_from_configuration', '-1', 'int');

        if ($param === -1) {
            return in_array((string) $this->product_folder_id, (array) $this->hyper['params']->get('external_parts', []));
        }

        return (bool) $param;
    }

    /**
     * Check if position can buy.
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function isForRetailSale()
    {
        return $this->getFolder()->isForRetailSale();
    }

    /**
     * Prepare folders. Add primary parent folder in list.
     *
     * @param   ProductFolder[] $folderList This list of group entity.
     * @return  array
     *
     * @since   2.0
     */
    public function prepareGroups(array $folderList = [])
    {
        $folders = [(string) $this->product_folder_id];
        if (array_key_exists($this->product_folder_id, $folderList)) {
            /** @var ProductFolder $folder */
            $folder = $folderList[$this->product_folder_id];
            if (!in_array((string) $folder->parent_id, $folders)) {
                array_push($folders, (string) $folder->parent_id);
            }
        }

        return $folders;
    }

    /**
     * Fields of JSON data.
     *
     * @return  array
     *
     * @since   2.0
     */
    protected function _getFieldJsonData()
    {
        $parentFields = parent::_getFieldJsonData();
        return array_merge(['review'], $parentFields);
    }
}
