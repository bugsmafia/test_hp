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

use Exception;
use JModelLegacy;
use HYPERPC\Data\JSON;
use Joomla\CMS\Date\Date;
use HYPERPC\Money\Type\Money;
use Joomla\CMS\Language\Text;
use HyperPcModelMoysklad_Part;
use HyperPcModelMoysklad_Service;
use HyperPcModelMoysklad_Product;
use HYPERPC\Render\Position as PositionRender;
use HYPERPC\Object\Position\BarcodeDataCollection;
use HYPERPC\Joomla\Model\Entity\Traits\PriceTrait;
use HYPERPC\Joomla\Model\Entity\Interfaces\Priceable;
use HYPERPC\Joomla\Model\Entity\Interfaces\Categorizable;

/**
 * Class Position
 *
 * @property    PositionRender $_render
 * @method      PositionRender getRender()
 *
 * @package     HYPERPC\Joomla\Model\Entity
 *
 * @since       2.0
 */
class Position extends Entity implements Categorizable, Priceable
{
    use PriceTrait;

    /**
     * Position alias.
     *
     * @var     string
     *
     * @since   2.0
     */
    public $alias;

    /**
     * Barcodes.
     *
     * @var     JSON
     *
     * @since   2.0
     */
    public $barcodes;

    /**
     * Virtual field for com_fields. Alias to product_folder_id.
     *
     * @var     string
     *
     * @since   2.0
     */
    public $catid;

    /**
     * Created datetime.
     *
     * @var     Date
     *
     * @since   2.0
     */
    public $created_time;

    /**
     * Created user.
     *
     * @var     int
     *
     * @since   2.0
     */
    public $created_user_id;

    /**
     * Full description.
     *
     * @var     string
     *
     * @since   2.0
     */
    public $description;

    /**
     * Primary key.
     *
     * @var     int
     *
     * @since   2.0
     */
    public $id;

    /**
     * Position images.
     *
     * @var     JSON
     *
     * @since   2.0
     */
    public $images;

    /**
     * List price object.
     *
     * @var     Money
     *
     * @since   2.0
     */
    public $list_price = 0.00;

    /**
     * Position meta data.
     *
     * @var     JSON
     *
     * @since   2.0
     */
    public $metadata;

    /**
     * Modified datetime.
     *
     * @var     Date
     *
     * @since   2.0
     */
    public $modified_time;

    /**
     * Modified user id.
     *
     * @var     int
     *
     * @since   2.0
     */
    public $modified_user_id;

    /**
     * Position name.
     *
     * @var     string
     *
     * @since   2.0
     */
    public $name;

    /**
     * Position ordering.
     *
     * @var     int
     *
     * @since   2.0
     */
    public $ordering = 10;

    /**
     * Position params.
     *
     * @var     JSON
     *
     * @since   2.0
     */
    public $params;

    /**
     * Product folder id.
     *
     * @var     int
     *
     * @since   2.0
     */
    public $product_folder_id;

    /**
     * Review tabs.
     *
     * @var     JSON
     *
     * @since   2.0
     */
    public $review;

    /**
     * Sale price object.
     *
     * @var     Money
     *
     * @since   2.0
     */
    public $sale_price = 0.00;

    /**
     * Published status.
     *
     * @var     int
     *
     * @since   2.0
     */
    public $state = 0;

    /**
     * Position translatable params.
     *
     * @var     JSON
     *
     * @since   2.0
     */
    public $translatable_params;

    /**
     * Position type id.
     *
     * @var     int
     *
     * @since   2.0
     */
    public $type_id = 1;

    /**
     * Position uuid.
     *
     * @var     string
     *
     * @since   2.0
     */
    public $uuid;

    /**
     * Vat.
     *
     * @var     int
     *
     * @since   2.0
     */
    public $vat;

    /**
     * Initialize entity.
     *
     * @return  void
     *
     * @since   2.0
     *
     * @todo remove after discounts are done
     */
    public function initialize()
    {
        parent::initialize();

        $this->sale_price = ($this->list_price instanceof Money ? clone $this->list_price : $this->list_price);
    }

    /**
     * Get position's barcodes
     *
     * @return  BarcodeDataCollection
     *
     * @since   2.0
     */
    public function getBarcodes()
    {
        return BarcodeDataCollection::fromPositionBarcodes($this->barcodes->getArrayCopy());
    }

