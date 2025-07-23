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

namespace HYPERPC\Joomla\Model\Entity;

use JBZoo\Data\Data;
use JBZoo\Data\JSON;
use JBZoo\Utils\Str;
use Joomla\CMS\Date\Date;

/**
 * Class Field
 *
 * @package     HYPERPC\Joomla\Model\Entity
 *
 * @since       2.0
 */
class Field extends Entity
{

    /**
     * Id.
     *
     * @var     int
     *
     * @since   2.0
     */
    public $id;

    /**
     * Category ids.
     *
     * @var     int[]
     *
     * @since   2.0
     */
    public $category_ids;

    /**
     * Field asset id.
     *
     * @var     int
     *
     * @since   2.0
     */
    public $asset_id;

    /**
     * Context.
     *
     * @var     string
     *
     * @since   2.0
     */
    public $context;

    /**
     * Field group id.
     *
     * @var     int
     *
     * @since   2.0
     */
    public $group_id;

    /**
     * Field title.
     *
     * @var     int
     *
     * @since   2.0
     */
    public $title;

    /**
     * Field name.
     *
     * @var     int
     *
     * @since   2.0
     */
    public $name;

    /**
     * Field label.
     *
     * @var     string
     *
     * @since   2.0
     */
    public $label;

    /**
     * Default value.
     *
     * @var     string
     *
     * @since   2.0
     */
    public $default_value;

    /**
     * Field type.
     *
     * @var     string
     *
     * @since   2.0
     */
    public $type;

    /**
     * Field none.
     *
     * @var     string
     *
     * @since   2.0
     */
    public $note;

    /**
     * Field description.
     *
     * @var     string
     *
     * @since   2.0
     */
    public $description;

    /**
     * Published flag.
     *
     * @var     bool
     *
     * @since   2.0
     */
    public $state;

    /**
     * Required flag.
     *
     * @var     int
     *
     * @since   2.0
     */
    public $required;

    /**
     * Checked out
     *
     * @var     int
     *
     * @since   2.0
     */
    public $checked_out;

    /**
     * Checked out time.
     *
     * @var     Date
     *
     * @since   2.0
     */
    public $checked_out_time;

    /**
     * Ordering.
     *
     * @var     int
     *
     * @since   2.0
     */
    public $ordering;

    /**
     * Params.
     *
     * @var     int
     *
     * @since   2.0
     */
    public $params;

    /**
     * Field params.
     *
     * @var     JSON
     *
     * @since   2.0
     */
    public $fieldparams;

    /**
     * Language.
     *
     * @var     string
     *
     * @since   2.0
     */
    public $language;

    /**
     * Created time.
     *
     * @var     Date
     *
     * @since   2.0
     */
    public $created_time;

    /**
     * Create user id.
     *
     * @var     int
     *
     * @since   2.0
     */
    public $created_user_id;

    /**
     * Modified time.
     *
     * @var     Date
     *
     * @since   2.0
     */
    public $modified_time;

    /**
     * Modified user id.
     *
     * @var     int
     *
     * @since   2.0
     */
    public $modified_by;

    /**
     * Field access.
     *
     * @var     int
     *
     * @since   2.0
     */
    public $access;

    /**
     * Field entity id.
     *
     * @var     int
     *
     * @since   2.0
     */
    public $item_id;

    /**
     * Field value.
     *
     * @var     string
     *
     * @since   2.0
     */
    public $value;

    /**
     * Field types has options.
     *
     * @var     array
     *
     * @since   2.0
     */
    protected $_fieldsHasOptions = ['list'];

    /**
     * Prepare entity properties.
     *
     * @param   mixed $propName
     * @param   mixed $propValue
     * @return  void
     *
     * @since   2.0
     */
    protected function _prepareData($propName, $propValue)
    {
        parent::_prepareData($propName, $propValue);

        if ($propName === 'category_ids') {
            $this->category_ids = [];
            foreach (explode(',', $propValue) as $value) {
                $this->category_ids[] = (int) $value;
            }
        }
    }

    /**
     * Get field option.
     *
     * @param   bool      $hideNone         Flag of hide none value.
     * @param   string    $hideNoneVal      Value key of hide none value.
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getFieldOption($hideNone = true, $hideNoneVal = 'none')
    {
        if (!in_array($this->type, $this->_fieldsHasOptions)) {
            return [];
        }

        $options = (array) $this->fieldparams->get('options');
        foreach ($options as $i => $data) {
            $options[$i] = new Data($data);
            if (@$data['value'] === $hideNoneVal && $hideNone === true) {
                unset($options[$i]);
            }
        }

        return $options;
    }

    /**
     * Get current field value.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getValue()
    {
        if (in_array($this->type, ['list'])) {
            $options = $this->fieldparams->get('options');
            foreach ($options as $data) {
                $data = new Data($data);
                if ($this->value === $data->get('value')) {
                    return $data->get('name');
                }
            }
        }

        return $this->value;
    }

    /**
     * Get icon by alias.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getIcon()
    {
        $name = Str::low($this->name);
        if (in_array($name, ['videocarta', 'videokarta'])) {
            return 'hp-graphics-cards';
        } elseif (in_array($name, ['diagonal-ekrana'])) {
            return 'hp-display-size';
        } elseif (in_array($name, ['razreshenie-ekrana'])) {
            return 'hp-resolution';
        } elseif (in_array($name, ['protsessor'])) {
            return 'hp-processors';
        } elseif (in_array($name, ['operativnaya-pamyat'])) {
            return 'hp-memory';
        } elseif (in_array($name, ['zhestkij-disk'])) {
            return 'hp-hdd';
        } elseif (in_array($name, ['tverdotelnyj-nakopitel'])) {
            return 'hp-ssd';
        } elseif (in_array($name, ['operatsionnaya-sistema'])) {
            return 'hp-os';
        }

        return '';
    }

    /**
     * Get site view category url.
     *
     * @return  string
     * @param   array $query
     *
     * @since   2.0
     */
    public function getViewUrl(array $query = [])
    {
        return null;
    }

    /**
     * Fields of datetime.
     *
     * @return  array
     *
     * @since   2.0
     */
    protected function _getFieldDate()
    {
        return array_merge(parent::_getFieldDate(), ['checked_out_time']);
    }

    /**
     * Fields of boolean data.
     *
     * @return  array
     *
     * @since   2.0
     */
    protected function _getFieldBoolean()
    {
        return ['state', 'required'];
    }

    /**
     * Fields of JSON data.
     *
     * @return  array
     *
     * @since   2.0
     */
    protected function _getFieldJsonData()
    {
        return array_merge(parent::_getFieldJsonData(), ['fieldparams']);
    }

    /**
     * Fields of integer data.
     *
     * @return  array
     *
     * @since   2.0
     */
    protected function _getFieldInt()
    {
        return array_merge(parent::_getFieldInt(), ['asset_id', 'checked_out', 'modified_by', 'access']);
    }
}
