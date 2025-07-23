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
 */

namespace HYPERPC\Helper;

use HYPERPC\Data\JSON;
use JBZoo\Utils\Filter;
use Joomla\CMS\Form\Form;
use HYPERPC\ORM\Table\Table;
use Joomla\CMS\Language\Text;
use HYPERPC\ORM\Entity\Review;
use HYPERPC\Helper\Context\EntityContext;

/**
 * Class ReviewHelper
 *
 * @package HYPERPC\Helper
 *
 * @since   2.0
 */
class ReviewHelper extends EntityContext
{

    /**
     * Xml review form value filter.
     *
     * @param   $value
     *
     * @return  string
     *
     * @since   2.0
     */
    public static function formFilter($value)
    {
        return strip_tags($value);
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
     * Get total rating.
     *
     * @param   array  $reviews
     *
     * @return  float|int
     *
     * @since   2.0
     */
    public function getTotalRating(array $reviews = [])
    {
        $totalSum = 0;
        $total    = 0;
        /** @var Review $review */
        foreach ($reviews as $review) {
            if ($review->rating) {
                $total++;
                $totalSum += $review->rating;
            }
        }

        return ($totalSum <= 0) ? 0 : round($totalSum / $total, 1);
    }

    /**
     * Get total review slant text.
     *
     * @param   int $number
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getTotalSlant($number)
    {
        return Text::plural('COM_HYPERPC_REVIEW_N_COUNT', (int) $number);
    }

    /**
     * Get review global template.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getTpl()
    {
        return $this->hyper['params']->get('review_tpl', 'default');
    }

    /**
     * Get all user reviews.
     *
     * @param   null|int  $userId
     *
     * @return  array|mixed
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getUserReviews($userId = null)
    {
        if (!$userId) {
            $userId = $this->hyper['user']->id;
        }

        return $this->findAll([
            'conditions' => [
                $this->_db->quoteName('a.published') . ' = ' . $this->_db->quote((int) $userId)
            ],
            'order' => [
                'a.created_time'
            ]
        ]);
    }

    /**
     * Reassign all reviews between users
     *
     * @param  string $oldUserId
     * @param  string $userId
     *
     * @return mixed
     *
     * @since 2.0
     */
    public function reassignUser(string $oldUserId, string $userId)
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query
            ->update($db->quoteName($this->getTable()->getTableName()))
            ->set([
                $db->quoteName('created_user_id') . ' = ' . $db->quote($userId),
            ])
            ->where([
                $db->quoteName('created_user_id') . ' = ' . $db->quote($oldUserId),
            ]);

        return $db->setQuery($query)->execute();
    }

    /**
     * Check has user send review before.
     *
     * @param   string  $context
     * @param   int     $itemId
     * @param   null    $userId
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function hasUserReviewBefore($context, $itemId, $orderId, $userId = null)
    {
        if (!$userId) {
            $userId = $this->hyper['user']->id;
        }

        $db = $this->hyper['helper']['moyskladProduct']->getDbo();

        $userReview = $this->hyper['helper']['review']->findByCreatedUserId($userId, [
            'conditions' => [
                $db->quoteName('a.context') . ' = ' . $db->quote($context),
                $db->quoteName('a.item_id') . ' = ' . $db->quote($itemId),
                $db->quoteName('a.order_id') . ' = ' . $db->quote($orderId)
            ]
        ]);

        return Filter::bool($userReview->id);
    }

    /**
     * Initialize helper.
     *
     * @return  void
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function initialize()
    {
        $table = Table::getInstance('Reviews');
        $this->setTable($table);

        parent::initialize();
    }

    /**
     * Check pre moderation mode.
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function isPreModeration()
    {
        return Filter::bool($this->hyper['params']->get('review_pre_moderation', HP_STATUS_PUBLISHED));
    }

    /**
     * Render method.
     *
     * @param   array  $args
     * @param   null   $layout
     *
     * @return  string
     *
     * @since   2.0
     */
    public function render(array $args = [], $layout = null)
    {
        $tpl = 'reviews/' . $this->getTpl();
        if ($layout) {
            $tpl .= '/' . $layout;
        }

        return $this->hyper['helper']['render']->render($tpl, $args);
    }

    /**
     * Get reviews separeted by rating.
     *
     * @param   array  $reviews
     *
     * @return  JSON
     *
     * @since   2.0
     */
    public function separateByRating(array $reviews = [])
    {
        $return = [
            'star_5' => [
                'items'   => [],
                'count'   => 0,
                'percent' => 0
            ],
            'star_4' => [
                'items'   => [],
                'count'   => 0,
                'percent' => 0,
            ],
            'star_3' => [
                'items'   => [],
                'count'   => 0,
                'percent' => 0,
            ],
            'star_2' => [
                'items'   => [],
                'count'   => 0,
                'percent' => 0,
            ],
            'star_1' => [
                'items'   => [],
                'count'   => 0,
                'percent' => 0,
            ]
        ];

        $total = count($reviews);


        /** @var Review $review */
        foreach ($reviews as $review) {
            $rating = (int) $review->rating;
            switch ($rating) {
                case 5:
                    $i = $return['star_5']['count']++;
                    $return['star_5']['items'][$review->id] = $review;
                    $return['star_5']['percent'] = (($i + 1) * 100) / $total;
                    break;

                case 4:
                    $i = $return['star_4']['count']++;
                    $return['star_4']['items'][$review->id] = $review;
                    $return['star_4']['percent'] = (($i + 1) * 100) / $total;
                    break;

                case 3:
                    $i = $return['star_3']['count']++;
                    $return['star_3']['items'][$review->id] = $review;
                    $return['star_3']['percent'] = (($i + 1) * 100) / $total;
                    break;

                case 2:
                    $i = $return['star_2']['count']++;
                    $return['star_2']['items'][$review->id] = $review;
                    $return['star_2']['percent'] = (($i + 1) * 100) / $total;
                    break;

                default:
                    $i = $return['star_1']['count']++;
                    $return['star_1']['items'][$review->id] = $review;
                    $return['star_1']['percent'] = (($i + 1) * 100) / $total;
            }
        }

        return new JSON($return);
    }
}
