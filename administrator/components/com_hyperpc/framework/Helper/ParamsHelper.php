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

namespace HYPERPC\Helper;

use JBZoo\Data\Data;

/**
 * Class ParamsHelper
 *
 * @package HYPERPC\Helper
 *
 * @since   2.0
 */
class ParamsHelper extends AppHelper
{

    /**
     * Get company phones from app params.
     *
     * @return  Data[]
     *
     * @since   2.0
     */
    public function getCompanyPhones()
    {
        $phonesParam = trim($this->hyper['params']->get('site_phones', ''));
        if (empty($phonesParam)) {
            return [new Data()];
        }

        $result = [];
        $phones = explode(',', $phonesParam);
        foreach ($phones as $phone) {
            $raw = preg_replace('/[^0-9\+]/', '', $phone);
            $result[] = new Data([
                'value'    => trim($phone),
                'rawvalue' => $raw
            ]);
        }

        return $result;
    }

    /**
     * Get hyperbox dimensions
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getHyperboxDimensionsByType($boxType)
    {
        $result = [
            'weight' => 13.0,
            'dimensions' => [
                'length' => 62,
                'width'  => 65,
                'height' => 42
            ]
        ];

        $types = $this->hyper['params']->get('hyperbox_types', []);
        if (array_key_exists($boxType, $types)) {
            $result['weight'] = (double) $types[$boxType]['weight'];
            $result['dimensions'] = [
                'length' => (int) $types[$boxType]['length'],
                'width'  => (int) $types[$boxType]['width'],
                'height' => (int) $types[$boxType]['height']
            ];
        }

        return $result;
    }
}
