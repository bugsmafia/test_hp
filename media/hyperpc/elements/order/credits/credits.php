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
 * @author      Sergey Kalistratov <kalistratov.s.m@gmail.com>
 */

defined('_JEXEC') or die('Restricted access');

use HYPERPC\Data\JSON;
use HYPERPC\ORM\Table\Table;
use HYPERPC\Elements\Element;
use HYPERPC\Elements\Manager;
use HYPERPC\Elements\ElementCredit;

/**
 * Class ElementOrderCredits
 *
 * @since   2.0
 */
class ElementOrderCredits extends Element
{

    /**
     * Render action.
     *
     * @param   array $params
     *
     * @return  null|string
     *
     * @throws \Exception
     *
     * @since   2.0
     */
    public function render(array $params = [])
    {
        if ($layout = $this->getLayout('default')) {
            $this->loadAssets();
            return $this->_renderLayout($layout, [
                'elements' => $this->getManager()->getByPosition(Manager::ELEMENT_TYPE_CREDIT)
            ]);
        }

        return null;
    }

    /**
     * Callback before on save item.
     *
     * @param   Table   $table
     * @param   bool    $return
     * @param   bool    $isNew
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function onAfterSaveItem(Table &$table, &$return, $isNew)
    {
        if ($isNew) {
            $elementsData = new JSON($table->elements);
            $elementType  = $elementsData->find($this->_type . '.value');

            $config  = new JSON((array) $this->hyper['params']->get('credit'));
            $eConfig = (array) $config->find($elementType);

            $eConfig['table'] = $table;

            $element = $this->getManager()->create($elementType, 'credit', $eConfig);

            if ($element instanceof ElementCredit) {
                $return = $element->notify();
            }
        }
    }
}
