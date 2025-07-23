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

use JBZoo\Utils\Str;
use HYPERPC\Data\JSON;
use HYPERPC\Elements\Element;

/**
 * Class ElementCoreList
 *
 * @since   2.0
 */
class ElementCoreList extends Element
{

    public function render(array $params = [])
    {
        $params = new JSON($params);
        $layout = $params->get('layout');

        if (!$layout) {
            $layout = $this->_config->get('layout', 'default');
        }

        if ($layout = $this->getLayout($layout)) {
            $this->loadAssets();
            return $this->_renderLayout($layout, [
                'params'   => $params,
                'options'  => $this->_getOptions(),
                'default'  => $this->_config->get('default'),
                'multiple' => $this->_config->get('multiple', false, 'bool')
            ]);
        }

        return null;
    }

    protected function _getOptions()
    {
        $options = [];
        $values  = explode(PHP_EOL, (string) $this->_config->get('values'));
        foreach ($values as $value) {
            $title = $value;
            $val   = Str::slug($value);
            if (preg_match('/=/', $value)) {
                list ($val, $title) = explode('=', $value);
            }

            $val = !empty($val) ? trim($val) : $val;

            $options[$val] = [
                'value' => $val,
                'text'  => $title
            ];
        }

        return $options;
    }
}
