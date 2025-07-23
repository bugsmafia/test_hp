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
    <div class="tm-product-teaser__fps" @click="openModal">
        <div class="uk-flex-none tm-margin-12-right">
            <FpsMeter :fps="averageFps"/>
        </div>
        <div class="uk-width-expand">
            <div class="uk-text-bold tm-color-white-smoke uk-text-truncate">{{ fpsHeading }}</div>
            <div class="tm-color-gray-100 uk-text-small tm-text-size-14@s">{{ fpsDescription }}</div>
        </div>

        <span class="uk-icon tm-color-gray-800 tm-margin-8-top tm-margin-8-right uk-position-top-right">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M7.99613 16C12.3809 16 16 12.3791 16 8C16 3.62089 12.3731 0 7.9884 0C3.61141 0 0 3.62089 0 8C0 12.3791 3.61914 16 7.99613 16ZM7.81054 9.59381C7.30788 9.59381 7.02948 9.3617 7.02948 8.89749V8.81238C7.02948 8.08511 7.44708 7.67505 8.01933 7.27273C8.69985 6.80077 9.03238 6.54545 9.03238 6.05029C9.03238 5.51644 8.62252 5.16054 7.9884 5.16054C7.52441 5.16054 7.18415 5.39265 6.92895 5.78723C6.68149 6.06576 6.56549 6.30561 6.1015 6.30561C5.72257 6.30561 5.41324 6.05803 5.41324 5.67118C5.41324 5.51644 5.44418 5.37718 5.49831 5.23791C5.7535 4.47969 6.68149 3.86074 8.06573 3.86074C9.50411 3.86074 10.7182 4.62669 10.7182 5.97292C10.7182 6.90135 10.2078 7.36557 9.40358 7.88395C8.89319 8.21663 8.59932 8.49516 8.57612 8.91296C8.57612 8.93617 8.56839 8.97486 8.56839 9.0058C8.54519 9.33849 8.25906 9.59381 7.81054 9.59381ZM7.8028 12.0696C7.27695 12.0696 6.84389 11.6905 6.84389 11.1799C6.84389 10.6692 7.26921 10.2901 7.8028 10.2901C8.32866 10.2901 8.75399 10.6692 8.75399 11.1799C8.75399 11.6983 8.32093 12.0696 7.8028 12.0696Z"
                    fill="#404040"/>
                <path
                    d="M7.99613 16C12.3809 16 16 12.3791 16 8C16 3.62089 12.3731 0 7.9884 0C3.61141 0 0 3.62089 0 8C0 12.3791 3.61914 16 7.99613 16ZM7.81054 9.59381C7.30788 9.59381 7.02948 9.3617 7.02948 8.89749V8.81238C7.02948 8.08511 7.44708 7.67505 8.01933 7.27273C8.69985 6.80077 9.03238 6.54545 9.03238 6.05029C9.03238 5.51644 8.62252 5.16054 7.9884 5.16054C7.52441 5.16054 7.18415 5.39265 6.92895 5.78723C6.68149 6.06576 6.56549 6.30561 6.1015 6.30561C5.72257 6.30561 5.41324 6.05803 5.41324 5.67118C5.41324 5.51644 5.44418 5.37718 5.49831 5.23791C5.7535 4.47969 6.68149 3.86074 8.06573 3.86074C9.50411 3.86074 10.7182 4.62669 10.7182 5.97292C10.7182 6.90135 10.2078 7.36557 9.40358 7.88395C8.89319 8.21663 8.59932 8.49516 8.57612 8.91296C8.57612 8.93617 8.56839 8.97486 8.56839 9.0058C8.54519 9.33849 8.25906 9.59381 7.81054 9.59381ZM7.8028 12.0696C7.27695 12.0696 6.84389 11.6905 6.84389 11.1799C6.84389 10.6692 7.26921 10.2901 7.8028 10.2901C8.32866 10.2901 8.75399 10.6692 8.75399 11.1799C8.75399 11.6983 8.32093 12.0696 7.8028 12.0696Z"
                    fill="white" fill-opacity="0.2"/>
            </svg>
        </span>
        <teleport to="body" v-if="offcanvasOpen">
            <offcanvas id="fps-offcanvas">
                <template #title>{{ text.titleProduct }}</template>
                <template #body>
                    <FpsTable :fps="fps" :productFps="productFps" :resolutionsText="text.resolutions"/>
                </template>
                <template #footer>
                    <div class="uk-text-small uk-text-muted">
                        <span v-html="this.text.disclaimer" class="tm-margin-4-right"/>
                        <a v-if="this.fps.article" :href="this.fps.article" target="_blank" class="jsLoadIframe"
                           v-html="this.text.details"/>
                    </div>
                </template>
            </offcanvas>
        </teleport>
    </div>
</template>

<script>
import FpsMeter from "./FpsMeter.vue";
import FpsTable from "../../../components/FpsTable.vue";
import Offcanvas from "../../../components/Offcanvas.vue";

export default {
    name: "Fps",
    components: {FpsMeter, FpsTable, Offcanvas},
    props: {
        averageFps: {
            type: Number,
            required: true
        },
        activeGame: {},
        productFps: {}
    },
    data() {
        return {
            fps: Joomla.getOptions('fps', {}),
            text: {
                fpsHeading: Joomla.Text._('COM_HYPERPC_PRODUCT_TEASER_FPS_HEADING'),
                subtext: Joomla.Text._('COM_HYPERPC_PRODUCT_TEASER_FPS_SUB'),
                titleProduct: Joomla.Text._('COM_HYPERPC_FPS_TITLE_PRODUCT'),
                disclaimer: Joomla.Text._('COM_HYPERPC_FPS_DISCLAIMER'),
                details: Joomla.Text._('COM_HYPERPC_DETAILS'),
                resolutions: {}
            },
            offcanvasOpen: false,
            selectionSettings: this.activeGame.indexOf('@') !== -1 ? this.activeGame.split('@') : Array(this.activeGame)
        }
    },
    computed: {
        currentGame() {
            return this.selectionSettings[0] ? this.fps.games[this.selectionSettings[0]].name : '';
        },
        currentResolution() {
            const selected = this.selectionSettings[1] || '';
            if (selected.includes('fullhd')) return 'FullHD';
            if (selected.includes('qhd')) return '2K';
            return selected ? '4K' : '';
        },
        fpsHeading() {
            return this.currentGame || this.text.fpsHeading;
        },
        fpsDescription() {
            return this.currentResolution ?  `${this.text.subtext}, ${this.currentResolution}` : this.text.subtext;
        }
    },
    mounted() {
        if (this.fps.resolutions) {
            this.text.resolutions = this.fps.resolutions.reduce((acc, resolution) => {
                acc[resolution] = Joomla.Text._(`COM_HYPERPC_FPS_RESOLUTION_${resolution.toUpperCase()}`);
                return acc;
            }, {});
        }
    },
    methods: {
        openModal() {
            this.offcanvasOpen = true;
            this.$nextTick(() => {
                const fpsOffcanvas = document.querySelector('#fps-offcanvas');
                UIkit.offcanvas(fpsOffcanvas).show();
                UIkit.util.on(fpsOffcanvas, 'hidden', () => {
                    this.offcanvasOpen = false;
                })
            })
        },
    }
}
</script>
