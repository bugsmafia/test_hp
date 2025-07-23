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

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use HYPERPC\Joomla\View\ViewLegacy;
use HYPERPC\ORM\Entity\Processingplan;
use Joomla\CMS\Access\Exception\NotAllowed;
use HYPERPC\Joomla\Model\Entity\Interfaces\Stockable;

/**
 * Class HyperPcViewProcessingplan
 *
 * @property    Processingplan $processingplan
 *
 * @since       2.0
 */
class HyperPcViewProcessingplan extends ViewLegacy
{

    /**
     * Default display view action.
     *
     * @param   null|string $tpl
     *
     * @return  void
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public function display($tpl = null)
    {
        $this->hyper['doc']->setMetaData('robots', 'noindex');

        if (empty($this->hyper['input']->get(HP_COOKIE_HMP))) { // check manager's cookie
            /** @todo uncomment on prod */
            //throw new NotAllowed(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        $id = (int) $this->hyper['input']->get('id', 0);
        $this->processingplan = $this->hyper['helper']['processingplan']->findById($id);
        if (!$this->processingplan->id) {
            throw new Exception(Text::_('COM_HYPERPC_NOTHING_FOUND'), 404);
        }

        parent::display($tpl);
    }

    /**
     * Get availability label color class
     *
     * @param   string $availability
     *
     * @return  string
     *
     * @since   2.0
     */
    protected function _getAvailabilityLabelColorClass($availability)
    {
        $colorClass = ' uk-text-';
        switch ($availability) {
            case Stockable::AVAILABILITY_INSTOCK:
                $colorClass .= 'success';
                break;
            case Stockable::AVAILABILITY_PREORDER:
            case Stockable::AVAILABILITY_OUTOFSTOCK:
                $colorClass .= 'warning';
                break;
            case Stockable::AVAILABILITY_DISCONTINUED:
                $colorClass .= 'danger';
                break;
            default:
                $colorClass .= 'muted';
                break;
        }

        return $colorClass;
    }
}
