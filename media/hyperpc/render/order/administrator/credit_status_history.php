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

defined('_JEXEC') or die('Restricted access');

use HYPERPC\Data\JSON;
use HYPERPC\Elements\Manager;
use Joomla\CMS\Language\Text;
use HYPERPC\Helper\RenderHelper;
use HYPERPC\Elements\ElementCredit;
use HYPERPC\Joomla\Model\Entity\Order;

/**
 * @var RenderHelper    $this
 * @var Order           $order
 */

$manager  = Manager::getInstance();

/** @var ElementCredit[] $elements */
$elements = (new JSON(
    (array) $manager->getElementsByGroups(Manager::ELEMENT_TYPE_CREDIT)
))->get(Manager::ELEMENT_TYPE_CREDIT);

echo '<h3 class="my-3">' . Text::_('COM_HYPERPC_ORDER_CREDIT_STATUS_TITLE') . '</h3>';

foreach ($elements as $element) {
    $history = (array) $order->params->find($element->getParamKey() . '.status_history');
    if (count($history)) {
        echo $this->hyper['helper']['render']->render('order/administrator/credit_list_history', [
            'history' => $history,
            'element' => $element
        ]);
    }
}
