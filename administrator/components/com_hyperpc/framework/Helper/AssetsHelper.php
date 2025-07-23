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

namespace HYPERPC\Helper;

use HYPERPC\Helper\Traits\LoadAssets;
use JBZoo\Assets\Asset\AbstractAsset as Asset;

/**
 * Class AssetsHelper
 *
 * @package HYPERPC\Helper
 *
 * @since   2.0
 */
class AssetsHelper extends AppHelper
{
    use LoadAssets;

    /**
     * Add js script in to the document.
     *
     * @param   string  $script
     * @param   bool    $docReady
     *
     * @return  $this
     *
     * @since   2.0
     */
    public function addScript($script, $docReady = true)
    {
        $script = trim(trim($script), ';') . ';';
        $uniqId = 'asset-' . md5($script);

        if ($docReady) {
            $script = "\tjQuery(function($){ " . $script . " });\n";
        } else {
            $script = "\t" . $script . "\n";
        }

        $this->hyper['assets']->add($uniqId, $script, [], ['type' => Asset::TYPE_JS_CODE]);
        return $this;
    }

    /**
     * Add file to Assets manager and include in to the document.
     *
     * @param   string      $source
     * @param   null|string $alias
     *
     * @return  $this
     *
     * @since   2.0
     */
    public function js($source, $alias = null)
    {
        if ($alias === null) {
            $alias = 'asset-' . md5($source);
        }

        $this->hyper['assets']->add($alias, $source);
        return $this;
    }

    /**
     * Initialize java script widget.
     *
     * @param   string          $selector
     * @param   string          $widgetName
     * @param   array|object    $params
     * @param   bool            $return
     *
     * @return  null|string
     *
     * @since   2.0
     */
    public function widget($selector, $widgetName, $params = [], $return = false)
    {
        static $included = [];

        $jquerySelector = is_array($selector) ? implode(', ', $selector) : $selector;
        $hash = implode('///', [$jquerySelector, $widgetName, (int) $return]);

        if (!array_key_exists($hash, $included)) {
            $included[$hash] = true;
            $widgetName = str_replace('.', '', $widgetName);

            if (is_object($params)) {
                $params = (array) $params;
            }

            $jsonParams = (count($params) > 0) ? json_encode($params) : '{}';
            $initScript = '$("' . $jquerySelector . '").' . $widgetName . '(' . $jsonParams . ');';

            if ($return) {
                return implode(PHP_EOL, [
                    '<script type="text/javascript">',
                    "\tjQuery(function($){ setTimeout(function(){" . $initScript . "}, 0); });",
                    '</script>'
                ]);
            }

            $this->addScript($initScript);
        }

        return null;
    }

    /**
     * Include jquery raty.
     *
     * @return $this
     *
     * @since   2.0
     */
    public function jqueryRaty()
    {
        $this->js('js:libs/jquery-raty.min.js');
        $this->hyper['assets']->add(__FUNCTION__, 'css:libs/raty.css');
        return $this;
    }

    /**
     * Include product teaser form modal js widget.
     *
     * @param  string $selector
     *
     * @return $this
     *
     * @since   2.0
     */
    public function productTeaserModalButton($selector)
    {
        $this->js('js:widget/site/product-teaser-form.js')
             ->widget($selector, 'HyperPC.ProductTeaserForm', []);

        return $this;
    }
}
