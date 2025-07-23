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

namespace HYPERPC\Joomla\Model\Entity;

use HYPERPC\Data\JSON;

/**
 * Class Status
 *
 * @package     HYPERPC\Joomla\Model\Entity
 *
 * @since       2.0
 */
class Status extends Entity
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
     * AmoCMS pipeline Id.
     *
     * @var     int
     *
     * @since   2.0
     */
    public $pipeline_id;

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
     * Get site view category url.
     *
     * @return  string
     * @param   array $query
     *
     * @since   2.0
     */
    public function getViewUrl(array $query = [])
    {
        return null;
    }

    /**
     * Get pipeline data.
     *
     * @return  JSON
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function getPipeline()
    {
        /** @var JSON $pipelines */
        $pipelines = $this->hyper['helper']['crm']->getPipelineTmpData();
        return new JSON($pipelines->find($this->pipeline_id));
    }

    /**
     * Get AmoCRM status id.
     *
     * @return  int
     *
     * @since   2.0
     */
    public function getAmoStatusId()
    {
        return $this->params->get('amo_status_id', 0, 'int');
    }

    /**
     * Get pipeline name by id.
     *
     * @return  string
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function getPipelineName()
    {
        return $this->getPipeline()->get('name');
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
        return [
            'id',
            'pipeline_id'
        ];
    }
}
