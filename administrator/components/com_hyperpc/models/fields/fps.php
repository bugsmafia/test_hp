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
 * @author      Artem Vyshnevskiy
 */

use HYPERPC\Joomla\Form\FormField;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die('Restricted access');

/**
 * Class JFormFieldFps
 *
 * @since   2.0
 */
class JFormFieldFps extends FormField
{
    const TYPE_CPU_FACTOR = 'cpu-factor';
    const TYPE_SLI_FACTOR = 'sli-factor';

    const TYPE_GRAPHIC_CORE = 'graphic-core';
    const TYPE_GRAPHIC_CORE_VALUES = 'graphic-core-value';

    const SIZES = [
        'fullhd' => 'FullHD',
        'qhd'    => 'QHD',
        '4k'     => '4K'
    ];

    const QUALITY = [
        'ultra' => 'Ultra'
    ];

    /**
     * Name of the layout being used to render the field.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $layout = 'joomla.form.field.fps';

    /**
     * The form field type.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $type = 'Fps';

    /**
     * Method to get the field input markup.
     *
     * @return  string
     *
     * @since   2.0
     */
    protected function getInput()
    {
        $this->hyper['wa']->usePreset('fields.fps');

        $this->hyper['helper']['assets']
            ->widget('.hp-fps', 'HyperPC.FieldFps', [
                'inputName'     => $this->name,
                'typeCpuFactor' => self::TYPE_CPU_FACTOR
            ]);

        Text::script('JGLOBAL_CONFIRM_DELETE', true);

        return parent::getInput();
    }

    /**
     * Method to get the data to be passed to the layout for rendering.
     *
     * @return  array
     *
     * @since   2.0
     */
    protected function getLayoutData()
    {
        return array_merge(['cpuFactors' => $this->_getCpuFactors()], parent::getLayoutData());
    }

    /**
     * Get cpu factor data list
     *
     * @return  array<array{name: string, value: int}>
     */
    protected function _getCpuFactors()
    {
        $cpuFactorList = $this->value[self::TYPE_CPU_FACTOR] ?? [];

        $parts = $this->hyper['helper']['moyskladPart']->getByItemKeys(\array_keys($cpuFactorList));

        $result = [];
        foreach ($parts as $part) {
            $itemKey = $part->getItemKey();
            $result[$itemKey] = [
                'name' => $part->getName(),
                'value' => (int) $cpuFactorList[$itemKey]
            ];
        }

        $unavailableParts = \array_diff_key($cpuFactorList, $result);
        foreach ($unavailableParts as $itemKey => $value) {
            $result[$itemKey] = [
                'name' => Text::_('JUNDEFINED'),
                'value' => (int) $cpuFactorList[$itemKey]
            ];
        }

        if (count($result) > 1) {
            uasort($result, function ($item1, $item2) {
                return $item1['value'] <=> $item2['value'];
            });
        }

        return $result;
    }
}
