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
use HYPERPC\Helper\MoyskladProductHelper;
use HYPERPC\Joomla\Controller\ControllerLegacy;
use HYPERPC\Joomla\Model\Entity\Interfaces\ProductMarker;

/**
 * Class HyperPcControllerProduct
 *
 * @since 2.0
 */
class HyperPcControllerMoysklad_Product extends ControllerLegacy
{

    const PRODUCT_RECOUNT_METHOD_OPTION = 'toggle-option';
    const PRODUCT_RECOUNT_METHOD_PART   = 'toggle-part';

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
        $this->registerTask('recount-price', 'recountPrice');
    }

    /**
     * Recount product price.
     *
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function recountPrice()
    {
        /** @var MoyskladProductHelper $productHelper */
        $productHelper = $this->hyper['helper']['moyskladProduct'];

        /** @var ProductMarker $product */
        $product = $productHelper->getById($this->hyper['input']->get('product-id'));
        $output  = new JSON(['result' => false, 'price' => 0]);
        $method  = $this->hyper['input']->get('method', self::PRODUCT_RECOUNT_METHOD_OPTION);

        if ($product->id) {
            $defaultParts      = $product->configuration->get('default');
            $defaultOptionList = $product->configuration->get('option');
            $configPartOptions = $product->configuration->get('part_options');

            if ($method === self::PRODUCT_RECOUNT_METHOD_OPTION) {
                $optionId = $this->hyper['input']->get('option-id');
                $partId   = (int) $this->hyper['input']->get('part-id');

                $defaultOptionId   = 0;
                if (array_key_exists($partId, $defaultOptionList)) {
                    $defaultOptionId = (int) $defaultOptionList[$partId];
                    $defaultOptionList[$partId] = $optionId;
                }

                if (array_key_exists($defaultOptionId, $configPartOptions)) {
                    $optionData = $configPartOptions[$defaultOptionId];
                    unset($configPartOptions[$defaultOptionId]);
                    $configPartOptions[(int) $optionId] = $optionData;
                }
            } elseif ($method === self::PRODUCT_RECOUNT_METHOD_PART) {
                $args = (array) $this->hyper['input']->get('args', [], 'array');
                foreach ($args as $data) {
                    $data      = new JSON($data);
                    $optionId  = $data->get('option_id');
                    $partId    = $data->get('part_id', 0, 'int');
                    $defPartId = $data->find('default.part', 0, 'int');
                    $defOptId  = $data->find('default.option', 0, 'int');

                    if ($optionId && isset($defaultOptionList[$defPartId]) && isset($configPartOptions[$defOptId])) {
                        unset($defaultOptionList[$defPartId]);
                        $defaultOptionList[$partId] = $optionId;

                        $configPartOptions[$defOptId]['is_default'] = false;

                        $configPartOptions[$optionId] = [
                            'is_default' => true,
                            'part_id'    => (string) $partId
                        ];
                    } elseif (!$optionId) {
                        unset(
                            $defaultOptionList[$defPartId],
                            $configPartOptions[$defOptId]
                        );

                        foreach ((array) $defaultParts as $i => $defaultPart) {
                            if ($defaultPart === (string) $defPartId) {
                                unset($defaultParts[$i]);
                            }
                        }

                        if (!in_array((string) $partId, $defaultParts)) {
                            $defaultParts[] = (string) $partId;
                        }
                    }
                }
            }

            $product->configuration
                ->set('default', $defaultParts)
                ->set('option', $defaultOptionList)
                ->set('part_options', $configPartOptions);

            $price = $productHelper->countPrice($product);

            $productPrice = $product->list_price->set($price->val());

            $output
                ->set('result', true)
                ->set('price', $product->getConfigPrice(true)->val())
                ->set('monthly', $this->hyper['helper']['credit']->getMonthlyPayment($productPrice->val())->val());
        }

        echo $output->write();
        $this->hyper['cms']->close();
    }
}
