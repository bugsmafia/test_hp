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

use HYPERPC\Joomla\Model\Entity\Interfaces\ProductMarker;

/**
 * Class FpsHelper
 *
 * @package HYPERPC\Helper
 *
 * @since   2.0
 */
class FpsHelper extends AppHelper
{
    private const FPS_TOP_LIMIT = 200;

    /**
     * Calculate FPS
     *
     * @param   int $fpsVal
     * @param   int $minFps
     * @param   int $maxFps
     * @param   int $cpuFactor
     *
     * @return  int
     *
     * @since   2.0
     */
    public function calculateFps($fpsVal, $minFps, $maxFps, $cpuFactor)
    {
        $fpsVal    = intval($fpsVal);
        $minFps    = intval($minFps);
        $maxFps    = intval($maxFps);
        $cpuFactor = intval($cpuFactor);

        $fpsRange = $maxFps - $minFps;

        if ($fpsRange === 0 || $fpsVal === 0) {
            return 0;
        }

        $fps = (int) round($fpsVal + ($cpuFactor * ($fpsVal - ($minFps - $fpsRange * 0.05)) / $fpsRange));
        return $fps;
    }

    /**
     * Calculate average fps for product
     *
     * @param   array $productFps
     * @param   string $game in format game@resolution
     *
     * @return  int
     *
     * @since   2.0
     */
    public function calculateAverageFps(array $productFps, $game = '')
    {
        $productFps = array_filter($productFps);
        if (count($productFps) === 0) {
            return 0;
        }

        list($targetGame, $targetResolution) = explode('@', $game . '@');
        if (!empty($targetGame) && isset($productFps[$targetGame])) {
            $productFps = array_filter($productFps, function ($alias) use ($targetGame) {
                return $alias === $targetGame;
            }, ARRAY_FILTER_USE_KEY);
        }

        $sum = 0;
        foreach ($productFps as $gameAlias => $fps) {
            $fps = $fps['ultra']; // TODO remove this after removing the quality property
            if (!empty($targetResolution) && isset($fps[$targetResolution])) {
                $sum += $fps[$targetResolution];
            } else {
                $sum += array_sum($fps) / count($fps);
            }
        }

        $averageFps = $sum / count($productFps);

        return intval(round($averageFps));
    }

    /**
     * Get Product FPS remastered
     *
     * @param   ProductMarker $product
     * @param   string        $gameAlias
     *
     * @return  array
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getFps(ProductMarker $product, $gameAlias = null)
    {
        /** @var GameHelper $gameHelper */
        $gameHelper = $this->hyper['helper']['game'];

        $games = $gameHelper->getGames();
        if (empty($games)) {
            return [];
        }

        $cpuFolderId = $this->hyper['params']->get('cpu_folder_id', 0, 'int');
        $gpuFolderId = $this->hyper['params']->get('gpu_folder_id', 0, 'int');

        $db = $this->hyper['db'];

        $parts = $this->hyper['helper']['moyskladPart']->findAll([
            'conditions' => [
                $db->quoteName('a.product_folder_id') . ' IN (' . implode(',', [$cpuFolderId, $gpuFolderId]) . ')',
                $db->quoteName('a.id') . ' IN (' . implode(',', $product->get('configuration')->get('default', [], 'arr')) . ')'
            ],
            'key' => 'product_folder_id',
            'select' => ['a.id', 'a.product_folder_id']
        ]);

        $cpuPart = $parts[$cpuFolderId] ?? null;
        $gpuPart = $parts[$gpuFolderId] ?? null;

        if (empty($gpuPart)) {
            return [];
        }

        // Set quantity to the gpu part
        $quantitiesList = $product->get('configuration')->get('quantity');
        $gpuPart->set('quantity', $quantitiesList[$gpuPart->id] ?? 1);

        $gpuData = $gameHelper->getGpuData($gpuPart);

        $fps = [];
        if (!$gpuData) {
            foreach ($games as $game) {
                $fps[$game->alias] = null;
            }

            return $fps;
        }

        $fpsLimits  = $gameHelper->getFpsLimits('graphic-core-value');
        $processors = $gameHelper->getProcessors();
        $cpuItemKey = $cpuPart?->getItemKey();

        $enabledResolutions = $this->getResolutions();

        foreach ($games as $game) {
            if ($gameAlias && $game->alias !== $gameAlias) {
                continue;
            }

            if (!key_exists($game->alias, $gpuData['fpsValues'])) {
                $fps[$game->alias] = null;
                continue;
            }

            $sliFactor = $gpuPart->quantity > 1 ? 1 + ($game->params->find('fps.sli-factor') / 100) : 1;

            foreach ($gpuData['fpsValues'][$game->alias] as $quality => $resolutions) {
                $hasValue = false;
                foreach ($resolutions as $resolution => $value) {
                    if (!in_array($resolution, $enabledResolutions)) {
                        continue;
                    }

                    $gpuFpsVal = $value;
                    $minFps    = $fpsLimits[$game->alias]['minFps'][$quality][$resolution];
                    $maxFps    = $fpsLimits[$game->alias]['maxFps'][$quality][$resolution];

                    $cpuFactor    = $cpuPart && isset($processors[$cpuItemKey][$game->alias]) ? $processors[$cpuItemKey][$game->alias] : 0;
                    $fpsCalculate = $this->calculateFps($gpuFpsVal, $minFps, $maxFps, $cpuFactor);
                    $fpsValue     = round($fpsCalculate * $gpuData['gpuBoost'] * $sliFactor);
                    $fps[$game->alias][$quality][$resolution] = $fpsValue;
                    if ($fpsValue > 0) {
                        $hasValue = true;
                    }
                }

                if (!$hasValue) {
                    $fps[$game->alias] = null;
                }
            }

            if ($gameAlias && $game->alias === $gameAlias) {
                break; // break the loop if only a certain game is needed
            }

        }

        return $fps;
    }

    /**
     * Get available resolutions
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getResolutions()
    {
        return (array) $this->hyper['params']->get('fps_resolutions');
    }

    /**
     * Get fps top limit
     *
     * @return  int
     *
     * @since   2.0
     */
    public function getFpsTopLimit()
    {
        return self::FPS_TOP_LIMIT;
    }

    /**
     * Should fps be shown
     *
     * @param   int|string $categoryId
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function showFps($categoryId)
    {
        if (empty($this->getResolutions())) {
            return false;
        }

        $fpsCategories = (array) $this->hyper['params']->get('fps_folders');
        if (!in_array((string) $categoryId, $fpsCategories)) {
            return false;
        }

        return !empty($this->hyper['helper']['game']->getGames());
    }
}
