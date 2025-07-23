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

namespace HYPERPC\ORM\Entity;

use Exception;
use HYPERPC\App;
use HYPERPC\Data\JSON;
use HYPERPC\Helper\StoreHelper;
use Joomla\CMS\Language\LanguageHelper;

/**
 * Store class.
 *
 * @property    int         $id
 * @property    string      $name
 * @property    string      $geoid
 * @property    JSON        $params
 * @property    StoreHelper $helper
 *
 * @package     HYPERPC\ORM\Entity
 *
 * @since       2.0
 */
class Store extends Entity
{

    /**
     * Get admin (backend) edit url.
     *
     * @return  null
     *
     * @since   2.0
     */
    public function getAdminEditUrl()
    {
        return $this->hyper['route']->build([
            'layout' => 'edit',
            'view'   => 'store',
            'id'     => $this->id
        ]);
    }

    /**
     * Initialize hook method.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function initialize()
    {
        $this
            ->setTablePrefix()
            ->setTableType('Stores');

        parent::initialize();
    }

    /**
     * Get store item.
     *
     * @param   int     $itemId
     * @param   int     $optionId
     * @param   string  $context
     * @param   bool    $multiply
     *
     * @return  array|Entity|StoreItem
     *
     * @since   2.0
     */
    public function getItem($itemId, $optionId = 0, $context = StoreHelper::CONTEXT_PART, $multiply = false)
    {
        return $this->hyper['helper']['store']->getItem($this->id, $itemId, $optionId, $context, $multiply);
    }

    /**
     * Get merged param
     *
     * @param   string $key
     * @param   mixed  $default
     *
     * @return  JSON
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public function getParam(string $key, $default)
    {
        $app   = App::getInstance();
        $value = $this->params->get($key);

        $contentLangs = LanguageHelper::getContentLanguages();
        $langSefs = array_map(function ($langData) {
            return $langData->sef;
        }, $contentLangs);

        $langSef = $app->getLanguageSef();

        if (is_array($value)) {
            if (array_key_exists($langSef, $value)) {
                $value = $value[$langSef];
            } elseif (array_intersect($langSefs, array_keys($value))) {
                $value = $value[$langSef] ?? '';
            }
        }

        return !empty($value) ? $value : $default;
    }
}
