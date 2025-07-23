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

namespace HYPERPC\ORM\Entity;

use HYPERPC\Data\JSON;
use Joomla\CMS\Date\Date;
use MoySklad\Entity\Group;
use MoySklad\Entity\Assortment;
use HYPERPC\Joomla\Model\Entity\MoyskladPart;
use HYPERPC\MoySklad\Entity\Document\PlanItem;
use HYPERPC\MoySklad\Entity\Document\PlanItems;
use HYPERPC\Object\Processingplan\PlanItemDataCollection;
use HYPERPC\MoySklad\Entity\Document\ProcessingPlan as MoyskladProcessingPlan;

/**
 * Processingplan class.
 *
 * @property    int         $id
 * @property    string      $uuid
 * @property    string      $name
 * @property    JSON        $parts
 * @property    Date        $created_time
 * @property    Date        $modified_time
 *
 * @package     HYPERPC\ORM\Entity
 *
 * @since       2.0
 */
class Processingplan extends Entity
{

    /**
     * Field list of json type.
     *
     * @var     array
     *
     * @since   2.0
     */
    protected $_fieldJsonType = ['parts'];

    /**
     * Field list of boolean type.
     *
     * @var     array
     *
     * @since   2.0
     */
    protected $_fieldBooleanType = [];

    /**
     * Get admin (backend) edit url.
     *
     * @return  null
     *
     * @since   2.0
     */
    public function getAdminEditUrl()
    {
        return null;
    }

    /**
     * Get moysklad edit url.
     *
     * @return  string|null
     *
     * @since   2.0
     */
    public function getEditUrl()
    {
        if ($this->uuid) {
            return $this->hyper['helper']['moysklad']->getAppPath('processingplan') . "/edit?id={$this->uuid}";
        }

        return null;
    }

    /**
     * Initialize hook method.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function initialize()
    {
        $this
            ->setTablePrefix()
            ->setTableType('Processingplans');

        parent::initialize();
    }

    /**
     * Build moysklad processingplan entity from ORM object
     *
     * @param   Group $group
     * @param   MoyskladProcessingPlan|null $processingPlan
     *
     * @return  MoyskladProcessingPlan
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public function toMoyskladEntity(Group $group, ?MoyskladProcessingPlan $processingPlan = null): MoyskladProcessingPlan
    {
        $processingPlan = $processingPlan ?? new MoyskladProcessingPlan();
        if (empty($this->id)) {
            return $processingPlan;
        }

        $processingPlan->parent = $group;
        $processingPlan->name = $this->name;

        $processingPlan->shared = true;
        $processingPlan->externalCode = $this->id;

        $planItemCollection = PlanItemDataCollection::create((array) $this->parts);
        $processingPlan->materials = $planItemCollection->toPlanItems();

        $processingPlan->products = new PlanItems();

        $productItem = new PlanItem();
        $productItem->quantity = 1;

        $productVariant = $this->hyper['helper']['moyskladProductVariant']->findById($this->id);

        $productItemType = $productVariant->getContext();

        $productItem->assortment = new Assortment(
            $this->hyper['helper']['moysklad']->buildEntityMeta($productItemType, $productVariant->uuid)->toBaseMeta()
        );

        $processingPlan->products->rows[] = $productItem;

        return $processingPlan;
    }

    /**
     * Get processingplan parts
     *
     * @return  MoyskladPart[]
     *
     * @since   2.0
     */
    public function getParts()
    {
        $planItemCollection = PlanItemDataCollection::create((array) $this->parts);
        return $planItemCollection->toMoyskladParts();
    }
}
