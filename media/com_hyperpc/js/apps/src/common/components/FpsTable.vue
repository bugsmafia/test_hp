<!--
HYPERPC - The shop of powerful computers.

This file is part of the HYPERPC package.
For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.

@package    HYPERPC
@license    Proprietary
@copyright  Proprietary https://hyperpc.ru/license
@link       https://github.com/HYPER-PC/HYPERPC".

@author     Artem Vyshnevskiy
@author     Sergey Voronin
-->

<template>
    <table class="uk-table uk-table-small uk-table-justify uk-table-divider uk-table-middle tm-fps-table" data-uk-scrollspy="target: .tm-fps-table__bar; delay: 33; repeat: false">
        <thead>
            <tr>
                <th class="uk-visible@s uk-table-shrink"></th>
                <td class="uk-table-expand uk-padding-remove-left">
                    <div class="uk-grid">
                        <div v-for="(resolution, index) in fps.resolutions" :key="index" class="uk-flex uk-flex-middle">
                            <span :class="'tm-fps-table__color-sample tm-fps-table__color-sample--' + resolution" class="uk-badge uk-margin-small-right"></span>
                            <span>{{ resolutionsText[resolution] }}</span>
                        </div>
                    </div>
                </td>
            </tr>
        </thead>

        <tbody>
            <tr v-for="(gameData, gameKey) in transformedProductFps" :key="gameKey">
                <td class="uk-text-right uk-text-nowrap uk-visible@s">{{ gameData.name }}</td>
                <td class="tm-fps-table__fps-cell" :style="{ backgroundSize: axisStep }">
                    <div class="uk-hidden@s">{{ gameData.name }}</div>
                    <div v-for="(resolution, index) in fps.resolutions" :key="index" :class="'tm-fps-table__bar tm-fps-table__bar--' + resolution" :style="{ width: calcPercentValue(gameData[resolution]) }">
                        <span class="tm-fps-table__fps-value">
                          {{ gameData[resolution] !== undefined ? gameData[resolution] : '-' }}
                        </span>
                    </div>
                </td>
            </tr>
        </tbody>

        <tfoot>
            <tr>
                <th class="uk-visible@s"></th>
                <td class="tm-fps-table__fps-cell" :style="{ backgroundSize: axisStep }">
                    <div class="tm-fps-table__axis uk-flex uk-flex-between">
                        <div v-for="(item, index) in axisItems" :key="index">{{ item }}</div>
                    </div>
                </td>
            </tr>
        </tfoot>
    </table>
</template>

<script>
export default {
    name: "FpsTable",
    props: {
        productFps: {
            type: Array | Object,
            required: true
        },
        resolutionsText: {
            type: Object,
            required: true
        },
        fps: {
            type: Object,
            required: true
        },
    },
    computed: {
        transformedProductFps() {
            return Object.entries(this.productFps).map(([gameKey, gameData]) => {
                if (!gameData || !gameData.ultra) return null;
                let row = { name: this.fps.games[gameKey]?.name || gameKey };

                this.fps.resolutions.forEach(resolution => {
                    row[resolution] = gameData.ultra[resolution] !== undefined ? gameData.ultra[resolution] : '-';
                });

                return row;
            }).filter(Boolean);
        },
        axisItems() {
            let items = [];
            for (let i = 0; i < this.fps.topLimit; i += 50) {
                items.push(i);
            }
            items.push('FPS');
            return items;
        },
        axisStep() {
            return (100 / (this.axisItems.length - 1)) + '%';
        }
    },
    methods: {
        calcPercentValue(value) {
            return `${Math.min(100, (value / (this.fps.topLimit * 0.01)))}%`;
        }
    }
};
</script>
