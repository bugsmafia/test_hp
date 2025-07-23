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
 * @author      Roman Evsyukov <roman_e@hyperpc.ru>
 * @author      Artem Vyshnevskiy
 */

defined('_JEXEC') or die('Restricted access');

use JBZoo\Utils\Arr;
use HYPERPC\Data\JSON;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;
use HYPERPC\Filters\FilterFactory;
use HYPERPC\Joomla\Model\Entity\ProductFolder;
use HYPERPC\Joomla\Controller\ControllerLegacy;
use HYPERPC\Html\Data\Filter\ProductFolderFilter;

/**
 * Class HyperPcControllerProduct_Folders
 *
 * @since 2.0
 */
class HyperPcControllerProduct_Folders extends ControllerLegacy
{

    /**
     * Hook on initialize controller.
     *
     * @param array $config
     *
     * @since   2.0
     */
    public function initialize(array $config)
    {
        parent::initialize($config);
        $this
            ->registerTask('filter-group', 'filterFolder')
            ->registerTask('filter-parts', 'filterParts');
    }

    public function filterParts()
    {
        $this->hyper['cms']->setHeader('Content-Type', 'application/json');

        $output = new Registry([
            'result' => false
        ]);

        $productFolderId = $this->hyper['input']->getInt('id');
        /** @var ProductFolder $productFolder */
        $productFolder = $this->hyper['helper']['productFolder']->findById($productFolderId);
        if (!$productFolder->id) {
            $output->set('message', Text::_('JGLOBAL_CATEGORY_NOT_FOUND'));
            $this->hyper['cms']->close($output->toString());
        }

        try {
            $partsFilter = FilterFactory::createFilter('productFolderParts');
        } catch (\Throwable $th) {
            $output->set('message', $th->getMessage());
            $this->hyper['cms']->close($output->toString());
        }

        $output->set('filters', $partsFilter->getState());

        if (!$partsFilter->hasItems()) {
            $output->set('message', Text::_('COM_HYPERPC_FILTERS_RESULT_NOT_FOUND'));
            $this->hyper['cms']->close($output->toString());
        }

        $output->set('result', true);

        $parts = $this->hyper['helper']['moyskladPart']->getByItemKeys($partsFilter->getItems());
        $compareItems = $this->hyper['helper']['compare']->getItems('position');

        $html = [];
        foreach ($parts as $part) {
            $html[] = $this->hyper['helper']['render']->render('part/teaser/part', [
                'part'         => $part,
                'group'        => $productFolder,
                'compareItems' => $compareItems,
            ]);
        }

        $output->set('resultsCount', count($html));
        $output->set('html', join(PHP_EOL, $html));

        $this->hyper['cms']->close($output->toString());
    }

    /**
     * filter data from product folder
     *
     * @return  void
     *
     * @throws  Exception
     *
     * @since   2.0
     *
     * @deprecated
     */
    public function filterFolder()
    {
        $this->hyper['cms']->setHeader('Content-Type', 'application/json');

        $db      = $this->hyper['db'];
        $html    = [];
        $output  = new JSON([
            'result'  => false,
            'message' => null,
        ]);

        $sorting  = $this->hyper['input']->get('sorting', 'availability_asc', 'string');
        $groupId  = $this->hyper['input']->get('id');
        $fields   = $this->hyper['input']->get('filters', [], 'array');
        $url      = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_QUERY);

        /** @var $productFolderHelper ProductFolderHelper */
        $productFolderHelper = $this->hyper['helper']['ProductFolder'];

        $productFolder  = $productFolderHelper->findById($groupId);
        $partOrder      = $productFolder->getPartOrder();
        $teaserTemplate = 'part';

        $productFolder->set('sorting', $sorting);

        $fields = array_filter($fields, function ($element) {
            if (empty($element[0])) {
                return false;
            }

            return !empty($element);
        });

        $compareItems = $this->hyper['helper']['compare']->getItems('position');

        $publishedStatuses = [HP_STATUS_PUBLISHED, HP_STATUS_ARCHIVED];

        $conditions[] = $db->qn('a.state') . ' in (' . implode(',', $publishedStatuses) . ')';

        $items = $productFolder->getParts($conditions, $partOrder, 'id', false);

        list ($_parts, $discontinuedParts) = $productFolderHelper->sortParts($items, $productFolder);

        $filter = new ProductFolderFilter($productFolder, $_parts);

        $_fields = $filter->getFields();
        if (count($_fields)) {
            $params        = [];
            $filterParams  = [];
            $allowedFields = Arr::map(function ($element) {
                return $element->name;
            }, $_fields);

            if ($url) {
                foreach (explode('&', $url) as $param) {
                    list ($paramName, $paramValue) = explode('=', $param);

                    if (!in_array($paramName, $allowedFields)) {
                        $params[$paramName] = $paramValue;
                    }
                }
            }

            foreach ($fields as $paramName => $paramValue) {
                $paramValue = implode(FILTER_URL_DELIMITER, $paramValue);
                $filterParams[$paramName] = $paramValue;
            }

            $url = $this->hyper['route']->build(array_merge($filterParams, $params));
        }

        $resultFilters = $filter->getFilterResults();
        $parts         = $filter->getItems();

        foreach ($parts as $part) {
            $html[] = $this->hyper['helper']['render']->render('part/teaser/' . $teaserTemplate, [
                'part'         => $part,
                'group'        => $productFolder,
                'filters'      => $resultFilters,
                'compareItems' => $compareItems,
            ]);
        }

        $output->set('filters', $resultFilters);
        $output->set('resultsCount', count($html));

        if (empty($html)) {
            $output->set('message', Text::_('COM_HYPERPC_FILTERS_RESULT_NOT_FOUND'));
            $this->hyper['cms']->close($output->write());
        }

        $output
            ->set('result', true)
            ->set('html', join(' ', $html))
            ->set('url', $url);

        $this->hyper['cms']->close($output->write());
    }

    /**
     * Method to get a model object, loading it if required.
     *
     * @param   string $name
     * @param   string $prefix
     * @param   array $config
     *
     * @return  bool|JModelLegacy
     *
     * @since   2.0
     */
    public function getModel($name = 'Product_folder', $prefix = HP_MODEL_CLASS_PREFIX, $config = [])
    {
        return parent::getModel($name, $prefix, $config);
    }
}
