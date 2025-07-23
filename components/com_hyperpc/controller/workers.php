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
 * @link        https://github.com/HYPER-PC/HYPERPC".
 *
 * @author      Sergey Kalistratov © <kalistratov.s.m@gmail.com>
 */

defined('_JEXEC') or die('Restricted access');

use HYPERPC\Data\JSON;
use HYPERPC\Helper\WorkerHelper;
use HYPERPC\Joomla\Model\Entity\Worker;
use HYPERPC\Joomla\Controller\ControllerLegacy;

/**
 * Class HyperPcControllerWorkers
 *
 * @since 2.0
 */
class HyperPcControllerWorkers extends ControllerLegacy
{

    /**
     * Hold WorkerHelper object.
     *
     * @var     WorkerHelper
     *
     * @since   2.0
     */
    public $helper;

    /**
     * Hook on initialize controller.
     *
     * @param   array $config
     *
     * @return  void
     *
     * @since   2.0
     */
    public function initialize(array $config)
    {
        $this
            ->registerTask('render-form', 'renderForm')
            ->registerTask('ajax-get-snippet-list', 'ajaxGetSnippetList');

        $this->helper = $this->hyper['helper']['worker'];
    }

    /**
     * Render worker form by id.
     *
     * @return  void
     *
     * TODO use view.html action.
     *
     * @since   2.0
     */
    public function renderForm()
    {
        /** @var Worker $worker */
        $worker = $this->hyper['helper']['worker']->findById($this->hyper['input']->get('id'));

        if ($worker->id) {
            echo $this->hyper['helper']['render']->render('worker/card/form', ['worker' => $worker]);
        }
    }

    /**
     * Get ajax html list worker by snippet.
     *
     * @return  void
     *
     * @throws  \Exception
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function ajaxGetSnippetList()
    {
        $output = new JSON([
            'result' => true,
            'html'   => null
        ]);

        $db        = $this->hyper['db'];
        $workerIds = (array) $this->hyper['input']->get('id', [1,2,3,4,5,9,7]);

        $workers = $this->helper->findAll([
            'conditions' => [$db->quoteName('a.published') . ' = ' . HP_STATUS_PUBLISHED]
        ]);

        $_workers  = [];
        $today     = date('N');
        $nowTime   = strtotime(date('H:i'));
        $closeTime = strtotime(WorkerHelper::CLOSE_TIME);
        $tomorrow  = date('N', strtotime('+1 day', time()));

        /** @var Worker $worker */
        foreach ($workers as $worker) {
            if (in_array((string) $worker->id, $workerIds)) {
                $fromTime = strtotime($worker->params->find('schedule.from_time'));
                $toTime   = strtotime($worker->params->find('schedule.to_time'));

                if ($nowTime >= $closeTime || (int) date('H') <= 10) {
                    if (!$worker->tomorrowHasDayOff()) {
                        $_workers[$worker->id] = $worker;
                    }
                } else {
                    $isCurrentTime = false;
                    if ($fromTime && $toTime) {
                        if ($nowTime >= $fromTime && $nowTime <= $toTime) {
                            $isCurrentTime = true;
                        }
                    }

                    $workedDays = (array) $worker->params->find('schedule.days');

                    if (in_array((string) $today, $workedDays) && $isCurrentTime) {
                        $_workers[$worker->id] = $worker;
                    }
                }
            }
        }

        $countWorkers = count($_workers);
        $limit = $this->hyper['input']->get('limit', 3, 'int');

        if ($countWorkers < $limit) {
            /** @var Worker $worker */
            foreach ($workers as $worker) {
                if ($countWorkers < $limit && !array_key_exists($worker->id, $_workers)) {
                    $fromTime = strtotime($worker->params->find('schedule.from_time'));
                    $toTime   = strtotime($worker->params->find('schedule.to_time'));

                    $isCurrentTime = false;
                    if ($fromTime && $toTime) {
                        if ($nowTime >= $fromTime && $nowTime <= $toTime) {
                            $isCurrentTime = true;
                        }
                    }

                    $workedDays = (array) $worker->params->find('schedule.days');
                    if (in_array((string) $tomorrow, $workedDays) && $isCurrentTime) {
                        $_workers[$worker->id] = $worker;
                        $countWorkers++;
                    }
                }
            }
        }

        $renderWorkers = $_workers;
        if ($countWorkers > $limit) {
            $workerKeys = (array) array_rand($_workers, $limit);
            if (count($workerKeys)) {
                $renderWorkers = [];
                foreach ($workerKeys as $aKey) {
                    if (array_key_exists($aKey, $_workers)) {
                        $renderWorkers[] = $_workers[$aKey];
                    }
                }
            }
        }

        $wrapper = $this->hyper['input']->get('wrapper', 'list');

        $output->set('html', $this->hyper['helper']['render']->render('worker/wrapper_' . $wrapper, [
            'workers' => $renderWorkers,
            'tpl'     => $this->hyper['input']->get('tpl', 'default')
        ]));

        $this->hyper['cms']->close($output->write());
    }
}
