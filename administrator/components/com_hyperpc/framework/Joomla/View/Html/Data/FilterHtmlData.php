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

namespace HYPERPC\Joomla\View\Html\Data;

use HYPERPC\App;
use HYPERPC\Data\JSON;

defined('_JEXEC') or die('Restricted access');

/**
 * Class FilterHtmlData
 *
 * @package HYPERPC\Joomla\View\Html\Data
 *
 * @since   2.0
 */
class FilterHtmlData extends HtmlData
{

    const QUERY_DATA_SEPARATOR  = '|';
    const UK_STICKY_OFFSET_EPIX = 79;
    const UK_STICKY_OFFSET_HP   = 51;

    /**
     * Get allowed filter fields.
     *
     * @return  array|bool
     *
     * @since   2.0
     */
    public function getAllowedFilterFields()
    {
        $filterHelper = $this->filter->getFilterHelper();

        return $filterHelper->getAllowedFields();
    }

    /**
     * Get input data by key name.
     *
     * @param   string  $keyName
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getInput($keyName)
    {
        $value = $this->hyper['input']->get($keyName, '', 'string');
        if (preg_match('/\\' . self::QUERY_DATA_SEPARATOR . '/', $value)) {
            return explode(self::QUERY_DATA_SEPARATOR, $value);
        }

        if (empty($value)) {
            return [];
        }

        return (array) $value;
    }

    /**
     * Get UIKit sticky offset value.
     *
     * @return  int
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public static function getUiKitStickyOffset()
    {
        $offset   = self::UK_STICKY_OFFSET_HP;
        $app      = App::getInstance();
        $isMobile = $app['detect']->isMobile();

        if ($app->isSiteContext(HP_CONTEXT_EPIX) && !$isMobile) {
            $offset = self::UK_STICKY_OFFSET_EPIX;
        }

        return $offset;
    }

    /**
     * Get url query filter data.
     *
     * @return  JSON
     *
     * @since   2.0
     */
    public function getUrlQueryData()
    {
        $filterHelper = $this->filter->getFilterHelper();

        $filterData = new JSON();
        $fieldIndex = $filterHelper->getProductUrlQueryAllowedAliasList();

        foreach ($fieldIndex as $alias) {
            $value = $this->hyper['input']->get($alias, null, 'string');
            if ($value !== null) {
                if (preg_match('/\\' . self::QUERY_DATA_SEPARATOR . '/', $value)) {
                    $filterData->set($alias, explode(self::QUERY_DATA_SEPARATOR, $value));
                } else {
                    $filterData->set($alias, $value);
                }
            }
        }

        return $filterData;
    }
}
