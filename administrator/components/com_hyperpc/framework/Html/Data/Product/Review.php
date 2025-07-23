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

namespace HYPERPC\Html\Data\Product;

defined('_JEXEC') or die('Restricted access');

use Exception;
use JBZoo\Data\Data;
use JBZoo\Utils\Filter;
use Joomla\CMS\Form\Form;
use HYPERPC\ORM\Table\Table;
use HYPERPC\Joomla\View\Html\Data\HtmlData;
use HYPERPC\Joomla\Model\Entity\Interfaces\ProductMarker;

/**
 * Class Review
 *
 * @package HYPERPC\Html\Data\Review
 *
 * @since   2.0
 */
class Review extends HtmlData
{

    /**
     * Hold reviews context
     *
     * @var   string
     *
     * @since 2.0
     */
    protected $_context;

    /**
     * Hold result data from $this->findAll().
     *
     * @var     array
     *
     * @since   2.0
     */
    protected static $_findAllData = [];

    /**
     * Hold rating by grade
     *
     * @var   array
     *
     * @since 2.0
     */
    public $rating = [
        'star_5' => 0,
        'star_4' => 0,
        'star_3' => 0,
        'star_2' => 0,
        'star_1' => 0
    ];

    /**
     * Hold reviews
     *
     * @var   array
     *
     * @since 2.0
     */
    public $reviews = [];

    /**
     * Hold category products ids
     *
     * @var   array
     *
     * @since 2.0
     */
    public $productIds = [];

    /**
     * Hold ajax load products params
     *
     * @var   array
     *
     * @since 2.0
     */
    public $ajaxLoadArgs = [];

    /**
     * Hold product
     *
     * @var   ProductMarker
     *
     * @since 2.0
     */
    public $product;

    /**
     * Hold category
     *
     * @var   array
     *
     * @since 2.0
     */
    public $category;

    /**
     * Hold total rating
     *
     * @var   array
     *
     * @since 2.0
     */
    public $totalRating = 0;

    /**
     * Hold count reviews
     *
     * @var   array
     *
     * @since 2.0
     */
    public $reviewsCount = 0;

    /**
     * Review constructor
     *
     * @param  ProductMarker $product
     * @param  string        $mode
     * @param  int           $offset
     * @param  int           $limit
     *
     * @throws Exception
     *
     * @since  2.0
     */
    public function __construct(ProductMarker $product, $mode = 'default', $offset = 0, $limit = 0)
    {
        parent::__construct();

        $this->product  = $product;
        $this->category = $product->getFolder();
        $this->_context = $product->getReviewsContext();

        if ($this->category->isGeneralReview() && $categoryProducts = $this->category->getProducts(
            [],
            'a.name ASC',
            false
        )) {
            $this->productIds = array_keys($categoryProducts);
        } else {
            $this->productIds[] = $product->id;
        }

        $this->setCountByGrades();

        if ($mode === 'default') {
            $this->setItemReviews($offset, $limit);
        }
    }

    /**
     * Get reviews
     *
     * @return array
     *
     * @since  2.0
     */
    public function getReviews()
    {
        return $this->reviews;
    }

    /**
     * Get preview global value.
     *
     * @return  int
     *
     * @since   2.0
     */
    public function getPreviewValue()
    {
        return (int) $this->hyper['params']->get('review_show_preview', 5);
    }

    /**
     * Get total rating
     *
     * @return int
     *
     * @since  2.0
     */
    public function getTotalRating()
    {
        return $this->totalRating;
    }

    /**
     * Get reviews count
     *
     * @return int
     *
     * @since  2.0
     */
    public function getReviewsCount()
    {
        return $this->reviewsCount;
    }

    /**
     * Get reviews count
     *
     * @return string
     *
     * @since  2.0
     */
    public function getTotalSlant()
    {
        return $this->hyper['helper']['review']->getTotalSlant((int) $this->reviewsCount);
    }

    /**
     * Get rating
     *
     * @return array
     *
     * @since  2.0
     */
    public function getRating()
    {
        return $this->rating;
    }

    /**
     * Get ajax load args
     *
     * @return array
     *
     * @since  2.0
     */
    public function getAjaxArgs()
    {
        return $this->ajaxLoadArgs;
    }

