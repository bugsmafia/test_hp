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
 * @author      Sergey Kalistratov <kalistratov.s.m@gmail.com>
 * @author      Roman Evsyukov
 * @author      Artem Vyshnevskiy
 */

defined('_JEXEC') or die('Restricted access');

use HYPERPC\Joomla\Form\FormField;
use HYPERPC\Joomla\Model\ModelList;
use HYPERPC\Joomla\Model\Entity\Position;
use HYPERPC\Joomla\Model\Entity\MoyskladProduct;
use HYPERPC\Joomla\Model\Entity\Interfaces\Stockable;

/**
 * Class JFormFieldMoyskladConfigurator
 *
 * @since   2.0
 */
class JFormFieldMoyskladConfigurator extends FormField
{

    /**
     * Name of the layout being used to render the field.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $layout = 'joomla.form.field.moysklad.configurator.default';

    /**
     * The form field type.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $type = 'Configurator';

    /**
     * Render field layout partial.
     *
     * @param   string $name
     * @param   array $args
     * @return  null|string
     *
     * @since   2.0
     */
    public function partial($name, array $args = [])
    {
        return $this->hyper['helper']['render']->render('joomla/form/field/moysklad/configurator/' . $name, $args, 'layouts');
    }

    /**
     * Prepare part for configurator.
     *
     * @param   array $productFoldersList
     * @return  array
     *
     * @throws  \Exception
     * @throws  \RuntimeException
     *
     * @since   2.0
     */
    protected function _prepareParts(array $productFoldersList)
    {
        $db = $this->hyper['db'];

        $folderIdsForItem = [];
        foreach ($productFoldersList as $productFolder) {
            if (in_array($productFolder->level, [2, 3]) && !in_array($productFolder->id, $folderIdsForItem)) {
                $folderIdsForItem[$productFolder->id] = $productFolder->id;
            }
        }

        $defaultParts = [];
        $product = $this->getProduct();
        if ($product->id) {
            $defaultParts = (array) $this->getProduct()->configuration->get('default');
        }

        $publishStatuses = [HP_STATUS_PUBLISHED, HP_STATUS_ARCHIVED];
        $conditions[] = $db->qn('a.state') . ' IN (' . implode(', ', $publishStatuses) . ')';
        if (count($folderIdsForItem)) {
            $conditions[] = $db->qn('a.product_folder_id') . ' IN (' . implode(',', $folderIdsForItem) . ')';
        }

        $itemList = [];
        $parts    = $this->hyper['helper']['moyskladPart']->findList(['a.*'], $conditions, 'a.name ASC');
        $services = $this->hyper['helper']['moyskladService']->findList(['a.*'], $conditions, 'a.name ASC');

        /** @var Position[] $items */
        $items = array_merge($parts, $services);
        foreach ($items as $item) {
            if ($item->isPublished() || ($item->isArchived() && \in_array($item->id, $defaultParts))) {
                $itemList[$item->product_folder_id][$item->id] = $item;
            } elseif ($item instanceof Stockable && $item->isArchived() && $item->isInStock()) {
                $itemList[$item->product_folder_id][$item->id] = $item;
            }
        }

        return $itemList;
    }

    /**
     * Method to get the field input markup.
     *
     * @return  string
     *
     * @since   2.0
     */
    protected function getInput()
    {
        $this->hyper['wa']
            ->useScript('jquery-check-all')
            ->usePreset('jquery-fancybox');

        $this->hyper['helper']['assets']
            ->js('js:widget/fields/configurator.js')
            ->widget('.jsGieldConfigurator', 'HyperPC.FieldConfigurator', [
                'parts'   => $this->value->get('default', []),
                'options' => array_values($this->value->get('option', []))
            ]);

        return parent::getInput();
    }

    /**
     * Get product.
     *
     * @return   MoyskladProduct
     *
     * @since    2.0
     */
    public function getProduct()
    {
        static $product;
        if (!$product) {
            $product = $this->hyper['helper']['moyskladProduct']->findById($this->hyper['input']->get('id'));
        }

        return $product;
    }

    /**
     * Method to get the data to be passed to the layout for rendering.
     *
     * @return  array
     *
     * @throws  \Exception
     * @throws  \RuntimeException
     *
     * @since   2.0
     */
    protected function getLayoutData()
    {
        $this->hyper['helper']['assets']->addScript('
            $("#groupTab a, #categoryTab a, #producerTab a").click(function (e) {
                e.preventDefault();
                $(this).tab("show");
            })
        ');

        $db = $this->hyper['db'];

        $conditions = [
            'NOT ' . $db->quoteName('a.alias') . ' = ' . $db->quote('root'),
        ];

        $folderList = $this->hyper['helper']['productFolder']->findList(['a.*'], $conditions, 'a.lft ASC');

        /** @var HyperPcModelProduct_Folders $productFolderModel */
        $productFolderModel = ModelList::getInstance('Product_folders');

        $rootCategory = (int) $this->hyper['params']->get('configurator_root_category', 1);

        $productFolders = $productFolderModel->buildTree($folderList, $rootCategory);

        $items = $this->_prepareParts($folderList);

        $variants = $this->hyper['helper']['moyskladVariant']->getVariants(true, [], true);

        return array_merge(parent::getLayoutData(), [
            'productFolders' => $productFolders,
            'variants'       => $variants,
            'items'          => $items
        ]);
    }
}
