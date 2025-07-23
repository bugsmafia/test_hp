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

use HYPERPC\Data\JSON;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;

/**
 * Class Field.
 *
 * @property    int             $id
 * @property    string          $title
 * @property    string          $name
 * @property    int             $checked_out
 * @property    Date            $checked_out_time
 * @property    string          $note
 * @property    string          $state
 * @property    int             $access
 * @property    Date            $created_time
 * @property    int             $created_user_id
 * @property    int             $ordering
 * @property    string          $language
 * @property    JSON            $fieldparams
 * @property    JSON            $params
 * @property    string          $type
 * @property    string          $default_value
 * @property    string          $context
 * @property    int             $group_id
 * @property    string          $label
 * @property    string          $description
 * @property    string          $required
 * @property    string|null     $language_title
 * @property    string|null     $language_image
 * @property    string|null     $editor
 * @property    string|null     $access_level
 * @property    string|null     $author_name
 * @property    string|null     $group_title
 * @property    string|null     $group_access
 * @property    string|null     $group_state
 * @property    string|null     $group_note
 * @property    string          $value
 * @property    string          $rawvalue
 *
 * @package     HYPERPC\ORM\Entity
 *
 * @since       2.0
 */
class Field extends Entity
{

    /**
     * Field list of json type.
     *
     * @var     array
     *
     * @since   2.0
     */
    protected $_fieldJsonType = [
        'params',
        'fieldparams'
    ];

    /**
     * Get admin (backend) edit url.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getAdminEditUrl()
    {
        return '';
    }

    /**
     * Setup table instance.
     *
     * @return  $this
     *
     * @since   2.0
     */
    protected function _setTable()
    {
        $this->_table = Factory::getApplication()->bootComponent('com_fields')->getMVCFactory()->createTable('Field');

        return $this;
    }
}