    /**
     * Get position's barcodes by type
     *
     * @param   string $type
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getBarcodesByType(string $type)
    {
        $barcodes = $this->getBarcodes();
        $data     = [];

        foreach ($barcodes as $barcodeData) {
            if ($barcodeData->type === $type) {
                $data[] = $barcodeData->value;
            }
        }

        return $data;
    }

    /**
     * Get EAN13 barcodes
     *
     * @return  array of EAN13 position's barcodes
     *
     * @since   2.0
     */
    public function getEan13()
    {
        return $this->getBarcodesByType('ean13');
    }

    /**
     * Get edit url.
     *
     * @param   bool $byFolder
     * @return  string
     *
     * @since   2.0
     */
    public function getEditUrl($byFolder = true)
    {
        $query = [
            'view'   => 'moysklad_' . $this->getType(),
            'layout' => 'edit',
            'id'     => $this->id
        ];

        if ($byFolder) {
            $query['product_folder_id'] = $this->product_folder_id;
        }

        return $this->hyper['helper']['route']->url($query);
    }

    /**
     * Get item key
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getItemKey()
    {
        return 'position-' . $this->id;
    }

    /**
     * Get service field value by field id
     *
     * @param   int $fieldId
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getFieldValueById($fieldId)
    {
        if (!$fieldId) {
            return '';
        }

        if (!empty($this->fields)) {
            foreach ($this->fields as $field) {
                if ($field->id === (int) $fieldId) {
                    break;
                }
            }
        } else {
            $db = $this->hyper['db'];

            $query = $db
                ->getQuery(true)
                ->select([
                    'v.*', 'f.*'
                ])
                ->from(
                    $db->quoteName('#__fields_values', 'v')
                )
                ->join(
                    'LEFT',
                    $db->quoteName('#__fields', 'f') . ' ON v.field_id = f.id'
                )
                ->where([
                    $db->quoteName('v.field_id') . ' = ' . $db->quote($fieldId),
                    $db->quoteName('v.item_id')  . ' = ' . $db->quote($this->id),
                ]);

            $data = $db->setQuery($query)->loadAssoc();
            if ($data === null) {
                return '';
            }

            $field = $this->hyper['helper']['object']->create(
                $data,
                Field::class
            );
        }

        return $field->getValue();
    }

    /**
     * Get position discount
     *
     * @return  float
     *
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     *
     * @todo    Move to PriceTrait
     */
    public function getDiscount()
    {
        $discount  = 0;
        $listPrice = max($this->list_price->val(), 0.0);
        $salePrice = min($listPrice, max($this->sale_price->val(), 0.0));

        if ($listPrice !== 0.0 && $salePrice === 0.0) {
            return 100;
        }

        if ($salePrice < $listPrice) {
            $discountAbs = $listPrice - $salePrice;
            $discount    = ($discountAbs / $listPrice) * 100;
        }

        return $discount;
    }

    /**
     * Get service folder.
     *
     * @return  ProductFolder
     *
     * @since   2.0
     */
    public function getFolder()
    {
        return $this->hyper['helper']['productFolder']->findById($this->product_folder_id);
    }

    /**
     * Get product folder id .
     *
     * @return  int
     *
     * @since   2.0
     */
    public function getFolderId()
    {
        return $this->product_folder_id;
    }

