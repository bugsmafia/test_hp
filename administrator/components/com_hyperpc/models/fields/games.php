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
 * @author      Roman Evsyukov <roman_e@hyperpc.ru>
 * @author      Artem Vyshnevskiy
 */

use HYPERPC\App;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\Field\ListField;

defined('_JEXEC') or die('Restricted access');

/**
 * Class JFormFieldGames
 *
 * @property    bool $multiple
 * @property    bool $resolutions split game options by resolutions
 *
 * @since       2.0
 */
class JFormFieldGames extends ListField
{

    /**
     * The form field type.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $type = 'Games';

    /**
     * Name of the layout being used to render the field
     *
     * @var    string
     *
     * @since  2.0
     */
    protected $layout = 'joomla.form.field.list-fancy-select';

    /**
     * Method to get the field options.
     *
     * @return  array
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    protected function getOptions()
    {
        $options = [];
        $app = App::getInstance();

        $games = $app['helper']['game']->getGames();

        if ((bool) $this->multiple !== true) {
            $options['no'] = [
                'value' => '',
                'text'  => Text::_('COM_HYPERPC_SELECT_GAME')
            ];
        }

        $splitByResolutions = isset($this->element['resolutions']) && \in_array($this->element['resolutions'], ['true', '1']);
        if (!$splitByResolutions) {
            foreach ($games as $game) {
                $options[] = [
                    'value' => $game->alias,
                    'text'  => $game->name
                ];
            }
        } else {
            $resolutions = [
                'fullhd',
                'qhd',
                '4k'
            ];

            foreach ($games as $game) {
                foreach ($resolutions as $resolution) {
                    $options[] = [
                        'value' => $game->alias . '@' . $resolution,
                        'text'  => $game->name . '@' . $resolution
                    ];
                }
            }
        }

        return \array_merge(parent::getOptions(), $options);
    }
}
