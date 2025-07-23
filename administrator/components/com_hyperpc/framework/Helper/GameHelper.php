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

namespace HYPERPC\Helper;

use HYPERPC\ORM\Table\Table;
use HYPERPC\Helper\Context\EntityContext;
use Joomla\CMS\Factory;

/**
 * Class GameHelper
 *
 * @package HYPERPC\Helper
 *
 * @since   2.0
 */
class GameHelper extends EntityContext
{

    /**
     * Hold result data from $this->findAll().
     *
     * @var     array
     *
     * @since   2.0
     */
    protected static $_games;

    /**
     * Hold result data for fps table.
     *
     * @var     array
     *
     * @since   2.0
     */
    protected static $_defaultGames;

    /**
     * Initialize helper.
     *
     * @return  void
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function initialize()
    {
        $table = Table::getInstance('Games');
        $this->setTable($table);

        parent::initialize();

        $db = $this->hyper['db'];
        self::$_games ??= $this->findAll([
            'conditions' => [
                $db->quoteName('a.published') . ' = ' . HP_STATUS_PUBLISHED . ' OR ' . $db->quoteName('a.default_game') . ' = ' . HP_STATUS_PUBLISHED
            ],
            'order' => $db->quoteName('a.ordering') . ' ASC'
        ]);

        self::$_defaultGames ??= $this->findAll([
            'conditions' => [$db->quoteName('a.default_game') . ' = ' . HP_STATUS_PUBLISHED],
            'order' => $db->quoteName('a.ordering') . ' ASC'
        ]);
    }

    /**
     * Hold games
     *
     * @return array
     *
     * @since 2.0
     */
    public function getGames()
    {
        return self::$_games;
    }

    /**
     * Hold default games
     *
     * @return array
     *
     * @since 2.0
     */
    public function getDefaultGames()
    {
        return self::$_defaultGames;
    }

    /**
     * Set game selected in plugin to defaults
     *
     * @param $game
     *
     * @since 2.0
     */
    public function setDefaultGame($game)
    {
        foreach (self::$_games as $_game) {
            if ($_game->alias === $game && (int) $_game->default_game === HP_STATUS_UNPUBLISHED) {
                self::$_defaultGames[] = $_game;
            }
        }
    }

    /**
     * Get gpu data to calculate fps of product
     *
     * @param   $part
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getGpuData($gpuPart)
    {
        $data = [
            'gpuBoost' => 1,
            'fpsValues' => []
        ];

        $fieldContext = 'position';
        $fieldId = $this->hyper['params']->get('fps_gpu_field_id', 0, 'int');
        $field = current($this->hyper['helper']['fields']->getFieldsById((array) $fieldId, false, $fieldContext));

        if (!$field || !in_array($gpuPart->product_folder_id, $field->category_ids)) {
            return $data;
        }

        $data['part'] = $gpuPart;

        // Options gpu boost are not used
        // if (isset($data['part']->option) && $data['part']->option !== null) {
        //     if ($data['part']->option->params->get('fps_factor')) {
        //         $data['gpuBoost'] += $data['part']->option->params->get('fps_factor') / 100;
        //     }
        // }

        $gpuCoreName = $data['part']->getFieldValueById($fieldId);
        $gpuCoreValue = null;
        foreach ($field->fieldparams->get('options') as $fieldParam) {
            if ($fieldParam['name'] === $gpuCoreName) {
                $gpuCoreValue = $fieldParam['value'];
                break;
            }
        }

        foreach (self::$_games as $game) {
            $gameFps = (array) $game->params->get('fps');

            if (!array_key_exists('graphic-core-value', $gameFps)) {
                continue;
            }

            $gpuItems = $gameFps['graphic-core-value'];

            foreach ($gpuItems as $gpuAlias => $gpuItem) {
                if ($gpuCoreValue !== (string) $gpuAlias) {
                    continue;
                }

                $data['fpsValues'][$game->alias] = [
                    'ultra' => $gpuItem['ultra']
                ];
            }
        }

        return $data;
    }

    /**
     * Get processors.
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getProcessors()
    {
        $processors = [];

        foreach (self::$_games as $game) {
            $gameFps = $game->params->get('fps', [], 'arr');

            $cpuFactorKey = 'cpu-factor'; /** @todo change to constant */

            if (!key_exists($cpuFactorKey, $gameFps)) {
                continue;
            }

            $cpuFactor = (array) $gameFps[$cpuFactorKey];

            foreach ($cpuFactor as $itemKey => $value) {
                $processors[$itemKey][$game->alias] = (int) $value;
            }
        }

        return $processors;
    }

    /**
     * Get min ind max FPS values.
     *
     * @param   string $prop
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getFpsLimits(string $prop = 'graphic-core-value')
    {
        $limits = [];
        foreach (self::$_games as $game) {
            $minFps = [
                'ultra' => [
                    'fullhd' => 0,
                    'qhd'    => 0,
                    '4k'     => 0
                ]
            ];
            $maxFps = $minFps;

            $cards = $game->params->find('fps.' . $prop);

            if (!$cards) {
                $limits[$game->alias] = ['minFps' => $minFps, 'maxFps' => $maxFps];
                continue;
            }

            foreach ($cards as $card) {
                foreach ($card['ultra'] as $resolution => $fps) {
                    if ($fps === '') {
                        continue;
                    }

                    $minFps['ultra'][$resolution] =
                        $minFps['ultra'][$resolution] === 0 ?
                            intval($card['ultra'][$resolution]) :
                            min($minFps['ultra'][$resolution], intval($card['ultra'][$resolution]));

                    $maxFps['ultra'][$resolution] = max($maxFps['ultra'][$resolution], intval($card['ultra'][$resolution]));
                }
            }

            $limits[$game->alias] = ['minFps' => $minFps, 'maxFps' => $maxFps];
        }

        return $limits;
    }

    /**
     * Set games data to script options for using via js.
     */
    public function setJsGamesData()
    {
        static $alreadySet = false;

        if ($alreadySet) {
            return;
        }

        $gamesData = [];
        foreach (self::$_games as $game) {
            $gamesData[$game->alias] = [
                'name' => $game->name
            ];
        }

        $fpsHelper = $this->hyper['helper']['fps'];

        Factory::getApplication()->getDocument()->addScriptOptions('fps', [
            'article' => $this->hyper['params']->get('fps_info_article'),
            'topLimit' => $fpsHelper->getFpsTopLimit(),
            'games' => $gamesData,
            'resolutions' => $fpsHelper->getResolutions(),
        ]);

        $alreadySet = true;
    }
}
