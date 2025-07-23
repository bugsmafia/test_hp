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

namespace HYPERPC\Joomla\Model\Entity;

use HYPERPC\Data\JSON;
use Joomla\CMS\Date\Date;
use HYPERPC\Render\Worker as RenderWorker;

/**
 * Class Worker
 *
 * @method      RenderWorker getRender()
 *
 * @package     HYPERPC\Joomla\Model\Entity
 *
 * @since       2.0
 */
class Worker extends Entity
{

    /**
     * Id.
     *
     * @var     int
     *
     * @since   2.0
     */
    public $id;

    /**
     * Field name.
     *
     * @var     int
     *
     * @since   2.0
     */
    public $name;

    /**
     * Published status.
     *
     * @var     bool
     *
     * @since  2.0
     */
    public $published;

    /**
     * Params.
     *
     * @var     JSON
     *
     * @since   2.0
     */
    public $params;

    /**
     * Hold las order turn date.
     *
     * @var     Date
     *
     * @since   2.0
     */
    public $last_order_turn;

    /**
     * Hold las order turn date.
     *
     * @var     Date
     *
     * @since   2.0
     */
    public $last_form_turn;

    /**
     * Hold last user action in amoCrm.
     *
     * @var     Date
     *
     * @since   2.0
     */
    public $last_amo_crm_action;

    /**
     * Check now is work.
     *
     * @return  bool
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function nowIsWorked()
    {
        $ofWorkTime = strtotime($this->params->find('schedule.to_time'));
        $nowTime    = strtotime(date('H:i'));

        return ($ofWorkTime >= $nowTime);
    }

    /**
     * Check tomorrow day off.
     *
     * @return  bool
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function tomorrowHasDayOff()
    {
        $days     = (array) $this->params->find('schedule.days');
        $tomorrow = date('N', strtotime('tomorrow'));

        return !in_array((string) $tomorrow, $days);
    }

    /**
     * Get site view category url.
     *
     * @param   array $query
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getViewUrl(array $query = [])
    {
        return $this->hyper['route']->build([
            'layout' => 'edit',
            'view'   => 'worker',
            'id'      => $this->id
        ]);
    }

    /**
     * Fields of datetime.
     *
     * @return  array
     *
     * @since   2.0
     */
    protected function _getFieldDate()
    {
        return ['last_order_turn', 'last_form_turn', 'last_amo_crm_action'];
    }
}
