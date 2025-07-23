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
 * @author      Roman Evsyukov
 */

use HYPERPC\Data\JSON;
use HYPERPC\Joomla\Model\Entity\Position;
use HYPERPC\Joomla\Controller\ControllerForm;

defined('_JEXEC') or die('Restricted access');

/**
 * Class HyperPcControllerPosition
 *
 * @since   2.0
 */
class HyperPcControllerPosition extends ControllerForm
{

    const CONTROL_TYPE_PARTS            = 'parts';
    const CONTROL_TYPE_MINI             = 'mini';
    const CONTROL_TYPE_DEFAULT          = 'default';
    const CONTROL_TYPE_DEFAULT_PART_OPT = 'default_options';

    /**
     * Ajax change sorting action.
     *
     * @return  void
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function changeSorting()
    {
        $this->hyper['cms']->setHeader('Content-Type', 'application/json');

        /** @var HyperPcModelPosition $model */
        $model    = $this->getModel();
        $table    = $model->getTable();
        $id       = $this->hyper['input']->getInt('id');
        $ordering = $this->hyper['input']->getInt('ordering');
        $output   = new JSON(['result' => false]);

        /** @var Position $position */
        $position = $model->getItem($id);
        if ($position->id !== 0) {
            $position->set('ordering', $ordering);
            if ($table->save($position->getArray()) === true) {
                $output->set('result', true);
            }
        }

        $this->hyper['cms']->close($output->write());
    }

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
            ->registerTask('change-sorting', 'changeSorting')
            ->registerTask('get-example-data', 'getExampleData');
    }

    /**
     * Get example data from product position for related computer fields
     *
     * @return  void
     *
     * @since   2.0
     */
    public function getExampleData()
    {
        $this->hyper['cms']->setHeader('Content-Type', 'application/json');

        $db     = $this->hyper['db'];
        $return = [
            self::CONTROL_TYPE_PARTS            => [],
            self::CONTROL_TYPE_MINI             => [],
        ];

        $item = $this->hyper['input']->get('id');
        list(, $partId) = explode('-', $item);


        $conditions[]  = $db->qn('a.state') . ' = ' . $db->q(HP_STATUS_PUBLISHED);
        $productHelper = $this->hyper['helper']['moyskladProduct'];

        $products = $productHelper->findAll([
            'conditions' => [$conditions]
        ]);

        foreach ($products as $product) {
            $configurationParts   = (array) $product->configuration->get('parts');
            $configurationOptions = (array) $product->configuration->get('options');
            $optionsMini          = (array) $product->configuration->get('options_mini');
            $partsMini            = (array) $product->configuration->get('parts_mini');

            if (!in_array($product->id, $return[self::CONTROL_TYPE_PARTS])) {
                if (isset($option) && array_key_exists($option, $configurationOptions)) {
                    $return[self::CONTROL_TYPE_PARTS][] = $product->id;
                }
                if (array_key_exists($partId, $configurationParts)) {
                    $return[self::CONTROL_TYPE_PARTS][] = $product->id;
                }
            }

            if (isset($option) && in_array((string) $partId, $optionsMini)) {
                $return[self::CONTROL_TYPE_MINI][] = $product->id;
            }

            if (in_array((string) $partId, $partsMini)) {
                $return[self::CONTROL_TYPE_MINI][] = $product->id;
            }
        }

        $output = new JSON([
            'result' => true,
            'data'   => $return,
        ]);

        $this->hyper['cms']->close($output->write());
    }
}
