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
 * @author      Artem Vyshnevskiy
 */

defined('_JEXEC') or die('Restricted access');

use HYPERPC\Data\JSON;
use HYPERPC\ORM\Entity\User;
use Joomla\CMS\Language\Text;
use HYPERPC\ORM\Entity\Review;
use Joomla\CMS\Session\Session;
use HYPERPC\Helper\ReviewHelper;
use HYPERPC\Joomla\Controller\ControllerLegacy;
use HYPERPC\Html\Data\Product\Review as ReviewData;

/**
 * Class HyperPcControllerReview
 *
 * @property    ReviewHelper $_helper
 *
 * @since       2.0
 */
class HyperPcControllerReview extends ControllerLegacy
{

    /**
     * Ajax save review.
     *
     * @return  void
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public function ajaxSave()
    {
        $output = new JSON([
            'message' => '',
            'output'  => null,
            'result'  => false
        ]);

        $data = new JSON((array) $this->hyper['input']->get('jform', [], 'array'));

        $this->hyper['input']->server->set('HTTP_X_CSRF_TOKEN', $data->get('token'));

        if (!Session::checkToken()) {
            $output->set('message', Text::_('JINVALID_TOKEN'));
            $this->hyper['cms']->close($output->write());
        };

        /** @var User */
        $user = $this->hyper['user'];

        $data->set('created_user_id', $user->id);
        if (!$data->get('created_user_id')) {
            $output->set('message', Text::_('COM_HYPERPC_REVIEW_ERROR_USER_NOT_AUTH_IN'));
            $this->hyper['cms']->close($output->write());
        }

        $userOrders = $user->getOrders(0);
        if (!array_key_exists((int) $data->get('order_id'), $userOrders)) {
            $output->set('message', Text::_('COM_HYPERPC_REVIEW_ERROR_NOT_BELONG_TO_USER'));
            $this->hyper['cms']->close($output->write());
        }

        $context = $data->get('context');
        $orderId = $data->get('order_id');

        $order   = $userOrders[$orderId];
        $product = $order->getReviewProduct();

        if (!$product && ($context === 'com_hyperpc.product' || $context === 'com_hyperpc.position')) {
            $output->set('message', Text::_('COM_HYPERPC_REVIEW_ERROR_NOT_PRODUCT_IN_ORDER'));
            $this->hyper['cms']->close($output->write());
        }

        if (!$order->isSold()) {
            $output->set('message', Text::_('COM_HYPERPC_REVIEW_ERROR_ORDER_COMPLETED'));
            $this->hyper['cms']->close($output->write());
        }

        $hasUserReview = $this->_helper->hasUserReviewBefore(
            $data->get('context'),
            $data->get('item_id'),
            $data->get('order_id')
        );

        if ($hasUserReview) {
            $output->set('message', Text::_('COM_HYPERPC_REVIEW_ERROR_USER_HAVE_LEFT_BEFORE'));

            $this->hyper['cms']->close($output->write());
        }

        /** @var HyperPcModelReview $model */
        $model = $this->getModel();
        /** @var HyperPcTableReviews $table */
        $table = $model->getTable();
        $form  = $model->getForm();

        $arrayData = $data->getArrayCopy();
        $form->bind($arrayData);
        $filterData = $form->filter($arrayData);
        if (is_array($filterData) && $form->validate($filterData)) {
            $table->bind($filterData);
            if ($table->store()) {
                /** @var Review $review */
                $review = $this->hyper['helper']['review']->findById($table->getDbo()->insertid());

                if ($this->hyper['helper']['review']->isPreModeration()) {
                    $output
                        ->set('result', true)
                        ->set('output', null)
                        ->set('message', Text::_('COM_HYPERPC_REVIEW_SUCCESS_SEND_MODERATE_MSG'));
                } else {
                    $reviewHtml = [
                        '<tr>',
                            '<td style="visibility: hidden; width: 0; display: none;">',
                                $review->created_time->format('d-m-Y'),
                            '</td>',
                            '<td data-sort-method="none">',
                                $this->hyper['helper']['review']->render([
                                    'i'      => 0,
                                    'review' => $review,
                                    'item'   => $review->getItem()
                                ], 'item'),
                            '</td>',
                            '<td style="visibility: hidden; width: 0; display: none;">',
                                (int) $review->rating,
                            '</td>',
                        '</tr>',
                    ];

                    $output
                        ->set('result', true)
                        ->set('output', implode(PHP_EOL, $reviewHtml))
                        ->set('message', Text::_('COM_HYPERPC_REVIEW_SUCCESS_SEND_MSG'));
                }
            }
        } else {
            $message = [];
            /** @var RuntimeException $error */
            foreach ($form->getErrors() as $error) {
                $message[] = $error->getMessage();
            }

            $output->set('message', implode(PHP_EOL, $message));
        }

        $this->hyper['cms']->close($output->write());
    }

    /**
     * Load reviews by ajax
     *
     * @return string
     * @throws Exception
     *
     * @since   2.0
     */
    public function loadReviews()
    {
        $output = new JSON([
            'html'    => null,
            'button'  => false,
        ]);

        $limit   = $this->hyper['input']->get('limit', 0, 'int');
        $start   = $this->hyper['input']->get('start', 0, 'int');
        $itemId  = $this->hyper['input']->get('id', 0, 'int');
        $context = $this->hyper['input']->get('context', HP_OPTION . '.product');

        $html = [];

        $product      = $this->hyper['helper']['moyskladProduct']->findById($itemId);
        $reviewsData  = new ReviewData($product, 'default', $start, $limit);
        $ajaxLoadArgs = $reviewsData->getAjaxArgs();
        $reviews      = $reviewsData->getReviews();

        if (count($reviews)) {
            foreach ($reviews as $review) {
                $html[] = $this->hyper['helper']['render']->render('reviews/default/item', [
                    'review'  => $review
                ]);
            }
            $output->set('html', implode(PHP_EOL, $html));
        }

        if (count($ajaxLoadArgs)) {
            $output->set('button', $this->hyper['helper']['render']->render('reviews/default/load_more', [
                'ajaxLoadArgs' => $ajaxLoadArgs
            ]));
        }

        return $this->hyper['cms']->close($output->write());
    }

    /**
     * Method to get a model object, loading it if required.
     *
     * @param   string  $name    The model name. Optional.
     * @param   string  $prefix  The class prefix. Optional.
     * @param   array   $config  Configuration array for model. Optional.
     *
     * @return  \JModelLegacy|boolean  Model object on success; otherwise false on failure.
     *
     * @since   2.0
     */
    public function getModel($name = 'Review', $prefix = HP_MODEL_CLASS_PREFIX, $config = [])
    {
        return parent::getModel($name, $prefix, $config);
    }

    /**
     * Hook on initialize controller.
     *
     * @param   array $config
     * @return  void
     *
     * @since   2.0
     *
     * @SuppressWarnings("unused")
     */
    public function initialize(array $config)
    {
        $this
            ->registerTask('ajax-save', 'ajaxSave')
            ->registerTask('load-reviews', 'loadReviews');

        $this->_helper = $this->hyper['helper']['review'];
    }
}