    /**
     * Get form instance.
     *
     * @return  Form
     *
     * @since   2.0
     */
    public function getForm()
    {
        static $form;
        if (!$form) {
            Form::addFormPath(JPATH_ROOT . '/components/' . HP_OPTION . '/models/forms');

            $form = Form::getInstance(HP_OPTION . '.review', 'review', [
                'load_data' => true,
                'control'   => JOOMLA_FORM_CONTROL
            ]);
        }

        return $form;
    }

    /**
     * Get item review list.
     *
     * @param   string  $order
     * @param   bool    $published
     * @param   int     $offset
     * @param   int     $limit
     *
     * @throws Exception
     *
     * @since   2.0
     */
    public function setItemReviews($offset = 0, $limit = 0, $order = 'created_time DESC', $published = true)
    {
        $db = $this->hyper['db'];

        $conditions = [
            $db->quoteName('a.context')   . ' = ' . $db->quote($this->_context),
            $db->quoteName('a.published') . ' = ' . $db->quote(Filter::int($published))
        ];

        $conditions[] = $db->quoteName('a.item_id') . ' IN (' . implode(',', $this->productIds) . ')';

        $this->ajaxLoadArgs = [
            'limit'       => $limit,
            'itemId'      => $this->product->id,
            'context'     => $this->_context,
        ];

        if ($offset === 0) {
            $initialAmount = $this->getPreviewValue();
            $this->ajaxLoadArgs['start'] = $initialAmount;
        } else {
            $this->ajaxLoadArgs['start'] = $offset + $limit;
        }

        if ($this->ajaxLoadArgs['start'] >= $this->reviewsCount) {
            $this->ajaxLoadArgs = [];
        }

        $this->reviews = $this->findAll([
            'conditions' => $conditions,
            'order'  => $order,
            'limit'  => isset($initialAmount) ? $initialAmount : $limit,
            'offset' => $offset,
        ]);
    }

    /**
     * Calculate reviews counts
     *
     * @since  2.0
     */
    public function setCountByGrades()
    {
        $db = $this->hyper['db'];

        $conditions = [
            $db->qn('item_id') . ' IN (' . implode(', ', $this->productIds) . ')',
            $db->qn('published') . ' = ' . HP_STATUS_PUBLISHED
        ];

        $subQuery = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->qn(HP_TABLE_REVIEWS, 'a'))
            ->where($conditions);

        $query = $db->getQuery(true)
            ->select(['a.rating', 'COUNT(*) as count', '(' . $subQuery->__toString() . ') as total'])
            ->from($db->qn(HP_TABLE_REVIEWS, 'a'))
            ->where($conditions)
            ->group($db->qn('a.rating'));

        $result = $db->setQuery($query)->loadAssocList();

        $totalSum = 0;
        $total    = 0;
        foreach ($result as $item) {
            $this->reviewsCount += $item['count'];

            if ($item['rating']) {
                $total    += $item['count'];
                $totalSum += $item['rating'] * $item['count'];
            }

            $this->rating[$item['rating'] === '0' ? 'star_1' : 'star_' . $item['rating']] = ($item['count'] * 100) / $item['total'];
        }

        $this->totalRating  = ($totalSum <= 0) ? 0 : round($totalSum / $total, 1);
    }

    /**
     * Find all record.
     *
     * @param   array $options
     *
     * @return  array|mixed
     *
     * @throws  \Exception
     *
     * @since   1.0
     */
    public function findAll(array $options = [])
    {
        $options = new Data(array_replace([
            'conditions' => [],
            'key'        => 'id',
            'select'     => ['a.*'],
            'order'      => 'a.id ASC',
            'offset'     => 0,
            'limit'      => 0
        ], $options));

        $table = Table::getInstance('Reviews');
        $options->set('table', $table->getTableName());

        $hash = md5($options->write());

        if (!array_key_exists($hash, self::$_findAllData)) {
            $db = $table->getDbo();

            /** @var \JDatabaseQueryMysqli $query */
            $query = $db
                ->getQuery(true)
                ->select($options->get('select'))
                ->from($db->qn($options->get('table'), 'a'))
                ->order($options->get('order'));

            $this->_setConditions($query, $options->get('conditions'));
            $_list = (array) $db
                ->setQuery($query, $options->get('offset'), $options->get('limit'))
                ->loadAssocList($options->get('key'), $table->getEntity());

            $class = $table->getEntity();
            $list  = [];
            foreach ($_list as $id => $item) {
                $list[$id] = new $class($item);
            }

            self::$_findAllData[$hash] = $list;
        }

        return self::$_findAllData[$hash];
    }
}