    /**
     * Get position name
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get page title.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getPageTitle()
    {
        return (!empty($this->getParams()->get('title'))) ? $this->getParams()->get('title') : $this->name;
    }

    /**
     * Get type alias
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getType()
    {
        $db = $this->hyper['db'];

        $query = $db->getQuery(true)
            ->select('a.alias')
            ->from($db->quoteName('#__hp_position_types', 'a'))
            ->where($db->quoteName('a.id') . ' = ' . $this->type_id);

        $db->setQuery($query);

        return $db->loadResult();
    }

    /**
     * Get type name
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getTypeName()
    {
        return Text::_('COM_HYPERPC_MOYSKLAD_POSITION_TYPE_' . strtoupper($this->getType()));
    }

    /**
     * Get site view url.
     *
     * @param   array $query
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getViewUrl(array $query = [], $isFull = false)
    {
        $defaultQuery = [
            'view'     => 'moysklad_' . $this->getType(),
            'id'       => $this->id,
            'product_folder_id' => $this->product_folder_id,
        ];

        $args = new JSON(array_replace($query, $defaultQuery));

        if ($args->get('opt', false, 'bool')) {
            if ($this->option instanceof MoyskladVariant && !empty($this->option->id)) {
                $args->set('view', 'moysklad_variant');
                $args->set('id', $this->option->id);
                $args->set('part_id', $this->id);
            }
            $args->offsetUnset('opt');
        }

        return $this->hyper['route']->build($args->getArrayCopy(), $isFull);
    }

    /**
     * Get item main image
     *
     * @var     int $imageMaxWidth
     * @var     int $imageMaxHeight
     *
     * @return  array
     *
     * @throws  \JBZoo\Image\Exception
     *
     * @since   2.0
     */
    public function getItemImage($imageMaxWidth = 0, $imageMaxHeight = 0)
    {
        $render = $this->getRender();

        if (isset($this->option) && $this->option instanceof MoyskladVariant && $this->option->id && !empty($this->option->images->get('image', '', 'hpimagepath'))) {
            $render = $this->option->getRender();
        }

        return $render->image($imageMaxWidth, $imageMaxHeight);
    }

    /**
     * Get merged params
     *
     * @return  JSON
     *
     * @since   2.0
     */
    public function getParams()
    {
        static $result = [];

        if (!key_exists($this->id, $result)) {
            $params = $this->params->getArrayCopy();

            $translatableParams = $this->translatable_params?->getArrayCopy();

            $result[$this->id] = new JSON(array_merge($params, (array) $translatableParams));
        }

        return $result[$this->id];
    }

    /**
     * Get part entity from position
     *
     * @return  MoyskladPart|bool
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public function getPart()
    {
        if (!$this->isPart()) {
            return false;
        }

        /** @var HyperPcModelMoysklad_Part $model */
        $model = JModelLegacy::getInstance('Moysklad_Part', HP_MODEL_CLASS_PREFIX);

        /** @var $part MoyskladPart */
        $part = $model->getItem($this->id);

        return $part->id > 0 ? $part : false;
    }

    /**
     * Get service entity from position
     *
     * @return  MoyskladService|bool
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public function getService()
    {
        if (!$this->isService()) {
            return false;
        }

        /** @var HyperPcModelMoysklad_Service $model */
        $model = JModelLegacy::getInstance('Moysklad_Service', HP_MODEL_CLASS_PREFIX);

        /** @var $part MoyskladService */
        $service = $model->getItem($this->id);

        return $service->id > 0 ? $service : false;
    }

    /**
     * Get product entity from position
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public function getProduct()
    {
        if (!$this->isProduct()) {
            return false;
        }

        /** @var HyperPcModelMoysklad_Product $model */
        $model = JModelLegacy::getInstance('Moysklad_Product', HP_MODEL_CLASS_PREFIX);

        /** @var $product MoyskladProduct */
        $product = $model->getItem($this->id);

        return $product->id > 0 ? $product : false;
    }

    /**
     * Check service is archived
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function isArchived()
    {
        return (int) $this->state === HP_STATUS_ARCHIVED;
    }

    /**
     * Is position published
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function isPublished()
    {
        return (int) $this->state === HP_STATUS_PUBLISHED;
    }

    /**
     * Check service is trashed
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function isTrashed()
    {
        return (int) $this->state === HP_STATUS_TRASHED;
    }

    /**
     * Check if position is service
     *
     * @return bool
     *
     * @since 2.0
     */
    public function isService()
    {
        return $this->getType() === 'service';
    }

    /**
     * Check if position is part
     *
     * @return bool
     *
     * @since 2.0
     */
    public function isPart()
    {
        return $this->getType() === 'part';
    }

    /**
     * Check if position is product
     *
     * @return bool
     *
     * @since 2.0
     */
    public function isProduct()
    {
        return $this->getType() === 'product';
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
        return ['barcodes', 'params', 'metadata', 'images', 'translatable_params'];
    }

    /**
     * Fields of integer data.
     *
     * @return  array
     *
     * @since   2.0
     */
    protected function _getFieldInt()
    {
        return ['id', 'type_id', 'state', 'product_folder_id', 'modified_user_id', 'created_user_id', 'ordering', 'vat'];
    }

    /**
     * Fields of money.
     *
     * @return  array
     *
     * @since   2.0
     */
    protected function _getFieldMoney()
    {
        return ['list_price', 'sale_price'];
    }
}
