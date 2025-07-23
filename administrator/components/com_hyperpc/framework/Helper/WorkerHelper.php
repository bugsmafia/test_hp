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

namespace HYPERPC\Helper;

use JBZoo\Utils\Str;
use HYPERPC\Data\JSON;
use Joomla\CMS\Date\Date;
use HYPERPC\ORM\Table\Table;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Filesystem\File;
use HYPERPC\Joomla\Model\Entity\Worker;
use HYPERPC\Helper\Context\EntityContext;

/**
 * Class WorkerHelper
 *
 * @package HYPERPC\Helper
 *
 * @method  Worker  findByName($value, array $options = [])
 *
 * @since   2.0
 */
class WorkerHelper extends EntityContext
{

    const CLOSE_TIME = '20:00';

    const DEFAULT_WORKER_ID = 1;

    const REGEX = '/{loadworkers\s(.*?)}/i';

    /**
     * Hold worker actual list.
     *
     * @var     array
     *
     * @since   2.0
     */
    protected static $_workers = [];

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
        $table = Table::getInstance('Workers');
        $this->setTable($table);

        parent::initialize();

        $db = $this->hyper['db'];

        self::$_workers = $this->findAll([
            'conditions' => [$db->quoteName('a.published') . ' = ' . HP_STATUS_PUBLISHED]
        ]);
    }

    /**
     * Get current value, find by id after by name.
     *
     * @param   string|int $value
     *
     * @return  int|null
     *
     * @since   2.0
     */
    public function getCurrentValue($value)
    {
        $value    = trim((string) $value);
        $idWorker = $this->findById($value);

        if ($idWorker->id) {
            return $idWorker->id;
        }

        $nameWorker = $this->findByName($value);
        if ($nameWorker->id) {
            return $nameWorker->id;
        }

        return self::DEFAULT_WORKER_ID;
    }

    /**
     * Get default worker
     *
     * @return  Worker
     *
     * @since   2.0
     *
     * @todo    get default id from settings
     */
    public function getDefaultWorker()
    {
        return self::$_workers[self::DEFAULT_WORKER_ID] ?? new Worker();
    }

    /**
     * Setup last AmoCRM action by lead.
     *
     * @param   JSON $lead
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function setLastAmoCrmActionByLead(JSON $lead)
    {
        $amoCrmUserId = $lead->get('modified_user_id', 0, 'int');
        if ($amoCrmUserId > 0) {
            $workers = $this->getWorkers();
            /** @var Worker $worker */
            foreach ($workers as $worker) {
                $workerAmoCrmId = $worker->params->get('amo_responsible_user_id', 0, 'int');
                if ($workerAmoCrmId === $amoCrmUserId) {
                    $worker->set('last_amo_crm_action', Date::getInstance()->toSql());
                    return $this->hyper['helper']['worker']->getTable()->save($worker);
                }
            }
        }

        return false;
    }

    /**
     * Render workers by tag snippet.
     *
     * @param   $article
     *
     * @return  void
     *
     * @throws  \Exception
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function renderBySnippet(&$article)
    {
        preg_match_all(self::REGEX, $article->text, $matches, PREG_SET_ORDER);
        if (count($matches)) {
            $this->hyper['helper']['assets']->js('js:widget/site/load-workers.js');
            foreach ($matches as $match) {
                $output      = [];
                $data        = new JSON();
                $matchData   = trim($match[1]);
                $dataDetails = explode(' ', $matchData);
                foreach ($dataDetails as $dataDetail) {
                    list($key, $value) = explode('=', $dataDetail);
                    $data->set($key, $value);
                }

                if (!$data->get('tpl')) {
                    $data->set('tpl', 'default');
                }

                if (!$data->get('wrapper')) {
                    $data->set('wrapper', 'list');
                }

                if ($data->get('id')) {
                    $hash = md5($data->write());

                    $data->set('id', explode(',', $data->get('id')));

                    $output[] = '<div id="hp-' . $hash . '" data-params=\'' . json_encode($data) . '\' class="jsLoadWorker"></div>';

                    $this->hyper['helper']['assets']->widget('#hp-' . $hash, 'HyperPC.SiteLoadWorkers', $data->getArrayCopy());
                }

                $article->text = preg_replace(
                    "|$match[0]|",
                    addcslashes(implode(PHP_EOL, $output), '\\$'),
                    $article->text,
                    1
                );
            }
        } else {
            if ($this->hyper['input']->get('view') !== 'configurator') {
                $this->hyper['helper']['assets']->js('js:widget/site/load-workers.js');
                $this->hyper['helper']['assets']->widget('.jsLoadWorker', 'HyperPC.SiteLoadWorkers');
            }
        }
    }

    /**
     * Get actual worker list.
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getWorkers()
    {
        return self::$_workers;
    }

    /**
     * Get path to worker's photo.
     *
     * @param   Worker $worker
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getPhotoPath(Worker $worker)
    {
        $imagePath = '/images/company/contacts/contact/male-placeholder.png';
        $paramsImage = $worker->params->get('image', '', 'hpimagepath');
        if (!empty($paramsImage)) {
            $paramsImage = Path::clean('/' . $paramsImage);
            $testPath = strpos($paramsImage, '?') ? substr($paramsImage, 0, strpos($paramsImage, '?')) : $paramsImage;
            if (File::exists(JPATH_ROOT . $testPath)) {
                $imagePath = $paramsImage;
            }
        }

        return $imagePath;
    }
}
